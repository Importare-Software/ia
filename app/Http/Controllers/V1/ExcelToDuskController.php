<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\TestResult;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ExcelToDuskController extends Controller
{
    public function index()
    {
        return view('upload-excel');
    }

    public function uploadExcel(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xlsx,xls']);

        try {
            $path = $request->file('excel')->store('temp'); // Guarda temporalmente el archivo
            $spreadsheet = IOFactory::load(storage_path('app/' . $path));
            $sheetNames = $spreadsheet->getSheetNames(); // Obtiene los nombres de todas las pestañas

            // Guarda la ruta del archivo en la sesión
            session(['excelPath' => $path]);

            return redirect()->back()->with('sheets', $sheetNames); // Devuelve la vista anterior con los nombres de las pestañas
        } catch (\Exception $e) {
            Log::error("Error al leer el archivo Excel: " . $e->getMessage());
            return back()->withErrors(['msg' => 'Error al leer el archivo Excel: ' . $e->getMessage()]);
        }
    }

    public function generateDusk(Request $request)
    {
        $request->validate([
            'sheetName' => 'required|string',
            'rows' => 'required|string'
        ]);

        $path = session('excelPath'); // Recupera la ruta del archivo de la sesión

        if (!$path) {
            return back()->with('error', 'No se ha cargado ningún archivo Excel.');
        }

        $rowsInput = $request->input('rows');
        $rowsToProcess = $this->parseRowsInput($rowsInput);
        $spreadsheet = IOFactory::load(storage_path('app/' . $path));
        $sheet = $spreadsheet->getSheetByName($request->sheetName);
        $startRow = 7;
        $prompts = [];

        Log::info("Loaded Excel file from path: $path");

        // Obtener localizadores de la celda 3B
        $locators = $sheet->getCell('B3')->getValue();

        foreach ($rowsToProcess as $row) {
            $scenarioID = $sheet->getCell('A' . $row)->getValue();
            $condition = $sheet->getCell('B' . $row)->getValue();
            $useCase = $sheet->getCell('C' . $row)->getValue();
            $executionDetail = $sheet->getCell('D' . $row)->getValue();
            $expectedResults = $sheet->getCell('E' . $row)->getValue();
            $inputData = $sheet->getCell('F' . $row)->getValue();

            // Log the extracted data
            Log::info("Row $row data: ScenarioID: $scenarioID, Condition: $condition, UseCase: $useCase, ExecutionDetail: $executionDetail, ExpectedResults: $expectedResults, Locators: $locators, InputData: $inputData");

            $prompts[] = [
                "scenarioID" => $scenarioID,
                "condition" => $condition,
                "useCase" => $useCase,
                "executionDetail" => $executionDetail,
                "expectedResults" => $expectedResults,
                "locators" => $locators,
                "inputData" => $inputData,
            ];
            //dd($prompts);
        }

        $yourApiKey = env('OPENAI_API_KEY');

        $client = new Client([
            'base_uri' => 'https://api.openai.com/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$yourApiKey}"
            ]
        ]);

        $modelUsed = "gpt-4-turbo";

        $responses = [];
        foreach ($prompts as $prompt) {
            $response = $client->post('v1/chat/completions', [
                'json' => [
                    'model' => $modelUsed,
                    'messages' => [
                        [
                            "role" => "system",
                            "content" => "You are an experienced software tester. Your objective is to generate Laravel Dusk test functions based on test case information provided."
                        ],
                        [
                            "role" => "user",
                            "content" => json_encode([
                                "instructions" => [
                                    [
                                        "step" => 1,
                                        "instruction" => "Retrieve the scenario ID: {$prompt['scenarioID']}, short description: {$prompt['condition']}, and process ID: {$prompt['useCase']} from the provided test case scenario."
                                    ],
                                    [
                                        "step" => 2,
                                        "instruction" => "Identify the fields using the locators: {$prompt['locators']} specified in the 'Locators' section."
                                    ],
                                    [
                                        "step" => 3,
                                        "instruction" => "Input data: {$prompt['inputData']} into the fields using the values given for each test case."
                                    ],
                                    [
                                        "step" => 4,
                                        "instruction" => "Perform the actions listed in 'Detail of Execution': {$prompt['executionDetail']} such as clicking buttons, filling out forms, and navigating through the application."
                                    ],
                                    [
                                        "step" => 5,
                                        "instruction" => "Check 'Expected Results': {$prompt['expectedResults']} using assertions to verify that the outcome of the test matches the expected results provided."
                                    ],
                                    [
                                        "step" => 6,
                                        "instruction" => "Generate only the necessary Laravel Dusk test function code based on the provided test case information. Exclude any explanations, descriptions, or comments that are not part of the functional code itself."
                                    ],
                                    [
                                        "step" => 7,
                                        "instruction" => "Take a screenshot to capture the current state of the application for evidence."

                                    ],
                                    [
                                        "step" => 8,
                                        "instruction" => "Every press() must be followed by a hash, example: press('#btnLogin') and after a press, there must be a 3 second pause."

                                    ]
                                ]
                            ])
                        ]
                    ],
                    'max_tokens' => 1024,
                    'temperature' => 0.1,
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            $responses[] = $responseBody['choices'][0]['message']['content'];

            $aiResponse = isset($responseBody['choices'][0]['message']['content']) ? $responseBody['choices'][0]['message']['content'] : 'No response';

            TestResult::create([
                'scetario_id' => $prompt['scenarioID'],
                'condition' => $prompt['condition'],
                'use_case' => $prompt['useCase'],
                'execution_detail' => $prompt['executionDetail'],
                'expected_results' => $prompt['expectedResults'],
                'locators' => $prompt['locators'],
                'input_data' => $prompt['inputData'],
                'ai_response' => $aiResponse,
                'model_used' => $modelUsed
            ]);
        }

        return redirect()->route('upload-excel')->with('responses', $responses);
    }

    private function parseRowsInput($rowsInput)
    {
        $rows = [];
        $parts = explode(',', $rowsInput); // Divide la entrada por comas para obtener partes individuales
        foreach ($parts as $part) {
            $part = trim($part); // Elimina espacios blancos innecesarios
            if (strpos($part, '-') !== false) {
                // Es un rango, por ejemplo "9-12"
                list($start, $end) = explode('-', $part);
                if (is_numeric($start) && is_numeric($end)) {
                    $rows = array_merge($rows, range((int)$start, (int)$end));
                }
            } elseif (is_numeric($part)) {
                // Es un número de fila individual
                $rows[] = (int) $part;
            }
        }
        return $rows;
    }
}

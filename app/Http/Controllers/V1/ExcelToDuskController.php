<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\TestResult;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
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
            'rows' => 'required|string',
            'project_name' => 'required|string',
            'use_feedback' => 'nullable|boolean',
        ]);

        $path = session('excelPath'); // Recupera la ruta del archivo de la sesión

        if (!$path) {
            return back()->with('error', 'No se ha cargado ningún archivo Excel.');
        }

        $rowsInput = $request->input('rows');
        $rowsToProcess = $this->parseRowsInput($rowsInput);
        $spreadsheet = IOFactory::load(storage_path('app/' . $path));
        $sheet = $spreadsheet->getSheetByName($request->sheetName);
        $prompts = [];

        Log::info("Loaded Excel file from path: $path");

        // Obtener localizadores de la celda B3
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
        }

        $yourApiKey = env('OPENAI_API_KEY');

        $client = new Client([
            'base_uri' => 'https://api.openai.com/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$yourApiKey}"
            ]
        ]);

        $modelUsed = "gpt-4";

        $responses = [];
        $useFeedback = $request->boolean('use_feedback', false);
        $projectName = $request->input('project_name');
        $userId = Auth::id();

        // Obtener retroalimentación de test_results si está habilitado
        $feedbackData = [];
        if ($useFeedback) {
            // Obtener los últimos N registros de test_results para usar como contexto
            $feedbackData = TestResult::where('user_id', $userId)
                ->where('project_name', $projectName)
                ->orderBy('created_at', 'desc')
                ->take(5) // Puedes ajustar la cantidad de registros a obtener
                ->get();
        }

        foreach ($prompts as $prompt) {
            $aiResponse = null;

            // Construir mensajes para el modelo GPT
            $messages = [
                [
                    "role" => "system",
                    "content" => "Eres un experimentado tester de software. Tu objetivo es generar funciones de prueba de Laravel Dusk basadas en la información de casos de prueba proporcionada."
                ]
            ];

            // Si hay retroalimentación, agregarla al mensaje
            if ($useFeedback && !$feedbackData->isEmpty()) {
                $feedbackContent = "";
                foreach ($feedbackData as $feedback) {
                    $feedbackContent .= "Caso previo:\n";
                    $feedbackContent .= "Scenario ID: {$feedback->scenario_id}\n";
                    $feedbackContent .= "Condición: {$feedback->condition}\n";
                    $feedbackContent .= "Detalle de ejecución: {$feedback->execution_detail}\n";
                    $feedbackContent .= "Resultados esperados: {$feedback->expected_results}\n";
                    $feedbackContent .= "Respuesta AI previa:\n{$feedback->ai_response}\n\n";
                }

                $messages[] = [
                    "role" => "system",
                    "content" => "Basándote en los siguientes casos previos y sus soluciones, genera el código para el nuevo caso."
                ];

                $messages[] = [
                    "role" => "assistant",
                    "content" => $feedbackContent
                ];
            }

            // Agregar el nuevo caso a resolver
            $userContent = "Nuevo caso:\n";
            $userContent .= "Scenario ID: {$prompt['scenarioID']}\n";
            $userContent .= "Condición: {$prompt['condition']}\n";
            $userContent .= "Caso de uso: {$prompt['useCase']}\n";
            $userContent .= "Detalle de ejecución: {$prompt['executionDetail']}\n";
            $userContent .= "Resultados esperados: {$prompt['expectedResults']}\n";
            $userContent .= "Localizadores: {$prompt['locators']}\n";
            $userContent .= "Datos de entrada: {$prompt['inputData']}\n";
            $userContent .= "\nGenera el código Dusk necesario para este caso, siguiendo las mejores prácticas y basándote en los casos previos si están disponibles. No incluyas explicaciones adicionales.";

            $messages[] = [
                "role" => "user",
                "content" => $userContent
            ];

            // Realizar la llamada a la API de OpenAI
            try {
                $response = $client->post('v1/chat/completions', [
                    'json' => [
                        'model' => $modelUsed,
                        'messages' => $messages,
                        'max_tokens' => 1024,
                        'temperature' => 0.1,
                    ]
                ]);

                $responseBody = json_decode($response->getBody(), true);
                $aiResponse = isset($responseBody['choices'][0]['message']['content']) ? $responseBody['choices'][0]['message']['content'] : 'No response';
                $responses[] = $aiResponse;
            } catch (\Exception $e) {
                Log::error("Error al llamar a la API de OpenAI: " . $e->getMessage());
                $aiResponse = 'No response due to API error';
            }

            // Guardar el resultado en test_results
            TestResult::create([
                'scenario_id' => $prompt['scenarioID'],
                'condition' => $prompt['condition'],
                'use_case' => $prompt['useCase'],
                'execution_detail' => $prompt['executionDetail'],
                'expected_results' => $prompt['expectedResults'],
                'locators' => $prompt['locators'],
                'input_data' => $prompt['inputData'],
                'ai_response' => $aiResponse,
                'model_used' => $modelUsed,
                'user_id' => $userId,
                'project_name' => $projectName,
            ]);
        }

        if (!empty($responses)) {
            return redirect()->route('upload-excel')->with('responses', $responses);
        } else {
            return redirect()->route('upload-excel')->with('success', 'Los resultados de las pruebas se han guardado correctamente.');
        }
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

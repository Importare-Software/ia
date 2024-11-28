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
            'sheetName'     => 'required|string',
            'rows'          => 'required|string',
            'project_name'  => 'required|string',
            'prompt_extra'  => 'nullable|string',
        ]);

        $path = session('excelPath'); // Recupera la ruta del archivo de la sesión

        if (!$path) {
            return back()->with('error', 'No se ha cargado ningún archivo Excel.');
        }

        $rowsInput      = $request->input('rows');
        $rowsToProcess  = $this->parseRowsInput($rowsInput);
        $spreadsheet    = IOFactory::load(storage_path('app/' . $path));
        $sheet          = $spreadsheet->getSheetByName($request->sheetName);
        $prompts        = [];

        Log::info("Loaded Excel file from path: $path");

        // Obtener localizadores de la celda B3
        $locators = $sheet->getCell('B3')->getValue();

        foreach ($rowsToProcess as $row) {
            $scenarioID         = $sheet->getCell('A' . $row)->getValue();
            $condition          = $sheet->getCell('B' . $row)->getValue();
            $useCase            = $sheet->getCell('C' . $row)->getValue();
            $executionDetail    = $sheet->getCell('D' . $row)->getValue();
            $expectedResults    = $sheet->getCell('E' . $row)->getValue();
            $inputData          = $sheet->getCell('F' . $row)->getValue();
            $outputData         = $sheet->getCell('G' . $row)->getValue();

            Log::info("Row $row data: ScenarioID: $scenarioID, Condition: $condition, UseCase: $useCase, ExecutionDetail: $executionDetail, ExpectedResults: $expectedResults, Locators: $locators, InputData: $inputData, OutputData: $outputData");

            $prompts[] = [
                "scenarioID"        => $scenarioID,
                "condition"         => $condition,
                "useCase"           => $useCase,
                "executionDetail"   => $executionDetail,
                "expectedResults"   => $expectedResults,
                "locators"          => $locators,
                "inputData"         => $inputData,
                "outputData"        => $outputData,
            ];
        }

        $yourApiKey = env('OPENAI_API_KEY');

        $client = new Client([
            'base_uri' => 'https://api.openai.com/',
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$yourApiKey}"
            ]
        ]);

        $modelUsed   = "gpt-4o";
        $responses   = [];
        $projectName = $request->input('project_name');
        $userId      = Auth::id();
        $promptExtra = $request->input('prompt_extra');

        foreach ($prompts as $prompt) {
            $aiResponse = null;

            $messages = [
                [
                    "role"    => "system",
                    "content" => "Eres un asistente especializado en generar código de funciones para pruebas automatizadas utilizando Laravel Dusk, implementando el patrón Page Object basado en los datos proporcionados por el usuario. Define selectores y usa estos objetos en las funciones de prueba. Tu única salida será el código de la función Dusk correspondiente, sin interacción adicional ni comentarios.

                    **Objetivos:**

                    - Generar funciones de prueba en Laravel Dusk basadas en los datos ingresados.
                    - Asegurarse de incluir `waitFor` y `#` en cada interacción con botones dentro del código.
                    - Proporcionar exclusivamente el código de la función y los selectores de Page Object, sin explicaciones ni comentarios adicionales.

                    **Instrucciones de Comportamiento:**

                    - **Entrada:** Recibir los siguientes datos del usuario:
                      - Scenario ID
                      - Condición
                      - Caso de uso
                      - Detalle de ejecución
                      - Resultados esperados
                      - Localizadores
                      - Datos de entrada
                      - Datos de salida (opcional y rara vez incluido)

                    - **Proceso:**
                      - Interpretar los datos proporcionados para construir la función de prueba adecuada.
                      - Utilizar los localizadores y datos de entrada para interactuar con elementos de la interfaz.
                      - Incorporar `waitFor` y `#` en cada interacción con botones según las mejores prácticas de Laravel Dusk.
                      - Cuando se deba esperar a que cargue una nueva página para continuar con el test, validar que exista el componente siguiente a utilizar con `assertSee` para evitar errores por no localizarlo.
                      - Cuando se deba esperar a que cargue una nueva página y sea necesario agregar datos, has uso de pause() antes de mandar la petición. 
                      - Separar claramente la definición de selectores de las funciones de prueba.

                    - **Salida:**
                      - Proporcionar únicamente el código de la función Dusk resultante y los selectores de Page Object.
                      - No incluir comentarios, explicaciones ni interactuar con el usuario más allá de proporcionar el código.
                      - Proporcionar los selectores en un array asociativo con un `return` al inicio, en el siguiente formato:
                        ```php
                        return [
                            '@selectorName' => 'selectorValue',
                            // Otros selectores...
                        ];
                        ```

                    **Restricciones y Consideraciones:**

                    - **Evitar:**
                      - Cualquier tipo de diálogo, consejo o comentario adicional.
                      - Explicaciones sobre lo que se hizo o cómo se generó el código.
                      - Proporcionar información que no esté directamente relacionada con el código de la función Dusk.
                      - Responder a preguntas o temas que estén fuera de Dusk; se negará a responder temas fuera de Dusk.

                    - **Obligatorio:**
                      - Incluir `waitFor` y `#` en cada interacción con botones dentro del código.
                      - Ceñirse exclusivamente a los datos proporcionados por el usuario para generar el código.
                      - Mantener el enfoque en generar código funcional y preciso según las especificaciones dadas.".
                      "" . (!empty($promptExtra) ? "\nEste prompt es obligatoria y de gran importancia: {$promptExtra}" : "")
                ],
                [
                    "role"    => "user",
                    "content" => "Datos del caso de prueba:

                    Scenario ID: {$prompt['scenarioID']}
                    Condición: {$prompt['condition']}
                    Caso de uso: {$prompt['useCase']}
                    Detalle de ejecución: {$prompt['executionDetail']}
                    Resultados esperados: {$prompt['expectedResults']}
                    Localizadores: {$prompt['locators']}
                    Datos de entrada: {$prompt['inputData']}
                    Datos de salida: {$prompt['outputData']}" .
                    "" . (!empty($promptExtra) ? "\nEste prompt es obligatoria y de gran importancia: {$promptExtra}" : "") . "
                                    
                    Genera el código Dusk necesario para este caso, siguiendo las mejores prácticas. No incluyas explicaciones adicionales. Formatea la salida de la siguiente manera:
                    
                    ### Selectors:
                    ```php
                        // Array de selectores en el formato especificado
                    ```

                    ### Test Function:
                    ```php
                        // Código de la función de prueba Dusk utilizando Selectors
                    ```"
                ]
            ];
            
            try {
                $response = $client->post('v1/chat/completions', [
                    'json' => [
                        'model'       => $modelUsed,
                        'messages'    => $messages,
                        'max_tokens'  => 16384,
                        'temperature' => 0.1,
                    ]
                ]);

                $responseBody = json_decode($response->getBody(), true);
                $aiResponse   = $responseBody['choices'][0]['message']['content'] ?? 'No response';
                $responses[]  = $aiResponse;
            } catch (\Exception $e) {
                Log::error("Error al llamar a la API de OpenAI: " . $e->getMessage());
                $aiResponse = 'No response due to API error';
            }

            TestResult::create([
                'scenario_id'       => $prompt['scenarioID'],
                'condition'         => $prompt['condition'],
                'use_case'          => $prompt['useCase'],
                'execution_detail'  => $prompt['executionDetail'],
                'expected_results'  => $prompt['expectedResults'],
                'locators'          => $prompt['locators'],
                'input_data'        => $prompt['inputData'],
                'output_data'       => $prompt['outputData'],
                'ai_response'       => $aiResponse,
                'model_used'        => $modelUsed,
                'user_id'           => $userId,
                'project_name'      => $projectName,
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

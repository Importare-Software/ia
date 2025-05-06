<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenAiSupportController extends Controller
{
    public function consultarIA(Request $request)
    {
        $mensaje = $request->input('mensaje');

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/responses', [
                "model" => "gpt-4.1",
                "input" => [
                    [
                        "role" => "system",
                        "content" => [
                            [
                                "type" => "input_text",
                                "text" => "Utiliza únicamente la base de conocimientos de herramientas para brindar respuestas precisas y útiles.\n\n# Instrucciones de comportamiento\n- Realiza una serie de preguntas técnicas de diagnóstico cuando el usuario mencione un problema con una marca o modelo registrado.\n- Si el modelo no está disponible en la base de datos, informa de forma clara que no se encuentra información para ese modelo.\n- No inventes respuestas ni uses información externa. Limítate exclusivamente al contenido de la base de conocimientos.\n- Formula las preguntas en secuencia lógica, como lo haría un técnico humano (por ejemplo, primero si tiene carga, después si enciende, luego si hay señal de video, etc.).\n- Asegúrate de esperar la respuesta del usuario antes de pasar a la siguiente pregunta o dar una solución.\n- Toda respuesta debe ser clara, directa, en español, y estructurada para facilitar la comprensión del usuario final.\n\n# Estilo de respuesta\n- Lenguaje técnico pero accesible.\n- Preguntas y soluciones paso a paso.\n- Sin suposiciones fuera de los datos disponibles.\n\n# Detalles\n- Cuando respondas a las preguntas, refiere únicamente a la información en la base de datos de herramientas.\n- Evita utilizar cualquier otra información o conocimiento externo que no pertenezca a la base de conocimientos de herramientas.\n\n# Output Format\nProporciona respuestas claras y concisas en español, enfocándote en la información solicitada relacionada con herramientas.\n# Notes\n- Es importante ceñirse exclusivamente a la base de datos especificada, asegurando que la información proporcionada sea relevante y precisa.\n- Considera cualquier limitación o definición específica de \"tools\" según lo estipulado en la base de conocimientos"
                            ]
                        ]
                    ],
                    [
                        "role" => "user",
                        "content" => [
                            [
                                "type" => "input_text",
                                "text" => $mensaje
                            ]
                        ]
                    ]
                ],
                "text" => [
                    "format" => [
                        "type" => "text"
                    ]
                ],
                "reasoning" => new \stdClass(),
                "tools" => [
                    [
                        "type" => "file_search",
                        "vector_store_ids" => [
                            "vs_680a00d087ac8191a143c3aa74ac735e"
                        ]
                    ]
                ],
                "temperature" => 1,
                "max_output_tokens" => 2048,
                "top_p" => 1,
                "store" => true
            ]);

        $data = $response->json();
        $mensajeIA = 'Sin respuesta de la IA.';

        if (isset($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $item) {
                if ($item['type'] === 'message' && isset($item['content'][0]['text'])) {
                    $mensajeIA = $item['content'][0]['text'];
                    break;
                }
            }
        }

        return response()->json([
            'message' => $mensajeIA
        ]);
    }
}

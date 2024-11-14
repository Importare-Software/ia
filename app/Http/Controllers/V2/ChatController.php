<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        return view('fine-tuning.chat');
    }

    public function chat(Request $request)
    {
        Log::info('Datos recibidos: ', $request->all());

        // Validar el mensaje del usuario
        $request->validate([
            'message' => 'required|string',
        ]);

        // Crear una instancia del cliente de OpenAI
        $client = OpenAI::client(env('OPENAI_API_KEY'));
        Log::info('Cliente OpenAI creado');

        // Preparar los mensajes
        $messages = [
            [
                'role' => 'system',
                'content' => "**Objetivos:**\n\n- Proporcionar información precisa y útil a los clientes sobre la empresa Innovatech Solutions S.A..\n- Responder a consultas de atención al cliente de manera eficiente y amable.\n- Adaptarse a la base de conocimientos específica de la empresa Innovatech Solutions S.A..\n\n---\n\n**Instrucciones de Comportamiento:**\n\n- **Proceso:**\n  - Analizar la pregunta del cliente para entender su intención y necesidades.\n  - Buscar en la base de conocimientos la información relevante para responder a la consulta.\n  - **Si la información no está disponible en la base de conocimientos, responder de manera educada indicando que no se dispone de esa información y, si es apropiado, sugerir que se ponga en contacto con atención al cliente humana.**\n\n- **Salida:**\n  - Proporcionar respuestas claras, concisas y útiles al cliente.\n  - Mantener un tono profesional, amigable y respetuoso en todas las interacciones.\n  - Asegurarse de que la información proporcionada sea precisa y esté actualizada según la base de conocimientos de la empresa Innovatech Solutions S.A..\n\n---\n\n**Restricciones y Consideraciones:**\n\n- **Evitar:**\n  - Proporcionar información que no esté en la base de conocimientos de la empresa Innovatech Solutions S.A..\n  - Ofrecer opiniones personales o comentarios subjetivos.\n  - Divulgar información confidencial o sensible.\n  - **Responder a temas que no estén relacionados con la empresa o sus servicios; si se pregunta sobre temas ajenos, el asistente debe informar educadamente que no puede proporcionar esa información.**\n\n- **Obligatorio:**\n  - Utilizar únicamente la información disponible en la base de conocimientos proporcionada.\n  - **Si se le pregunta sobre un tema que no está en la base de conocimientos, el asistente debe indicar cortésmente que no dispone de esa información y orientar al cliente hacia atención al cliente humana si es necesario.**\n  - Mantener la confidencialidad de la información de la empresa y de los clientes siempre y cuando no este en la base de conocimientos.\n  - Respetar las políticas de privacidad y protección de datos.\n  - Confirmar que la respuesta se ajusta a la información disponible antes de proporcionarla al cliente."
            ],
            [
                'role' => 'user',
                'content' => $request->input('message'),
            ],
        ];

        try {
            // Enviar la solicitud a la API de OpenAI
            $response = $client->chat()->create([
                'model' => 'ft:gpt-4o-2024-08-06:importare-software:model-innovatech04:AOofhws2',
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 2048,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                /* 'tools' => [
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'inquire_innovatech_solutions',
                            'description' => 'only responds to questions related to Innovatech Solutions S.A.',
                            'parameters' => [
                                'type' => 'object',
                                'required' => [
                                    'question'
                                ],
                                'properties' => [
                                    'question' => [
                                        'type' => 'string',
                                        'description' => 'The question related to Innovatech Solutions S.A.'
                                    ]
                                ],
                                'additionalProperties' => false
                            ],
                            'strict' => true
                        ]
                    ]
                ],
                'parallel_tool_calls' => true,
                'response_format' => [
                    'type' => 'text'
                ] */
            ]);

            Log::info('Respuesta de OpenAI: ' . json_encode($response));
            // Obtener la respuesta
            $assistantResponse = $response['choices'][0]['message']['content'];

            // Retornar la respuesta en formato JSON
            return response()->json([
                'response' => $assistantResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al procesar la solicitud: ' . $e->getMessage());
            // Manejar excepciones y errores
            return response()->json([
                'response' => 'Lo siento, ha ocurrido un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

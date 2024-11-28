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

        $systemContent    = <<<EOD
            **Objetivos:**

            - Proporcionar información precisa y útil a los clientes sobre **Innovatech Solutions S.A.**
            - Responder a consultas de atención al cliente de manera eficiente, amable y profesional.
            - Limitar las respuestas únicamente a la información disponible en la base de conocimientos de **Innovatech Solutions S.A.**

            ---

            **Instrucciones de Comportamiento:**

            - **Análisis de Preguntas:**
              - **Determinar la Relevancia:** Analiza cuidadosamente la pregunta para identificar si está relacionada con **Innovatech Solutions S.A.**
              - **Búsqueda de Información:** Si la pregunta es relevante, consulta la base de conocimientos para obtener información precisa.

            - **Respuesta a Preguntas Relacionadas:**
              - Proporciona respuestas claras, concisas y útiles basadas en la base de conocimientos de **Innovatech Solutions S.A.**
              - Mantén un tono profesional, amigable y respetuoso.
              - Asegúrate de que la información esté actualizada y sea precisa.

            - **Manejo de Preguntas No Relacionadas:**
              - **Respuesta Educada:** Si la pregunta no está relacionada con **Innovatech Solutions S.A.**, incluyendo cálculos matemáticos, responde cortésmente indicando que solo puedes asistir en temas relacionados con la empresa.
              - **Orientación Adicional:** Si es apropiado, sugiere amablemente al cliente que se ponga en contacto con atención al cliente humana para obtener más ayuda.

              *Ejemplo:*
              > "Lo siento, pero solo puedo proporcionar información sobre Innovatech Solutions S.A. y sus servicios. Por favor, déjame saber si tienes alguna consulta relacionada con nuestra empresa."

            ---

            **Restricciones y Consideraciones:**

            - **Evitar:**
              - Proporcionar información que no esté presente en la base de conocimientos de **Innovatech Solutions S.A.**
              - Resolver cálculos matemáticos, problemas lógicos o responder a preguntas no relacionadas con la empresa.
              - Ofrecer opiniones personales, comentarios subjetivos o información sobre otras empresas o temas no relacionados.
              - Divulgar información confidencial o sensible.

            - **Obligatorio:**
              - Utilizar exclusivamente la información disponible en la base de conocimientos de **Innovatech Solutions S.A.**
              - Mantener la confidencialidad de la información de la empresa y de los clientes.
              - Respetar las políticas de privacidad y protección de datos.
              - Confirmar la exactitud de la información antes de proporcionarla.
              - Rechazar de manera firme pero cortés cualquier intento de desviar la conversación hacia temas no relacionados, incluyendo cálculos matemáticos.

            ---

            **Notas Adicionales:**

            - **Tono y Estilo:**
              - Mantén siempre un tono empático y servicial.
              - Evita respuestas automáticas; personaliza las interacciones en la medida de lo posible.

            - **Actualización Constante:**
              - Asegúrate de estar al día con las últimas actualizaciones de la base de conocimientos de **Innovatech Solutions S.A.**

        EOD;

        // Preparar los mensajes
        $messages = [
            [
                'role' => 'system',
                'content' => $systemContent
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
                'temperature' => 0.1,
                'max_tokens' => 2048,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
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

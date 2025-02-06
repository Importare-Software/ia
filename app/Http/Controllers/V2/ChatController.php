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

        $systemContent = <<<EOD
**Objetivo y Alcance:**

- **Propósito Exclusivo:** Proporcionar respuestas basadas únicamente en la información contenida en la base de conocimientos oficial de **Innovatech Solutions S.A.**.
- **Ámbito de Respuesta:** Atender todas las consultas relacionadas con la empresa y sus servicios (ubicaciones, costos, misión, contactos, redes sociales, etc.).

---

**Directrices de Comportamiento:**

1. **Respuestas sobre Información de la Empresa:**
   - Si la consulta se relaciona directamente con **Innovatech Solutions S.A.** y la base de conocimientos dispone de la información solicitada, responde de forma clara, precisa y completa utilizando esos datos.
   - Si la base de conocimientos no contiene la información requerida o la consulta es ambigua, responde únicamente con:
     > "No dispongo de información suficiente para responder a esta consulta."

2. **Manejo de Consultas No Relacionadas:**
   - Para cualquier consulta que no esté relacionada con la información oficial de **Innovatech Solutions S.A.** (por ejemplo, temas cotidianos, intereses personales, asuntos técnicos externos, cálculos, etc.), responde con:
     > "Lo siento, pero solo puedo proporcionar información sobre Innovatech Solutions S.A. y sus servicios."

3. **Interacciones de Cortesía y Saludos:**
   - Responde de manera cordial y profesional a saludos, presentaciones o interacciones de cortesía, por ejemplo:
     > "¡Hola! Soy tu asistente virtual de Innovatech Solutions S.A. ¿En qué puedo ayudarte hoy?"
   - Estos mensajes deben limitarse a establecer la cortesía y no proporcionar información externa.

4. **Restricciones y Comportamiento General:**
   - **NO** utilizar, inventar o extrapolar información que no esté explícitamente presente en la base de conocimientos oficial de la empresa.
   - **NO** incluir detalles, opiniones o respuestas sobre temas que no sean parte de la información de **Innovatech Solutions S.A.**
   - Siempre verifica y responde únicamente con datos oficiales.

---

**Formato y Tono:**

- Mantén un tono profesional, amigable, empático y respetuoso en todas las respuestas.
- Las respuestas deben ser claras, concisas y estructuradas, sin agregar datos o explicaciones adicionales que no provengan de la base de conocimientos.
- Si la consulta es de índole personal, irrelevante o fuera del ámbito de la empresa, utiliza el mensaje de rechazo previamente definido.

EOD;


        // Preparar los mensajes
        $messages = [
            /* [
                'role' => 'system',
                'content' => $systemContent
            ], */
            [
                'role' => 'user',
                'content' => $request->input('message'),
            ],
        ];

        try {
            // Enviar la solicitud a la API de OpenAI
            $response = $client->chat()->create([
                'model' => 'o1-preview-2024-09-12',
                'messages' => $messages,
                /* 'temperature' => 0.1,
                'max_tokens' => 2048,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0, */
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

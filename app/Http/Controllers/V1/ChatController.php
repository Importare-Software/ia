<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatbotSetting;
use App\Models\Conversation;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Parsedown;

class ChatController extends Controller
{
    protected $supabase;
    protected $assistantName;
    protected $instructions;
    protected $embeddingModel;
    protected $chatModel;
    protected $similarityThreshold;

    public function __construct()
    {
        $this->supabase = new Client([
            'base_uri' => env('SUPABASE_URL'),
            'headers' => [
                'apikey'        => env('SUPABASE_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                'Content-Type'  => 'application/json'
            ]
        ]);

        $settings = ChatbotSetting::first();

        // Configuraciones por defecto, puedes cambiarlas según tus necesidades
        $this->assistantName       = $settings->assistantName;
        $this->instructions        = $settings->instructions;
        $this->embeddingModel      = 'text-embedding-3-small';
        $this->chatModel           = $settings->chatModel;
        $this->similarityThreshold = $settings->similarityThreshold;
    }

    public function index()
    {
        $sessionId = session()->getId();
        $conversations = Conversation::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat', ['conversations' => $conversations]);
    }

    public function sendMessage(Request $request)
    {
        $sessionId    = $request->input('session_id', session()->getId());
        $message      = $request->input('message');
        $incognito    = $request->input('incognito', false);
        $name         = $request->input('name', $this->assistantName);
        $instructions = $request->input('instructions', $this->instructions);
        $user         = auth()->user();

        // Parsear el mensaje usando Parsedown
        $message = Parsedown::instance()->text($message);

        if (!$incognito) {
            Conversation::create([
                'user_id'    => $user->id,
                'session_id' => $sessionId,
                'message'    => $message,
                'is_user'    => true,
            ]);
        }

        $responseMessage = $this->handleMessage($message, $name, $instructions, $sessionId);

        if (!$incognito) {
            Conversation::create([
                'user_id'    => $user->id,
                'session_id' => $sessionId,
                'message'    => $responseMessage,
                'is_user'    => false,
            ]);
        }

        return response()->json([
            'message'    => $responseMessage,
            'session_id' => $sessionId,
        ]);
    }

    private function handleMessage($message, $name, $instructions, $sessionId)
    {
        // Analizar la intención del usuario
        $intention = $this->analyzeIntention($message);

        // Obtener el historial de conversación
        $conversationHistory = Conversation::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        // Construir el mensaje del sistema con el nombre e instrucciones
        $systemPrompt = $instructions . " Tu nombre es " . $name . ". Responde **únicamente** basándote en la información proporcionada en el contexto a continuación. No añadas información que no esté en el contexto.";

        // Crear el array de mensajes para el modelo
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Añadir el historial de conversación
        foreach ($conversationHistory as $conv) {
            $role = $conv->is_user ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $conv->message];
        }

        // Añadir el mensaje actual del usuario
        $messages[] = ['role' => 'user', 'content' => $message];

        // Decidir si buscar en la base de datos vectorial
        if (in_array($intention, ['consulta específica', 'pregunta general'])) {
            $dbAnswer = $this->findAnswerInVectors($message);
            if ($dbAnswer) {
                // Añadir la respuesta de la base de datos como contexto
                $messages[] = ['role' => 'assistant', 'content' => $dbAnswer];
            }
        }

        // Generar la respuesta utilizando el modelo GPT
        $responseMessage = $this->generateResponse($messages);

        return $responseMessage;
    }

    private function analyzeIntention($message)
    {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'    => $this->chatModel,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Eres un asistente que analiza la intención del usuario. Clasifica el siguiente mensaje en una de las siguientes categorías: saludo, pregunta general, consulta específica, despedida. Solo responde con el nombre de la categoría.',
                    ],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $intention = strtolower(trim($data['choices'][0]['message']['content']));

        return $intention;
    }

    private function findAnswerInVectors($message)
    {
        $user = auth()->user();
        $messageVector = $this->vectorizeMessage($message);
        if (is_null($messageVector)) {
            Log::error('Vectorización fallida para el mensaje: ' . $message);
            return null;
        }

        // Realizar la búsqueda vectorial en Supabase
        $response = $this->supabase->request('POST', '/rest/v1/rpc/match_documents', [
            'json' => [
                'user_id'               => $user->id,
                'query_embedding'       => $messageVector,
                'match_count'           => 10,
                'similarity_threshold'  => $this->similarityThreshold - 0.1,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data)) {
            // Concatenar los contenidos de los documentos encontrados
            $combinedContent = implode("\n\n", array_column($data, 'content'));
            return $combinedContent;
        } else {
            return null;
        }
    }

    private function vectorizeMessage($message)
    {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'input' => $message,
                'model' => $this->embeddingModel,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['data'][0]['embedding'];
    }

    private function generateResponse($messages)
    {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'    => $this->chatModel,
                'messages' => $messages,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $reply = $data['choices'][0]['message']['content'];

        return $reply;
    }

    public function getMessages(Request $request)
    {
        $sessionId = session()->getId();
        $incognito = $request->input('incognito', false);

        if ($incognito) {
            return response()->json([]);
        }

        $messages = Conversation::where('session_id', $sessionId)->get();
        return response()->json($messages);
    }
}

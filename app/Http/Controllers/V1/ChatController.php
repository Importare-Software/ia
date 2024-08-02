<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Document;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use MathPHP\LinearAlgebra\Vector;
use Parsedown;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function sendMessage(Request $request)
    {
        $sessionId = $request->input('session_id', session()->getId());
        $message = $request->input('message');
        $incognito = $request->input('incognito', false);

        // Parse the message using Parsedown
        $message = Parsedown::instance()->text($message);

        if (!$incognito) {
            Conversation::create([
                'session_id' => $sessionId,
                'message' => $message,
                'is_user' => true,
            ]);
        }

        $responseMessage = $this->handleMessage($message);

        if (!$incognito) {
            Conversation::create([
                'session_id' => $sessionId,
                'message' => $responseMessage,
                'is_user' => false,
            ]);
        }

        return response()->json([
            'message' => $responseMessage,
            'session_id' => $sessionId,
        ]);
    }

    private function handleMessage($message)
    {
        $dbAnswer = $this->findAnswerInVectors($message);
        if ($dbAnswer) {
            $decodedContent = json_decode($dbAnswer, true);
            if (isset($decodedContent)) {
                $cleanContent = $this->cleanHtmlTags($decodedContent);
                return "Aquí tienes la información solicitada: " . $cleanContent;
            } else {
                return "Lo siento, no encontré información relevante. ¿Puedo ayudarte con algo más?";
            }
        } else {
            return "Lo siento, no encontré información relevante. ¿Puedo ayudarte con algo más?";
        }
    }

    private function findAnswerInVectors($message)
    {
        $messageVector = $this->vectorizeMessage($message);
        if (is_null($messageVector)) {
            Log::error('Vectorization failed for message: ' . $message);
            return null;
        }

        $documents = Document::all();
        $bestMatch = null;
        $highestSimilarity = 0.5;

        foreach ($documents as $document) {
            $documentVector = json_decode($document->vector, true);
            $tagVector = json_decode($document->tags, true);
            if ($documentVector && $tagVector) {
                if (count($messageVector) !== count($documentVector)) {
                    Log::error('Vector length mismatch: message vector length ' . count($messageVector) . ', document vector length ' . count($documentVector) . ', tag vector length ' . count($tagVector));
                    continue;
                }

                $vectorSimilarity = $this->cosineSimilarity($messageVector, $documentVector);
                $tagSimilarity = $this->cosineSimilarity($messageVector, $tagVector);

                if ($vectorSimilarity > $highestSimilarity || $tagSimilarity > $highestSimilarity) {
                    if ($vectorSimilarity > $highestSimilarity) {
                        $highestSimilarity = $vectorSimilarity;
                        $bestMatch = $document->content;
                    }
                }
            }
        }

        return $highestSimilarity > 0.5 ? $bestMatch : null;
    }


    private function vectorizeMessage($message)
    {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'input' => $message,
                'model' => 'text-embedding-3-large',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['data'][0]['embedding'];
    }

    private function cosineSimilarity($vectorA, $vectorB)
    {
        $vectorA = new Vector($vectorA);
        $vectorB = new Vector($vectorB);
        return $vectorA->dotProduct($vectorB) / ($vectorA->length() * $vectorB->length());
    }

    private function cleanHtmlTags($htmlContent)
    {
        return strip_tags($htmlContent);
    }

    public function getMessages(Request $request)
    {
        $sessionId = $request->input('session_id', session()->getId());
        $incognito = $request->input('incognito', false);

        if ($incognito) {
            return response()->json([]);
        }

        $messages = Conversation::where('session_id', $sessionId)->get();
        return response()->json($messages);
    }
}

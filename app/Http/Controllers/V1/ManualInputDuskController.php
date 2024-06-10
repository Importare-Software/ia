<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ManualInputDuskController extends Controller
{
    public function index()
    {
        return view('manual-input');
    }

    public function generateDusk(Request $request)
    {
        $request->validate([
            'scenarioID' => 'required|string',
            'condition' => 'required|string',
            'useCase' => 'required|string',
            'executionDetail' => 'required|string',
            'expectedResults' => 'required|string',
            'locators' => 'required|string',
            'inputData' => 'required|string',
        ]);

        $prompt = [
            "scenarioID" => $request->scenarioID,
            "condition" => $request->condition,
            "useCase" => $request->useCase,
            "executionDetail" => $request->executionDetail,
            "expectedResults" => $request->expectedResults,
            "locators" => $request->locators,
            "inputData" => $request->inputData,
        ];

        $instructions = [
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
            ]
        ];

        Log::info("Received manual input: ", $prompt);

        $yourApiKey = env('OPENAI_API_KEY');
        $client = new Client([
            'base_uri' => 'https://api.openai.com/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$yourApiKey}"
            ]
        ]);

        $response = $client->post('v1/chat/completions', [
            'json' => [
                'model' => "gpt-4-turbo",
                'messages' => [
                    [
                        "role" => "system",
                        "content" => "You are an experienced software tester. Your objective is to generate Laravel Dusk test functions based on test case information provided."
                    ],
                    [
                        "role" => "user",
                        "content" => json_encode($instructions),
                    ]
                ],
                'max_tokens' => 1024,
                'temperature' => 0.1,
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $code = $responseBody['choices'][0]['message']['content'];

        return redirect()->back()->with('code', $code);
    }
}

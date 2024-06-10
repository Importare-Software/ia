<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ByimageController extends Controller
{
    public function index()
    {
        return view('test-dusk');
    }

    public function generateDusk(Request $request)
    {
        $data = $request->validate([
            'imagen' => 'required|image',
        ]);

        $yourApiKey = env('OPENAI_API_KEY');

        $client = new Client([
            'base_uri' => 'https://api.openai.com/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$yourApiKey}"
            ]
        ]);

        $imagePath = $request->file('imagen')->getPathname();
        $imageBase64 = base64_encode(file_get_contents($imagePath));

        $prompt = [
            "instructions" => [
                [
                    "step" => 1,
                    "instruction" => "Retrieve the scenario ID, short description, and process ID from the provided test case scenario. These will help in defining the context and purpose of the test within the application workflow."
                ],
                [
                    "step" => 2,
                    "instruction" => "Identify the user email and password fields in the user interface using the locators specified. These locators are constants such as 'campo user.email' for the email field and 'campo password:password' for the password field, which are used to target these elements in the HTML structure."
                ],
                [
                    "step" => 3,
                    "instruction" => "Input data into the email and password fields using the values given for each test case. These values will vary with each test scenario, ensuring that the credentials used can test different user access scenarios."
                ],
                [
                    "step" => 4,
                    "instruction" => "Interact with the login button using its defined locator, such as 'botón iniciar sesión: buttonLogin'. This action simulates the user clicking the login button to authenticate."
                ],
                [
                    "step" => 5,
                    "instruction" => "Check the expected outcome as described in the scenario. Ensure the test case verifies this expected result, which might include successfully accessing the platform or handling error messages appropriately."
                ],
                [
                    "step" => 6,
                    "instruction" => "Log the result of each action step to help in debugging and ensuring the test runs as expected, which is crucial for maintaining the integrity and reliability of your test suite."
                ],

            ]
        ];

        $response = $client->post('v1/chat/completions', [
            'json' => [
                'model' => "gpt-4-turbo",
                'messages' => [
                    [
                        "role" => "system",
                        "content" => "You are an experienced software tester. Your objective is to generate Laravel Dusk test functions based on test case information and images provided."
                    ],
                    [
                        "role" => "user",
                        'content' => json_encode([
                            "type" => "image_url",
                            "image_url" => "data:image/jpeg;base64," . $imageBase64
                        ])
                    ],
                    [
                        "role" => "user",
                        "content" => json_encode($prompt)
                    ]
                ],
                'max_tokens' => 1024,
                'stop' => ['\n'],
                'temperature' => 0.1,
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);

        $content = $responseBody['choices'][0]['message']['content'];

        return redirect()->route('test-dusk')->with('response', $content);
    }
}

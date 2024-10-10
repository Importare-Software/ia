<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatbotSetting;
use Illuminate\Http\Request;

class ChatbotSettingsController extends Controller
{
    public function edit()
    {
        $settings = ChatbotSetting::firstOrCreate([], [
            'assistantName' => 'Ed',
            'instructions' => 'Instrucciones por defecto...',
            'chatModel' => 'gpt-4o-mini',
            'similarityThreshold' => 0.5
        ]);

        return view('chatbot-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = ChatbotSetting::first();
        $settings->update($request->all());

        return redirect()->route('chatbot.settings.edit')->with('success', 'Configuración actualizada correctamente.');
    }
}

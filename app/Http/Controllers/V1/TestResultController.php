<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\TestResult;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    public function index()
    {
        $testResults = TestResult::latest()->get();
        return view('test_results.index', compact('testResults'));
    }

    public function edit($id)
    {
        $testResult = TestResult::findOrFail($id);
        return view('test_results.edit', compact('testResult'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'ai_response_score' => 'required|integer',
            'ai_response_corrected' => 'required|string',
        ]);

        $testResult = TestResult::findOrFail($id);
        $testResult->update($data);

        return redirect()->route('test_results.index')->with('success', 'Respuesta actualizada correctamente');
    }
}

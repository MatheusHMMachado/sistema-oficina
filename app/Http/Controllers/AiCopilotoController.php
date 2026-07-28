<?php

namespace App\Http\Controllers;

use App\Services\Ai\CopilotoService;
use Illuminate\Http\Request;

class AiCopilotoController extends Controller
{
    public function status(CopilotoService $copiloto)
    {
        return response()->json([
            'enabled' => $copiloto->isEnabled(),
            'mode' => $copiloto->mode(),
            'has_llm' => $copiloto->hasLlm(),
            'model' => $copiloto->hasLlm() ? config('ai.model') : null,
        ]);
    }

    public function chat(Request $request, CopilotoService $copiloto)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:30',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
        ]);

        $result = $copiloto->chat(
            $validated['message'],
            $validated['history'] ?? []
        );

        return response()->json($result);
    }
}

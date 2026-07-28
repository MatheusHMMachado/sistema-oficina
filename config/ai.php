<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Copiloto de IA — Sistema Oficina
    |--------------------------------------------------------------------------
    |
    | Usa API compatível com OpenAI (Chat Completions + tool/function calling).
    | Funciona com: OpenAI, OpenRouter, Groq, Azure OpenAI (endpoint compatível).
    |
    | Se AI_API_KEY estiver vazio, o sistema usa o motor local (regras + tools).
    |
    */

    'enabled' => env('AI_COPILOTO_ENABLED', true),

    'api_key' => env('AI_API_KEY'),

    'base_url' => rtrim(env('AI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('AI_TIMEOUT', 60),

    'max_tool_rounds' => 5,

    'max_history' => 20,

];

<?php

// config for Maestroerror/LarAgent
return [

    /**
     * Default driver to use, binded in service provider
     * with \LarAgent\Core\Contracts\LlmDriver interface
     */
    'default_driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,

    /**
     * Default chat history to use, binded in service provider
     * with \LarAgent\Core\Contracts\ChatHistory interface
     */
    'default_chat_history' => \LarAgent\History\InMemoryChatHistory::class,

    /**
     * Autodiscovery namespaces for Agent classes.
     * Used by `agent:chat` to locate agents.
     */
    'namespaces' => [
        'App\\AiAgents\\',
        'App\\Agents\\',
    ],

    'default_provider' => 'openrouter', // or whatever your default is

    /**
     * Always keep provider named 'default'
     * You can add more providers in array
     * by copying the 'default' provider
     * and changing the name and values
     *
     * You can remove any other providers
     * which your project doesn't need
     */
    'providers' => [
        // 'default' => [
        //     'label' => 'openai',
        //     'api_key' => env('OPENROUTER_API_KEY'),
        //     'api_url' => "https://api.openrouter.ai/api/v1",
        //     'driver' => \LarAgent\Drivers\OpenAi\OpenAiDriver::class,
        //     'default_context_window' => 50000,
        //     'default_max_completion_tokens' => 10000,
        //     'default_temperature' => 1,
        // ],

        'ollama' => [
            'label' => 'ollama-local',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => 'ollama', // Can be any string for Ollama
            'api_url' => 'http://localhost:11434/v1',
            'default_context_window' => 50000,
            'default_max_completion_tokens' => 100,
            'default_temperature' => 0.7,
        ],

        'gpt4' => [
            'label' => 'GitHub-AI',
            'api_key' => env('GITHUB_TOKEN'),
            'api_url' => 'https://models.github.ai/inference',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class, // It’s OpenAI-compatible
            'default_context_window' => 4096,
            'default_max_completion_tokens' => 1000,
            'default_temperature' => 1,
        ],

        // 'audio' => [
        //     'label' => 'GitHub-AI',
        //     'api_key' => env('AUDIO'),
        //     'api_url' => 'https://models.github.ai/inference',
        //     'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class, // It’s OpenAI-compatible
        //     'default_context_window' => 4096,
        //     'default_max_completion_tokens' => 1000,
        //     'default_temperature' => 1,
        // ],

        'a4f_whisper' => [
            'label' => 'provider-3/whisper-1',
            'api_key' => env('A4F_API_KEY'),
            'api_url' => 'https://api.a4f.co/v1/audio/transcriptions',
            'model' => 'provider-3/whisper-1',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'default_context_window' => 4096,
            'default_max_completion_tokens' => 1000,
            'default_temperature' => 0.7,
        ],

        // openai/gpt-oss-20b:free
        'openrouter' => [
            'label' => 'openrouter',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => env('OPENROUTER_API_KEY'),
            'api_url' => 'https://openrouter.ai/api/v1',
            'default_context_window' => 50000,
            'default_max_completion_tokens' => 100,
            'default_temperature' => 1,
        ],

        'gemini' => [
            'label' => 'gemini',
            'api_key' => env('GEMINI_API_KEY'),
            'driver' => \LarAgent\Drivers\OpenAi\GeminiDriver::class,
            'default_context_window' => 1000000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],
    ],

    'fallback_provider' => 'openrouter',
];

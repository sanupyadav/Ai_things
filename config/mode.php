<?php

return [
    'ollama' => [
        ['label' => 'LLaMA 3.2 (3B)', 'value' => 'llama3.2:3b'],
        ['label' => 'Gemma 9B', 'value' => 'gemma2:9b'],
        ['label' => 'OpenChat', 'value' => 'openchat:latest'],
        ['label' => 'DeepSheek', 'value' => 'deepseek-r1'],
        // ['label' => 'Neural Cha', 'value' => 'neural-chat'],
    ],
    // 'openai' => [
    //     ['label' => 'GPT-4o-Mini', 'value' => 'gpt-4o-mini'],
    //     ['label' => 'GPT-4', 'value' => 'gpt-4'],
    //     ['label' => 'GPT-3.5 Turbo', 'value' => 'gpt-3.5-turbo'],
    // ],
    'gpt4o' => [
        ['label' => 'GPT-4o', 'value' => 'openai/gpt-4o'],
    ],
    'gpt41' => [
        ['label' => 'GPT-4.1', 'value' => 'openai/gpt-4.1'],
    ],
    'grok' => [
        ['label' => 'GROK-3', 'value' => 'xai/grok-3'],
    ],
    'new' => [
        ['label' => 'whisper', 'value' => 'provider-2/whisper-1'],
    ],
];

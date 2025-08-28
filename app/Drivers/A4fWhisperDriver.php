<?php

namespace App\Drivers;

use LarAgent\Drivers\OpenAi\BaseOpenAiDriver;

class A4fWhisperDriver extends BaseOpenAiDriver
{
    public function __construct()
    {
        // Default configs for Whisper-like service
        $this->apiUrl = config('laragent.providers.a4f.api_url', 'https://api.a4f.co/v1/audio/transcriptions');
        $this->apiKey = config('laragent.providers.a4f.api_key', env('A4F_API_KEY'));
        $this->model  = config('laragent.providers.a4f.model', 'provider-3/whisper-1'); // or your model
    }
}

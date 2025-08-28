<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AudioService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('A4F_API_KEY');   // .env se key le raha tha
        $this->baseUrl = 'https://api.a4f.co/v1/audio/transcriptions';
    }

    public function transcribe(string $filePath): ?string
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->attach(
            'file', file_get_contents($filePath), basename($filePath)
        )->post($this->baseUrl, [
            'model' => 'provider-3/whisper-1',
        ]);

        if ($response->successful()) {
            return $response->json()['text'] ?? null;
        }

        return null;
    }
}

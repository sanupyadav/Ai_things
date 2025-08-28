<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use Illuminate\Support\Facades\Http;

class AudioService
{
    protected FFMpeg $ffmpeg;
    protected string $outputFolder;
     protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {

        $this->apiKey = env('A4F_API_KEY');
        $this->baseUrl = 'https://api.a4f.co/v1'; 
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => '/opt/homebrew/bin/ffmpeg',
            'ffprobe.binaries' => '/opt/homebrew/bin/ffprobe',
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);

        // Define folder to store all processed audio
        $this->outputFolder = storage_path('app/public/processed_audio');

        // Create folder if it doesn't exist
        if (!file_exists($this->outputFolder)) {
            mkdir($this->outputFolder, 0755, true);
        }
    }

    /**
     * Preprocess audio:
     * - Resample to 16kHz mono
     * - Remove background noise
     * - Convert to MP3 or WAV
     * - Store in a single folder
     */
    // public function preprocess(string $filePath, string $format = 'mp3'): string
    // {
    //     if (!file_exists($filePath)) {
    //         throw new \Exception("File not found: " . $filePath);
    //     }

    //     $audio = $this->ffmpeg->open($filePath);

    //     // Apply noise reduction
    //     $audio->filters()->custom('afftdn=nf=-25');

    //     // Choose output format
    //    if ($format === 'mp3') {
    //         $audioFormat = new Mp3();
    //         $audioFormat->setAudioCodec('libmp3lame');
    //     } elseif ($format === 'flac') {
    //         $audioFormat = new Flac();
    //         $audioFormat->setAudioCodec('flac');
    //     } else {
    //         $audioFormat = new Wav();
    //         $audioFormat->setAudioCodec('pcm_s16le');
    //     }

    //     $audioFormat
    //         ->setAudioKiloBitrate(64)
    //         ->setAudioChannels(1);

    //     // Generate output filename (original name + timestamp)
    //     $fileName = pathinfo($filePath, PATHINFO_FILENAME);
    //     $timestamp = time();
    //     $outputPath = $this->outputFolder . '/' . $fileName . '_' . $timestamp . '.' . $format;

    //     // Save processed audio
    //     $audio->save($audioFormat, $outputPath);

    //     return $outputPath;
    // }


    function transcribeAudio(string $filePath, string $model = 'provider-3/whisper-1')
{
    $response = Http::withToken('ddc-a4f-d5d100db188f414fbf505a57f8b22b00')
        ->attach(
            'file',
            fopen($filePath, 'r'),
            basename($filePath)
        )
        ->post('https://api.a4f.co/v1/audio/transcriptions', [
            'model' => $model
        ]);

    if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'text'   => $response->json()['text'] ?? ''
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $response->body()
        ], $response->status());
}
}

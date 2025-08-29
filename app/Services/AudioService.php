<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Aws\TranscribeService\TranscribeServiceClient;

class AudioService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected $transcribe;

    public function __construct()
    {

        $this->transcribe = new TranscribeServiceClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
        $this->apiKey = env('A4F_API_KEY');   // .env se key le raha tha
        $this->baseUrl = 'https://api.a4f.co/v1/audio/transcriptions';
    }


     public function uploadToS3($file)
    {
        $path = Storage::disk('s3')->putFile('calls', $file);
        return Storage::disk('s3')->url($path); // public URL
    }

     // Start Transcription Job
    public function startTranscribeJob($jobName, $s3Uri)
    {
        $this->transcribe->startTranscriptionJob([
            'TranscriptionJobName' => $jobName,
            'Media' => ['MediaFileUri' => $s3Uri],
            'MediaFormat' => pathinfo($s3Uri, PATHINFO_EXTENSION),
            'LanguageCode' => 'hi-IN',
            'Settings' => [
                'ShowSpeakerLabels' => true,
                'MaxSpeakerLabels' => 2
            ],
        ]);
    }



    // Get Transcription Result
    public function getTranscribeResult($jobName)
    {
        $result = $this->transcribe->getTranscriptionJob([
            'TranscriptionJobName' => $jobName
        ]);

        $status = $result['TranscriptionJob']['TranscriptionJobStatus'];

        if ($status === 'COMPLETED') {
            $url = $result['TranscriptionJob']['Transcript']['TranscriptFileUri'];
            $json = file_get_contents($url);
            return json_decode($json, true);
        }

        if ($status === 'FAILED') {
            throw new Exception("Transcription failed");
        }

        return ['status' => $status]; // still in progress
    }


     // Delete file from S3 (optional)
    public function deleteFromS3($s3Path)
    {
        Storage::disk('s3')->delete($s3Path);
    }

    // Full flow: upload + start job + fetch result
    public function transcribe($file)
    {
        set_time_limit(300); // 5 minutes

        $s3Url = $this->uploadToS3($file);
        $jobName = 'transcribe_' . time();
        $this->startTranscribeJob($jobName, $s3Url);

        // Polling for job completion (basic, can use queue)
        $result = null;
        while(true) {
            $res = $this->getTranscribeResult($jobName);
            if(isset($res['status']) && $res['status'] === 'IN_PROGRESS') {
                sleep(5); // wait and retry
            } else {
                $result = $res;
                break;
            }
        }

        // Optional: delete file from S3
        $this->deleteFromS3($s3Url);

        return $result;
    }

    
    public function transcribeAudio(string $filePath): ?string
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

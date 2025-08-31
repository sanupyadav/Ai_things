<?php

namespace App\Http\Controllers;

use view;
use Illuminate\Http\Request;
use App\Services\AudioService;
use App\AiAgents\TranslationAgent;
use App\Http\Controllers\Controller;

class TranscriptionController extends Controller
{
    protected $audioService;

    public function __construct(AudioService $audioService)
    {
        $this->audioService = $audioService;
    }

    public function index()
    {
        return view('transcribe');
    }

    public function transcribe(Request $request)
    {
       set_time_limit(300); // sets execution time to 300 seconds (5 minutes)

        //dd($request->all());
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,flac|max:51200',
            'option' => 'required',
        ]);

        if ($request->option == 'AWS') {
            $result = $this->audioService->transcribe($request->file('audio'));
        } else {
            $result = include resource_path('data/aws-hi.php');

            $localData = [];

            // Access directly inside "results.transcripts"
            if (isset($result['results']['transcripts'])) {
                $localData['transcripts'] = $result['results']['transcripts'][0]['transcript'] ?? [];
            }
            
            if ($result['results']['audio_segments']) {
                foreach ($result['results']['audio_segments'] as $segment) {
                    $localData['audio_segments'][] = [
                        'id' => $segment['id'],
                        'transcript' => $segment['transcript'],
                        'start_time' => $segment['start_time'],
                        'end_time' => $segment['end_time'],
                        'speaker_label' => $segment['speaker_label'],
                    ];
                }
            }
           // dd($localData);
         $response = TranslationAgent::for('translation')->respond(json_encode($localData));
            $result = [
                'status' => 'success',
                'source' => 'local',
                'data' => $response,
                'transcripts' => $localData['transcripts'],
            ];
        }
        return response()->json($result);
        }
}

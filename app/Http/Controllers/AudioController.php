<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AudioService;
use App\AiAgents\SpeechToTextAgent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AudioController extends Controller
{
    public function index()
    {
        return view('audio-processor');
    }


    public function speechToText(Request $request)
{
   ini_set('max_execution_time', 300);
    // validate request
    $request->validate([
        'audio' => 'required|file|mimes:mp3,wav,ogg,flac|max:50480', // 20MB
    ]);

    // convert file to base64
    $audioFile = $request->file('audio');

    $res = new AudioService();
    $res = $res->transcribeAudio($audioFile->getRealPath());
   // dd($res);
    // send to a
    return $res;
}

    
    // public function speechToText(Request $request)
    // {
    //     try {
    //         // Validate request
    //         $request->validate([
    //             'audio' => 'required|file|mimes:mp3,wav,ogg,flac|max:50480',
    //         ]);

    //         // Store uploaded file
    //         $path = $request->file('audio')->store('audios/uploads', 'public');
    //         $audioPath = Storage::disk('public')->path($path);

    //         // Send audio to Agent
    //         $res = SpeechToTextAgent::for(auth()->id())
    //             ->withAudios($audioPath)
    //             ->respond("Please transcribe this audio into text.");

    //         return response()->json([
    //             'status' => 'success',
    //             'text'   => $res->output(), // depends on LarAgent response format
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->errors(),
    //         ], 422);

    //     } catch (\Exception $e) {
    //         Log::error("Speech-to-text failed: " . $e->getMessage());

    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}

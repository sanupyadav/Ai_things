<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AudioService;
use App\Http\Controllers\Controller;

class AudioController extends Controller
{
    protected $audioService;

    public function __construct(AudioService $audioService)
    {
        $this->audioService = $audioService;
    }


     public function index()
    {
        return view('audio-processor');
    }

    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,flac,m4a,aac|max:51200', // 50MB
        ]);

        $filePath = $request->file('audio')->getRealPath();
        $text = $this->audioService->transcribe($filePath);

        if (!$text) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to transcribe audio.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'text' => $text,
        ]);
    }
}

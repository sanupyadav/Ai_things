<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AudioService;
use App\Http\Controllers\Controller;

class AudioController extends Controller
{
    public function upload(Request $request, AudioService $audioService)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        // Validate file
        try {
      
        $request->validate([
            'audio' => 'required|mimes:mp3,wav,ogg|max:20480', // max 20MB
        ]);

        // Store file in public disk (audio/original folder)
        $storedPath = $request->file('audio')->store('audio/original', 'public');

        // Preprocess audio
        $processedPath = $audioService->preprocess(storage_path('app/public/' . $storedPath), $request->input('format', 'mp3'));

        return response()->json([
            'original'  => asset('storage/' . $storedPath),
            'processed' => asset('storage/processed_audio/' . basename($processedPath)),
        ]);
            
        } catch (\Throwable $th) {
          return response()->json(["status" => "error", "message" => $th->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AudioController extends Controller
{
    public function index()
    {
        return view('audio-processor');
    }

    // public function upload(Request $request)
    // {
    //     try {
    //         // validate request
    //         $request->validate([
    //             'audio'  => 'required|file|mimes:mp3,wav,ogg,flac', // 20MB
    //             'format' => 'required|string|in:mp3,wav,flac',
    //         ]);

    //         // store original file
    //         $path = $request->file('audio')->store('audios/original', 'public');
    //         $originalUrl = Storage::url($path);

    //         // fake processing (just copy file)
    //         $processedPath = str_replace('original', 'processed', $path);
    //         Storage::disk('public')->copy($path, $processedPath);
    //         $processedUrl = Storage::url($processedPath);

    //         return response()->json([
    //             'status'   => 'success',
    //             'message'  => 'Audio uploaded and processed successfully!',
    //             'format'   => $request->format,
    //             'original' => $originalUrl,
    //             'processed'=> $processedUrl,
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         // Validation error
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->errors(), // returns array of errors
    //         ], 422);
    //     } catch (\Exception $e) {
    //         // Unexpected error
    //         Log::error("Audio upload failed: " . $e->getMessage());

    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AudioService;
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
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,flac|max:51200'
        ]);

        $result = $this->audioService->transcribe($request->file('audio'));

        return response()->json($result);
    }
}

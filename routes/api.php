<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\TranscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload', [AudioController::class, 'upload'])->name('audio.upload');
// routes/web.php

Route::post('/speech-to-text', [AudioController::class, 'transcribe'])->name('speech.to.text');


Route::post('/transcribe', [TranscriptionController::class, 'transcribe']);


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\TranscriptionController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/agent/clear', [ChatController::class, 'clearHistoryFromAgent'])->name('chat.clear');

    Route::get('/agent', [ChatController::class, 'index'])->name('chat');

    Route::post('/agent/send', [ChatController::class, 'sendMessage'])->name('chat.send');

    Route::get('audio-processor', [AudioController::class, 'index'])->name('audio.processor');

    Route::get('/transcribe', [TranscriptionController::class, 'index'])->name('audio.aws');

    Route::post('/speech-to-text', [AudioController::class, 'transcribe'])->name('speech.to.text');



    Route::get('/review', function () {
    $data = [
        "customerSentiment" => "neutral",
        "guidelineAdherence" => "good",
        "issueResolution" => "partially_resolved",
        "communicationClarity" => "poor",
        "empathyLevel" => "low",
        "rating" => 7.5,
        "overallSummary" => "The customer service representative was able to resolve the issue partially but was unable to provide a clear explanation of the solution...",
        "keyCustomerImprovements" => [
            "Provide clear explanations for solutions",
            "Show empathy and understanding in conversations"
        ],
    ];
    return view('review', compact('data'));
});
});

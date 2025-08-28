<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AudioController;

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


Route::post('/speech-to-text', [AudioController::class, 'transcribe'])->name('speech.to.text');

});

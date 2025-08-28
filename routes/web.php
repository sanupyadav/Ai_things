<?php

use App\Http\Controllers\AudioController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

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

    //      Route::get('/audio-processor', function () {
    //        return view('audio-processor');
    //    });

});

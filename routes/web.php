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

    Route::post('/chat/clear', [ChatController::class, 'clearHistoryFromAgent'])->name('chat.clear');
    
    Route::get('/agent', [ChatController::class, 'index'])->name('chat');
    
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    
    Route::post('/audio/upload', [AudioController::class, 'upload']);

});         

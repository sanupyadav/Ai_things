<?php

use App\Http\Controllers\AudioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload', [AudioController::class, 'upload'])->name('audio.upload');

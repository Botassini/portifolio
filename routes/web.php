<?php

use App\Http\Controllers\YoutubeMusicController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/youtube/download', [YoutubeMusicController::class, 'download'])->name('youtube.download');
Route::post('/youtube/preview', [YoutubeMusicController::class, 'preview'])->name('youtube.preview');

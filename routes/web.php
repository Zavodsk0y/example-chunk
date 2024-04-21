<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload');
});

Route::post('/upload-chunk', [FileController::class, 'uploadChunk']);

Route::post('/upload', [\App\Http\Controllers\FileController::class, 'upload']);

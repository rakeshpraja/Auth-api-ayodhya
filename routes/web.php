<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('index', [ProfileController::class, 'fileForm']);
Route::post('/upload', [ProfileController::class, 'upload'])->name('file.upload');
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;




Route::post('register',[AuthController::class,'register']);
Route::get('verify/{token}',[AuthController::class,'verify'])->name('verify.user');

Route::post('login',[AuthController::class,'login']);

Route::post('password_forgot', [AuthController::class, 'forgotPassword']);
Route::post('password_reset/', [AuthController::class, 'resetPassword']);


Route::middleware('auth:api')->group(function () {
    Route::post('update_profile',[AuthController::class,'updateProfile']);
    
    Route::post('update_profile_verify',[AuthController::class,'verifyUpdateProfile'])->name('update.profileverify');
    Route::post('logout',[AuthController::class,'logout']);
});
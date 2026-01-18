<?php

use App\Http\Controllers\Api\V1\chat\ChatController;
use App\Http\Controllers\Api\V1\pdf\PdfController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::prefix('users')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware(['throttle:3,1', 'guest']);
        Route::post('login', [AuthController::class, 'login'])->middleware(['throttle:5,1', 'guest']);

        Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
            Route::patch('update-profile', [AuthController::class, 'updateProfile']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/pdf/upload', [PdfController::class, 'upload'])->middleware(['throttle:3,1']);
        Route::post('/chat', [ChatController::class, 'ask'])->middleware(['throttle:60,1']);
    });
});

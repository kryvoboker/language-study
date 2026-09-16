<?php
use httpdocs\backend\app\Http\Controllers\Api\V1\MeController;
use httpdocs\backend\app\Http\Controllers\Api\V1\TranslationRequestController;
use httpdocs\backend\app\Http\Controllers\Auth\ForgotPasswordController;
use httpdocs\backend\app\Http\Controllers\Auth\LoginController;
use httpdocs\backend\app\Http\Controllers\Auth\RegisterController;
use httpdocs\backend\app\Http\Controllers\Auth\ResetPasswordController;
use httpdocs\backend\app\Http\Controllers\Auth\SocialExchangeController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', RegisterController::class)->middleware('throttle:10,1');
    Route::post('/auth/login', LoginController::class)->middleware('throttle:10,1');
    Route::post('/auth/social/exchange', SocialExchangeController::class)->middleware('throttle:10,1');
    Route::post('/auth/forgot-password', ForgotPasswordController::class)->middleware('throttle:5,1');
    Route::post('/auth/reset-password', ResetPasswordController::class)->middleware('throttle:5,1');
    Route::middleware(['auth:api', 'verified'])->group(function (): void {
        Route::get('/me', MeController::class);
        Route::post('/translation-requests', [TranslationRequestController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/translation-requests/{translation_request}', [TranslationRequestController::class, 'show']);
        Route::delete('/translation-requests/{translation_request}', [TranslationRequestController::class, 'destroy']);
    });
});
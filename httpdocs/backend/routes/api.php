<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ContactRequestController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\TranslationRequestController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SocialExchangeController;
use App\Http\Middleware\AuthenticateApiOrWeb;
use App\Http\Middleware\EnsureTranslationAccess;
use App\Http\Middleware\ResolveOptionalApiOrWebUser;
use App\Http\Middleware\VerifySessionCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/contact-requests', ContactRequestController::class)
        ->middleware([
            'throttle:5,1',
            StartSession::class,
            ResolveOptionalApiOrWebUser::class,
            VerifySessionCsrfToken::class,
        ]);

    Route::post('/auth/register', RegisterController::class)->middleware('throttle:10,1');
    Route::post('/auth/login', LoginController::class)->middleware('throttle:10,1');
    Route::post('/auth/social/exchange', SocialExchangeController::class)->middleware('throttle:10,1');
    Route::post('/auth/forgot-password', ForgotPasswordController::class)->middleware('throttle:5,1');
    Route::post('/auth/reset-password', ResetPasswordController::class)->middleware('throttle:5,1');
    // TODO: Restore the authenticated verification-notification endpoint if email verification is re-enabled.
    Route::middleware([
        StartSession::class,
        AuthenticateApiOrWeb::class,
        VerifySessionCsrfToken::class,
    ])->group(function (): void {
        Route::get('/me', MeController::class);

        Route::middleware([EnsureTranslationAccess::class])->group(function (): void {
            Route::post('/translation-requests', [TranslationRequestController::class, 'store'])->middleware('throttle:60,1');
            Route::get('/translation-requests/{translation_request}', [TranslationRequestController::class, 'show']);
            Route::delete('/translation-requests/{translation_request}', [TranslationRequestController::class, 'destroy']);
        });
    });
});

<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\WebLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/social/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
// TODO: Restore the signed email-verification route and controller if email verification is re-enabled.
/** @var view-string $swagger_view */
$swagger_view = 'swagger';
Route::view('/docs/api', $swagger_view)->name('docs.api');

<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\WebLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/social/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware('signed')->name('verification.verify');
Route::view('/docs/api', 'swagger')->name('docs.api');

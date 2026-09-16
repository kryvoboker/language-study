<?php
use httpdocs\backend\app\Http\Controllers\Auth\SocialAuthController;
use httpdocs\backend\app\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
Route::get('/auth/social/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');
Route::view('/docs/api', 'swagger')->name('docs.api');
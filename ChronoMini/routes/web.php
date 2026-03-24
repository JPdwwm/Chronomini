<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [AuthController::class, 'authenticate'])
    ->name('authenticate')
    ->middleware('throttle:login');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

    // Routes pour le reset password
Route::post('/forgot-password', [PasswordResetController::class, 'requestReset'])->name('password.request')->middleware('throttle:password-reset');
Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken'])->name('password.verify')->middleware('throttle:password-reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset')->middleware('throttle:password-reset');

    // Route pour le formulaire de contact
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit')->middleware('throttle:contact');

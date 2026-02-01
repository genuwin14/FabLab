<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Socialite Routes
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Protected Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Phone Verification
    Route::get('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'show'])->name('verify.code');
    Route::post('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('verify.code.submit');
    Route::post('/verify-code/resend', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('verify.code.resend');

    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/staff/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('staff.dashboard');

    Route::get('/customer/shop', [\App\Http\Controllers\Customer\ShopController::class, 'index'])->name('customer.shop');

});

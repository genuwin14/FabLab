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

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'checkUserForReset'])->name('password.email.check');
Route::post('/forgot-password/send', [AuthController::class, 'sendResetCode'])->name('password.code.send');
Route::get('/forgot-password/verify', [AuthController::class, 'showResetVerificationForm'])->name('password.verify.show');
Route::post('/forgot-password/verify', [AuthController::class, 'verifyResetCode'])->name('password.verify.submit');

Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Phone Verification
    Route::get('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'show'])->name('verify.code');
    Route::post('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('verify.code.submit');
    Route::post('/verify-code/resend', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('verify.code.resend');

    // Admin Routes
    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/admin/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::get('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/admin/suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('admin.suppliers.index');
    Route::post('/admin/suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('admin.suppliers.store');
    Route::put('/admin/suppliers/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('admin.suppliers.update');
    Route::delete('/admin/suppliers/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');

    // Staff Routes
    Route::get('/staff/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('staff.dashboard');

    // Customer Routes
    Route::get('/customer/shop', [\App\Http\Controllers\Customer\ShopController::class, 'index'])->name('customer.shop');

});

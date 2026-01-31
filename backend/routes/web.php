<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Placeholder auth routes - replace with actual auth logic later
Route::get('/login', function () {
    return 'Login Page Placeholder'; })->name('login');
Route::get('/register', function () {
    return 'Register Page Placeholder'; })->name('register');

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        // TODO: Implement actual login logic
        return redirect()->route('landing');
    }

    public function register(Request $request)
    {
        // TODO: Implement actual registration logic
        return redirect()->route('login');
    }
}

@extends('layout.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background-color: #05111a;">
        <!-- Background Decor -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events: none;">
            <div class="position-absolute top-0 start-50 translate-middle bg-primary bg-opacity-20 rounded-circle blur-3xl opacity-50"
                style="width: 600px; height: 600px; filter: blur(100px);"></div>
        </div>

        <!-- Logo Top Left -->
        <a href="{{ route('landing') }}" class="position-absolute top-0 start-0 m-4 z-3">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" style="height: 60px;">
        </a>

        <div class="container position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="p-4 p-md-5 rounded-4 shadow-lg border border-white border-opacity-10 backdrop-blur text-center"
                        style="background-color: rgba(255, 255, 255, 0.05);">

                        <!-- Header -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-white">Welcome Back</h4>
                            <p class="text-white-50 small">Sign in to continue to FABLAB</p>
                        </div>

                        <form action="{{ route('login') }}" method="POST">
                            @csrf

                            <!-- Google Button -->
                            <button type="button"
                                class="btn btn-light w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 mb-4">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                    style="width: 20px; height: 20px;">
                                <span>Continue with Google</span>
                            </button>

                            <!-- Divider -->
                            <div class="d-flex align-items-center mb-4">
                                <hr class="flex-grow-1 border-white border-opacity-10">
                                <span class="px-3 text-white-50 small text-small">or login with email</span>
                                <hr class="flex-grow-1 border-white border-opacity-10">
                            </div>

                            <!-- Email -->
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label small fw-bold text-white-50 ms-1">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text border-white border-opacity-10 text-white-50"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white"
                                        placeholder="name@company.com"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required autofocus>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3 text-start">
                                <label for="password" class="form-label small fw-bold text-white-50 ms-1">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-white border-opacity-10 text-white-50"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white"
                                        placeholder="Enter your password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                </div>
                            </div>

                            <!-- Forgot Password -->
                            <div class="d-flex justify-content-end mb-4">
                                <a href="#"
                                    class="small text-accent text-decoration-none hover-text-white transition-colors">Forgot
                                    Password?</a>
                            </div>

                            <!-- Login Button -->
                            <button type="submit" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                Log In
                            </button>
                        </form>

                        <!-- Register Link -->
                        <div class="mt-4 text-center">
                            <p class="text-white-50 small mb-0">
                                Don't have an account?
                                <a href="{{ route('register') }}" class="text-white fw-bold text-decoration-none ms-1">Sign
                                    Up</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom placeholder color override */
        ::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
            opacity: 1;
        }

        .input-group-text {
            border-right: none;
        }

        .form-control:focus {
            background-color: rgba(0, 0, 0, 0.4) !important;
            border-color: #ffc508 !important;
            color: white !important;
            box-shadow: none;
        }

        /* Ensure input group text border color matches focus */
        .form-control:focus+.input-group-text,
        .input-group-text:has(+ .form-control:focus) {
            border-color: #ffc508 !important;
        }
    </style>
@endsection
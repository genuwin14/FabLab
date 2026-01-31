@extends('layout.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background-color: #05111a;">
        <!-- Background Decor -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events: none;">
            <!-- Removed yellow circle -->
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
                            <h4 class="fw-bold text-white">Create Account</h4>
                            <p class="text-white-50 small">Join FABLAB to manage your inventory smarter</p>
                        </div>

                        <form action="{{ route('register') }}" method="POST">
                            @csrf

                            <!-- Google Button -->
                            <button type="button"
                                class="btn btn-light w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 mb-4">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                    style="width: 20px; height: 20px;">
                                <span>Sign Up with Google</span>
                            </button>

                            <!-- Divider -->
                            <div class="d-flex align-items-center mb-4">
                                <hr class="flex-grow-1 border-white border-opacity-10">
                                <span class="px-3 text-white-50 small text-small">or sign up with email</span>
                                <hr class="flex-grow-1 border-white border-opacity-10">
                            </div>

                            <!-- Name -->
                            <div class="mb-3 text-start">
                                <label for="name" class="form-label small fw-bold text-white-50 ms-1">Full Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control border border-white border-opacity-10 text-white"
                                    placeholder="John Doe" style="background-color: rgba(0,0,0,0.3); color: white;" required
                                    autofocus>
                            </div>

                            <!-- Email -->
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label small fw-bold text-white-50 ms-1">Email Address</label>
                                <input type="email" name="email" id="email"
                                    class="form-control border border-white border-opacity-10 text-white"
                                    placeholder="name@company.com" style="background-color: rgba(0,0,0,0.3); color: white;"
                                    required>
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3 text-start">
                                <label for="phone" class="form-label small fw-bold text-white-50 ms-1">Phone Number</label>
                                <input type="tel" name="phone" id="phone"
                                    class="form-control border border-white border-opacity-10 text-white"
                                    placeholder="+1 (555) 000-0000" style="background-color: rgba(0,0,0,0.3); color: white;"
                                    required>
                            </div>

                            <!-- Password -->
                            <div class="mb-3 text-start">
                                <label for="password" class="form-label small fw-bold text-white-50 ms-1">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control border-end-0 border border-white border-opacity-10 text-white"
                                        placeholder="Create a password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                    <span class="input-group-text border-white border-opacity-10 text-white-50"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-eye-slash"></i></span>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4 text-start">
                                <label for="password_confirmation"
                                    class="form-label small fw-bold text-white-50 ms-1">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control border border-white border-opacity-10 text-white"
                                    placeholder="Confirm your password"
                                    style="background-color: rgba(0,0,0,0.3); color: white;" required>
                            </div>

                            <!-- Register Button -->
                            <button type="submit" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                Create Account
                            </button>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="text-white-50 small mb-0">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-white fw-bold text-decoration-none ms-1">Log
                                    In</a>
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

        .form-control:focus {
            background-color: rgba(0, 0, 0, 0.4) !important;
            border-color: #ffc508 !important;
            color: white !important;
            box-shadow: none;
        }
    </style>
@endsection
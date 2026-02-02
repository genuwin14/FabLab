@extends('layout.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="background-color: #05111a;">
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
                            <h4 class="fw-bold text-white">Reset Password</h4>
                            <p class="text-white-50 small">Enter your new password below.</p>
                        </div>

                        <!-- Form -->
                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf

                            <!-- Password -->
                            <div class="mb-3 text-start">
                                <label for="password" class="form-label small fw-bold text-white-50 ms-1">New
                                    Password</label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('password') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white @error('password') border-danger is-invalid @enderror"
                                        placeholder="Enter new password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1 ms-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4 text-start">
                                <label for="password_confirmation"
                                    class="form-label small fw-bold text-white-50 ms-1">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-white border-opacity-10 text-white-50"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white"
                                        placeholder="Confirm new password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                Reset Password
                            </button>
                        </form>
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

        .form-control:focus+.input-group-text,
        .input-group-text:has(+ .form-control:focus) {
            border-color: #ffc508 !important;
        }
    </style>
@endsection
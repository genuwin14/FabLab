@extends('layout.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 pt-auth-mobile" style="background-color: #05111a;">
        <!-- Background Decor -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events: none;">
            <!-- Removed yellow circle -->
        </div>

        <!-- Logo Top Left -->
        <a href="{{ route('landing') }}" class="position-absolute top-0 start-0 m-3 m-md-4 z-3 auth-logo-link">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" class="auth-logo">
        </a>

        <div class="container position-relative z-1 px-3 px-sm-4">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="auth-card p-3 p-sm-4 p-md-5 rounded-4 shadow-lg border border-white border-opacity-10 backdrop-blur text-center"
                        style="background-color: rgba(255, 255, 255, 0.05);">

                        <!-- Header -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-white">Verification Required</h4>
                            <p class="text-white-50 small">
                                @if (session('verification_mode') == 'email')
                                    Enter the 6-digit code sent to your email address: <br>
                                    <span class="text-white">{{ auth()->user()->email }}</span>
                                @else
                                    Enter the 6-digit code sent to your phone number ending in: <br>
                                    <span class="text-white">{{ substr(auth()->user()->contact_number, -4) }}</span>
                                @endif
                            </p>
                        </div>




                        <form action="{{ route('verify.code.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="verification_mode"
                                value="{{ session('verification_mode', request('verification_mode')) }}">

                            <!-- OTP Input -->
                            <div class="mb-4">
                                <label for="otp" class="form-label small fw-bold text-white-50 ms-1 d-none">OTP
                                    Code</label>
                                <input type="text" name="otp" id="otp" inputmode="numeric" pattern="[0-9]*"
                                    class="form-control text-center text-white border-white border-opacity-10 fw-bold otp-input"
                                    placeholder="000000" maxlength="6"
                                    style="background-color: rgba(0,0,0,0.3);" required autofocus>
                            </div>

                            <!-- Verify Button -->
                            <button type="submit" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                Verify Code
                            </button>
                        </form>

                        <!-- Resend Link -->
                        <div class="text-center">
                            <p class="text-white-50 small mb-0">
                                Didn't receive the code?
                            <form action="{{ route('verify.code.resend') }}" method="POST" id="resend-form"
                                class="d-inline">
                                @csrf
                                <input type="hidden" name="verification_mode"
                                    value="{{ session('verification_mode', request('verification_mode')) }}">
                                <button type="submit"
                                    class="btn btn-link p-0 text-white fw-bold text-decoration-none ms-1 border-0 bg-transparent"
                                    style="font-size: inherit;">Resend Code</button>
                            </form>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Responsive logo */
        .auth-logo {
            height: 60px;
        }

        @media (max-width: 575.98px) {
            .auth-logo {
                height: 42px;
            }
        }

        @media (max-width: 767.98px) {
            .pt-auth-mobile {
                padding-top: 5rem !important;
            }
            .auth-card h4 {
                font-size: 1.2rem;
            }
            .auth-card .btn {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 575.98px) {
            .auth-card h4 {
                font-size: 1.1rem;
            }
            .auth-card p.small {
                font-size: 0.75rem;
            }
            .auth-card .btn {
                font-size: 0.82rem;
                padding: 0.45rem 0.75rem;
            }
            .auth-card .mb-4 {
                margin-bottom: 1rem !important;
            }
        }

        /* Responsive OTP input */
        .otp-input {
            font-size: 2rem;
            letter-spacing: 0.5em;
            padding-left: 0.5em;
        }

        @media (max-width: 575.98px) {
            .otp-input {
                font-size: 1.4rem;
                letter-spacing: 0.35em;
                padding-left: 0.35em;
            }
        }

        @media (max-width: 360px) {
            .otp-input {
                font-size: 1.15rem;
                letter-spacing: 0.25em;
                padding-left: 0.25em;
            }
        }

        /* Custom placeholder color override */
        ::placeholder {
            color: rgba(255, 255, 255, 0.2) !important;
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
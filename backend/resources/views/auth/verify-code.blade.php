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
                                <input type="text" name="otp" id="otp"
                                    class="form-control text-center text-white border-white border-opacity-10 fs-2 fw-bold letter-spacing-2"
                                    placeholder="000000" maxlength="6"
                                    style="background-color: rgba(0,0,0,0.3); letter-spacing: 0.5em;" required autofocus>
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
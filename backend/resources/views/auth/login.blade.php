@extends('layout.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 pt-auth-mobile" style="background-color: #05111a;">
        <!-- Background Decor -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events: none;">
            <div class="position-absolute top-0 start-50 translate-middle bg-primary bg-opacity-20 rounded-circle blur-3xl opacity-50 auth-bg-blur"
                style="width: 600px; height: 600px; filter: blur(100px);"></div>
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
                            <h4 class="fw-bold text-white">Welcome Back</h4>
                            <p class="text-white-50 small">Sign in to continue to FABLAB</p>
                        </div>

                        <form action="{{ route('login') }}" method="POST">
                            @csrf

                            <!-- Google Button -->
                            <a href="{{ route('auth.google') }}"
                                class="btn btn-light w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 mb-4 text-decoration-none">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                    style="width: 20px; height: 20px;">
                                <span>Continue with Google</span>
                            </a>

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
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('email') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white @error('email') border-danger is-invalid @enderror"
                                        placeholder="name@company.com" value="{{ old('email') }}"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3 text-start">
                                <label for="password" class="form-label small fw-bold text-white-50 ms-1">Password</label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('password') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control border-start-0 border-end-0 border-white border-opacity-10 text-white @error('password') border-danger is-invalid @enderror"
                                        placeholder="Enter your password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 password-toggle @error('password') border-danger text-danger @enderror"
                                        id="togglePassword"
                                        style="background-color: rgba(0,0,0,0.3); cursor: pointer;"
                                        role="button" tabindex="0" aria-label="Show password">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Forgot Password -->
                            <div class="d-flex justify-content-end align-items-center mb-4">
                                <a href="{{ route('password.request') }}"
                                    class="small text-accent text-decoration-none hover-text-white transition-colors">Forgot
                                    Password?</a>
                            </div>

                            <!-- Login Button -->
                            <button type="submit" id="loginButton" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                <span id="loginButtonText">Log In</span>
                            </button>
                        </form>

                        <!-- Register Link -->
                        <div class="text-center">
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

    <!-- Verification Mode Selection Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark"
                style="background-color: #0d2235; color: white; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-bottom border-white border-opacity-10">
                    <h5 class="modal-title fw-bold" id="verificationModalLabel">Verify Your Account</h5>
                    <!-- No close button here to force selection -->
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <p class="mb-0 text-white-50">Your account is not verified yet. <br> How
                            would you like to receive your verification code?</p>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <form action="{{ route('verify.code.resend') }}" method="POST" id="verification-form">
                            @csrf
                            <input type="hidden" name="verification_mode" value="">

                            <button type="button"
                                class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option w-100 mb-3"
                                onclick="selectVerification('sms', this)">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-chat-square-dots-fill fs-4 text-accent"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-bold">Via SMS</h6>
                                        <small class="text-white-50">Code sent to your number</small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-white-50"></i>
                            </button>

                            <button type="button"
                                class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option w-100"
                                onclick="selectVerification('email', this)">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-envelope-fill fs-4 text-accent"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-bold">Via Email</h6>
                                        <small class="text-white-50">Code sent to your email</small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-white-50"></i>
                            </button>
                        </form>
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
            .auth-bg-blur {
                width: 360px !important;
                height: 360px !important;
            }
        }

        @media (max-width: 767.98px) {
            .pt-auth-mobile {
                padding-top: 5rem !important;
            }
        }

        /* Tablet: slight scale-down of typography & spacing */
        @media (max-width: 767.98px) {
            .auth-card h4 {
                font-size: 1.2rem;
            }
            .auth-card .form-control,
            .auth-card .input-group-text {
                font-size: 0.9rem;
            }
            .auth-card .btn {
                font-size: 0.9rem;
            }
        }

        /* Mobile: tighter typography, components, and spacing */
        @media (max-width: 575.98px) {
            .auth-card h4 {
                font-size: 1.1rem;
            }
            .auth-card p.small {
                font-size: 0.75rem;
            }
            .auth-card .form-label {
                font-size: 0.72rem;
            }
            .auth-card .form-control,
            .auth-card .input-group-text {
                font-size: 0.82rem;
                padding-top: 0.4rem;
                padding-bottom: 0.4rem;
            }
            .auth-card .btn {
                font-size: 0.82rem;
                padding: 0.45rem 0.75rem;
            }
            .auth-card .btn-light img {
                width: 16px !important;
                height: 16px !important;
            }
            .auth-card .text-small {
                font-size: 0.7rem;
            }
            .auth-card .mb-4 {
                margin-bottom: 1rem !important;
            }
            .auth-card .mb-3 {
                margin-bottom: 0.75rem !important;
            }
            /* Verification modal scale-down */
            #verificationModal .modal-body {
                padding: 1rem !important;
            }
            #verificationModal .verification-option {
                padding: 0.65rem !important;
            }
            #verificationModal .verification-option .fs-4 {
                font-size: 1.1rem !important;
            }
            #verificationModal .verification-option h6 {
                font-size: 0.85rem;
            }
            #verificationModal .verification-option small {
                font-size: 0.7rem;
            }
            #verificationModal .modal-title {
                font-size: 1rem;
            }
        }

        /* Custom placeholder color override */
        ::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
            opacity: 1;
        }

        .input-group-text:not(.password-toggle) {
            border-right: none;
        }

        .password-toggle {
            border-left: none;
            transition: color 0.15s ease-in-out;
        }

        .password-toggle:hover {
            color: #ffc508 !important;
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

        .verification-option:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: #ffc508;
        }

        .text-accent {
            color: #ffc508 !important;
        }

        /* Keep login button bright while disabled (loading state) */
        #loginButton:disabled,
        #loginButton.disabled {
            opacity: 1 !important;
            background-color: #ffc508 !important;
            border-color: #ffc508 !important;
            color: #05111a !important;
            cursor: progress;
        }

        #loginButton:disabled .spinner-border,
        #loginButton.disabled .spinner-border {
            color: #05111a;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Show Password Toggle (eye icon)
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput && togglePasswordIcon) {
                const toggleVisibility = () => {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    togglePasswordIcon.classList.toggle('bi-eye', !isPassword);
                    togglePasswordIcon.classList.toggle('bi-eye-slash', isPassword);
                    togglePassword.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                };

                togglePassword.addEventListener('click', toggleVisibility);
                togglePassword.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleVisibility();
                    }
                });
            }

            // Login Button Loading State
            const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
            const loginButton = document.getElementById('loginButton');
            const loginButtonText = document.getElementById('loginButtonText');

            if (loginForm && loginButton && loginButtonText) {
                loginForm.addEventListener('submit', function () {
                    loginButton.disabled = true;
                    loginButtonText.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...`;
                });
            }

            @if(isset($showVerificationModal) && $showVerificationModal)
                const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
                modal.show();
            @endif

            window.selectVerification = function(mode, btnElement) {
                const form = document.getElementById('verification-form');
                const input = form.querySelector('input[name="verification_mode"]');
                input.value = mode;

                // Add loading state
                const OriginalContent = btnElement.innerHTML;
                btnElement.innerHTML = `<span class="spinner-border spinner-border-sm text-accent me-2" role="status" aria-hidden="true"></span> Sending Code...`;
                btnElement.disabled = true;

                // Disable other button
                const buttons = document.querySelectorAll('.verification-option');
                buttons.forEach(btn => {
                    if (btn !== btnElement) btn.disabled = true;
                    btn.classList.add('opacity-50');
                });

                form.submit();
            };
        });
    </script>
@endsection
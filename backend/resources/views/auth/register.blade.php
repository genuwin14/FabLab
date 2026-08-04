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
                            <h4 class="fw-bold text-white">Create Account</h4>
                            <p class="text-white-50 small">Join FABLAB to manage your inventory smarter</p>
                        </div>

                        <form action="{{ route('register') }}" method="POST">
                            @csrf

                            <!-- Google Button -->
                            <a href="{{ route('auth.google') }}"
                                class="btn btn-light w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 mb-4 text-decoration-none">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                    style="width: 20px; height: 20px;">
                                <span>Sign Up with Google</span>
                            </a>

                            <!-- Divider -->
                            <div class="d-flex align-items-center mb-4">
                                <hr class="flex-grow-1 border-white border-opacity-10">
                                <span class="px-3 text-white-50 small text-small">or sign up with email</span>
                                <hr class="flex-grow-1 border-white border-opacity-10">
                            </div>

                            <!-- Name -->
                            <div class="mb-3 text-start">
                                <label for="name" class="form-label small fw-bold text-white-50 ms-1">Full Name</label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('name') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" id="name"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white @error('name') border-danger is-invalid @enderror"
                                        placeholder="John Doe" value="{{ old('name') }}"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required autofocus>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
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
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3 text-start">
                                <label for="phone" class="form-label small fw-bold text-white-50 ms-1">Phone Number</label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('phone') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" id="phone"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white @error('phone') border-danger is-invalid @enderror"
                                        placeholder="+1 (555) 000-0000" value="{{ old('phone') }}"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                </div>
                                @error('phone')
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
                                        placeholder="Create a password"
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

                            <!-- Confirm Password -->
                            <div class="mb-4 text-start">
                                <label for="password_confirmation"
                                    class="form-label small fw-bold text-white-50 ms-1">Confirm Password</label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 @error('password_confirmation') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control border-start-0 border-end-0 border-white border-opacity-10 text-white @error('password_confirmation') border-danger is-invalid @enderror"
                                        placeholder="Confirm your password"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required>
                                    <span
                                        class="input-group-text border-white border-opacity-10 text-white-50 password-toggle @error('password_confirmation') border-danger text-danger @enderror"
                                        id="togglePasswordConfirm"
                                        style="background-color: rgba(0,0,0,0.3); cursor: pointer;"
                                        role="button" tabindex="0" aria-label="Show password">
                                        <i class="bi bi-eye" id="togglePasswordConfirmIcon"></i>
                                    </span>
                                </div>
                                @error('password_confirmation')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Register Button -->
                            <button type="submit" id="registerButton" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                <span id="registerButtonText">Create Account</span>
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

    <!-- Verification Mode Selection Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark"
                style="background-color: #0d2235; color: white; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-bottom border-white border-opacity-10">
                    <h5 class="modal-title fw-bold" id="verificationModalLabel">Verify Your Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <!-- <div class="bg-primary bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3"
                                        style="width: 80px; height: 80px;">
                                        <i class="bi bi-shield-lock-fill fs-1 text-primary"></i>
                                    </div> -->
                        <p class="mb-0 text-white-50">To secure your account, we need to verify your identity. <br> How
                            would you like to receive your verification code?</p>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <button type="button"
                            class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option"
                            onclick="selectVerification('sms', this)">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-chat-square-dots-fill fs-4 text-accent"></i>
                                <div class="text-start">
                                    <h6 class="mb-0 fw-bold">Via SMS</h6>
                                    <small class="text-white-50">Code sent to mobile number</small>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-white-50"></i>
                        </button>

                        <button type="button"
                            class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option"
                            onclick="selectVerification('email', this)">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-envelope-fill fs-4 text-accent"></i>
                                <div class="text-start">
                                    <h6 class="mb-0 fw-bold">Via Email</h6>
                                    <small class="text-white-50">Code sent to email address</small>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-white-50"></i>
                        </button>
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
            .auth-card .form-control,
            .auth-card .input-group-text {
                font-size: 0.9rem;
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
                margin-bottom: 0.9rem !important;
            }
            .auth-card .mb-3 {
                margin-bottom: 0.7rem !important;
            }
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

        .btn-accent {
            background-color: #ffc508;
            color: #000;
            border: none;
        }

        .btn-accent:hover {
            background-color: #e0ac00;
            color: #000;
        }

        /* Keep register button bright while disabled (loading state) */
        #registerButton:disabled,
        #registerButton.disabled {
            opacity: 1 !important;
            background-color: #ffc508 !important;
            border-color: #ffc508 !important;
            color: #05111a !important;
            cursor: progress;
        }

        #registerButton:disabled .spinner-border,
        #registerButton.disabled .spinner-border {
            color: #05111a;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[action="{{ route('register') }}"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const registerButton = document.getElementById('registerButton');
            const registerButtonText = document.getElementById('registerButtonText');
            let verificationMode = '';

            // Password Visibility Toggles (eye icons)
            const setupPasswordToggle = (toggleId, iconId, inputId) => {
                const toggle = document.getElementById(toggleId);
                const icon = document.getElementById(iconId);
                const input = document.getElementById(inputId);
                if (!toggle || !icon || !input) return;

                const toggleVisibility = () => {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    icon.classList.toggle('bi-eye', !isPassword);
                    icon.classList.toggle('bi-eye-slash', isPassword);
                    toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                };

                toggle.addEventListener('click', toggleVisibility);
                toggle.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleVisibility();
                    }
                });
            };

            setupPasswordToggle('togglePassword', 'togglePasswordIcon', 'password');
            setupPasswordToggle('togglePasswordConfirm', 'togglePasswordConfirmIcon', 'password_confirmation');

            // Intercept form submission — show verification modal first
            form.addEventListener('submit', function (e) {
                if (!verificationMode) {
                    e.preventDefault();
                    // Show validation first (basic HTML5 validation check)
                    if (form.checkValidity()) {
                        const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
                        modal.show();
                    } else {
                        form.reportValidity();
                    }
                } else if (registerButton && registerButtonText) {
                    // Real submit (after verification mode selected) — show loading state
                    registerButton.disabled = true;
                    registerButtonText.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Account...`;
                }
            });

            // Handle verification selection
            window.selectVerification = function (mode, btnElement) {
                verificationMode = mode;

                // Add loading state to clicked button
                const OriginalContent = btnElement.innerHTML;
                btnElement.innerHTML = `<span class="spinner-border spinner-border-sm text-accent me-2" role="status" aria-hidden="true"></span> Sending Code...`;
                btnElement.disabled = true;

                // Disable other button
                const buttons = document.querySelectorAll('.verification-option');
                buttons.forEach(btn => {
                    if (btn !== btnElement) btn.disabled = true;
                    btn.classList.add('opacity-50');
                });

                // Add hidden input for verification mode
                let modeInput = form.querySelector('input[name="verification_mode"]');
                if (!modeInput) {
                    modeInput = document.createElement('input');
                    modeInput.type = 'hidden';
                    modeInput.name = 'verification_mode';
                    form.appendChild(modeInput);
                }
                modeInput.value = mode;

                // Submit form (don't close modal, let page reload handle it)
                form.submit();
            };
        });
    </script>
@endsection
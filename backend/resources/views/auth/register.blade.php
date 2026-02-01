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

    <!-- Verification Mode Selection Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[action="{{ route('register') }}"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            let verificationMode = '';

            // Password Toggle
            const togglePassword = document.querySelector('.input-group-text');
            const passwordInput = document.getElementById('password');
            const eyeIcon = togglePassword.querySelector('i');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.remove('bi-eye-slash');
                        eyeIcon.classList.add('bi-eye');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('bi-eye');
                        eyeIcon.classList.add('bi-eye-slash');
                    }
                });
            }

            // Intercept form submission
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
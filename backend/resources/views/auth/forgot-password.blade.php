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

                        <!-- Back Link -->
                        <div class="d-flex justify-content-start mb-3">
                            <a href="{{ route('login') }}"
                                class="text-white-50 text-decoration-none small hover-text-white transition-colors">
                                <i class="bi bi-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>

                        <!-- Header -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-white">Forgot Password?</h4>
                            <p class="text-white-50 small">Enter your email to search for your account.</p>
                        </div>

                        <form action="{{ route('password.email.check') }}" method="POST">
                            @csrf

                            <!-- Email -->
                            <div class="mb-4 text-start">
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
                                    <div class="text-danger small mt-1 ms-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Search Button -->
                            <button type="submit" class="btn btn-accent w-100 fw-bold py-2 mb-4 shadow-sm">
                                Search Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Mode Selection Modal -->
    <div class="modal fade" id="methodSelectionModal" tabindex="-1" aria-labelledby="methodSelectionModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark"
                style="background-color: #0d2235; color: white; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-bottom border-white border-opacity-10">
                    <h5 class="modal-title fw-bold" id="methodSelectionModalLabel">Select Recovery Method</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <p class="mb-0 text-white-50">Account found! <br> How would you like to receive your password reset
                            code?</p>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <form action="{{ route('password.code.send') }}" method="POST" id="method-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email ?? '' }}">
                            <input type="hidden" name="verification_mode" value="">

                            <button type="button"
                                class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option w-100 mb-3"
                                onclick="selectMethod('sms', this)">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-chat-square-dots-fill fs-4 text-accent"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-bold">Via SMS</h6>
                                        <small class="text-white-50">Code sent to {{ $maskedPhone ?? 'your phone' }}</small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-white-50"></i>
                            </button>

                            <button type="button"
                                class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 position-relative verification-option w-100"
                                onclick="selectMethod('email', this)">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-envelope-fill fs-4 text-accent"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-bold">Via Email</h6>
                                        <small class="text-white-50">Code sent to {{ $maskedEmail ?? 'your email' }}</small>
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
            .auth-card .mb-4 {
                margin-bottom: 1rem !important;
            }
            .auth-card .mb-3 {
                margin-bottom: 0.75rem !important;
            }
            #methodSelectionModal .modal-body {
                padding: 1rem !important;
            }
            #methodSelectionModal .verification-option {
                padding: 0.65rem !important;
            }
            #methodSelectionModal .verification-option .fs-4 {
                font-size: 1.1rem !important;
            }
            #methodSelectionModal .verification-option h6 {
                font-size: 0.85rem;
            }
            #methodSelectionModal .verification-option small {
                font-size: 0.7rem;
            }
            #methodSelectionModal .modal-title {
                font-size: 1rem;
            }
        }

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

        .hover-text-white:hover {
            color: white !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(isset($showMethodSelection) && $showMethodSelection)
                const modal = new bootstrap.Modal(document.getElementById('methodSelectionModal'));
                modal.show();
            @endif

            window.selectMethod = function(mode, btnElement) {
                const form = document.getElementById('method-form');
                const input = form.querySelector('input[name="verification_mode"]');
                input.value = mode;

                // Add loading state
                btnElement.innerHTML = `<span class="spinner-border spinner-border-sm text-accent me-2" role="status" aria-hidden="true"></span> Sending...`;
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
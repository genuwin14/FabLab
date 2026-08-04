@extends('layout.app')

@section('content')
    {{-- Shown after a Google sign-up, which brings an email but no phone number.
         Skippable on purpose: nobody should be locked out of the shop over it. --}}
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 pt-auth-mobile"
        style="background-color: #05111a;">

        <a href="{{ route('landing') }}" class="position-absolute top-0 start-0 m-3 m-md-4 z-3 auth-logo-link">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" class="auth-logo">
        </a>

        <div class="container position-relative z-1 px-3 px-sm-4">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="auth-card p-3 p-sm-4 p-md-5 rounded-4 shadow-lg border border-white border-opacity-10 backdrop-blur text-center"
                        style="background-color: rgba(255, 255, 255, 0.05);">

                        <div class="mb-4">
                            <i class="bi bi-telephone-fill fs-1" style="color: #ffc508;"></i>
                            <h4 class="fw-bold text-white mt-3 mb-2">One more thing</h4>
                            <p class="text-white-50 small mb-0">
                                Welcome, {{ auth()->user()->fullname }}. Adding a contact number lets the shop
                                reach you about your orders — it's the only detail Google didn't give us.
                            </p>
                        </div>

                        <form action="{{ route('profile.complete.store') }}" method="POST">
                            @csrf

                            <div class="mb-3 text-start">
                                <label for="contact_number" class="form-label small fw-bold text-white-50 ms-1">
                                    Contact Number
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-white border-opacity-10 text-white-50 @error('contact_number') border-danger text-danger @enderror"
                                        style="background-color: rgba(0,0,0,0.3);"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="contact_number" id="contact_number"
                                        class="form-control border-start-0 border-white border-opacity-10 text-white @error('contact_number') border-danger is-invalid @enderror"
                                        placeholder="09XX XXX XXXX" value="{{ old('contact_number') }}"
                                        style="background-color: rgba(0,0,0,0.3); color: white;" required autofocus>
                                </div>
                                @error('contact_number')
                                    <div class="text-danger small mt-1 text-start">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn w-100 fw-bold rounded-pill py-2 mb-3"
                                style="background-color: #ffc508; color: #0e2e45;">
                                Save and continue
                            </button>
                        </form>

                        <a href="{{ route(auth()->user()->homeRoute()) }}" class="text-white-50 small text-decoration-none">
                            Skip for now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

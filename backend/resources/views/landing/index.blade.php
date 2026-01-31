@extends('layout.app')

@section('content')
    <div class="landing-page-wrapper text-white" style="background-color: #05111a;">
        <!-- Navbar -->
        @include('landing.partials.navbar')

        <!-- Hero Section -->
        @include('landing.sections.hero')

        <!-- Product Section -->
        @include('landing.sections.product')

        <!-- About Section -->
        @include('landing.sections.about')

        <!-- Contact Section -->
        @include('landing.sections.contact')

        <!-- Footer -->
        @include('landing.partials.footer')
    </div>

    <!-- Page Specific Styles -->
    @include('landing.partials.styles')
@endsection
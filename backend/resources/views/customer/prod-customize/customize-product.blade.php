@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #05111a;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white-10"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0 text-white" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #05111a;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- 2-Panel Customizer Layout -->
            <main class="flex-grow-1 d-flex flex-column flex-md-row overflow-hidden">

                <!-- LEFT PANEL: Components, Features, Textures -->
                @include('customer.prod-customize.components.control-panel')

                    <!-- RIGHT PANEL: 3D Visualization -->
                    @include('customer.prod-customize.components.viewer-panel')

                </main>
            </div>
        </div>

        <!-- Customizer Styles -->
        @include('customer.prod-customize.components.styles')

        <!-- Customizer Scripts -->
        @include('customer.prod-customize.components.scripts')
@endsection
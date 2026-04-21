@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #05111a;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 flex-shrink-0"
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
            <header class="sticky-top border-bottom border-white border-opacity-10"
                style="z-index: 1030; background-color: #05111a;">
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

    <!-- Product Guidance Modal -->
    <div class="modal fade" id="productRequiredModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="productRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-4">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <h4 class="fw-bold text-white mb-2">Base Product Required</h4>
                        <p class="text-white-50">To add your design to the cart, you must first select a base product from our shop. This ensures we have the correct pricing and material for your order.</p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.shop') }}" class="btn btn-accent rounded-pill py-2 fw-bold">
                            <i class="bi bi-shop me-2"></i> Browse Shop & Selection
                        </a>
                        <button type="button" class="btn btn-link text-white-50 text-decoration-none small" data-bs-dismiss="modal">
                            Just let me preview the customizer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customizer Styles -->
    @include('customer.prod-customize.components.styles')

    <!-- Customizer Scripts -->
    @include('customer.prod-customize.components.scripts')
@endsection
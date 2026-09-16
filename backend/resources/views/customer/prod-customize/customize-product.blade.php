@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #05111a; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100" style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class='d-none d-md-block sidebar-spacer flex-shrink-0' style='width: 280px;'></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0 text-white" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #05111a;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 border-bottom border-white border-opacity-10 shadow-sm"
                style="z-index: 1042; background-color: #05111a;">
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
    <div class="modal fade qv-modal" id="productRequiredModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="productRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content">
                <div class="modal-body p-4 p-lg-5 text-center">
                    <span class="qv-icon qv-icon-primary mb-4">
                        <i class="bi bi-bag-check"></i>
                    </span>

                    <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">Customizer</span>
                    <h4 id="productRequiredModalLabel" class="qv-title mb-2">Base Product Required</h4>
                    <p class="text-muted small mb-4">
                        To add your design to the cart, you must first select a base product from our shop. This
                        ensures we have the correct pricing and material for your order.
                    </p>

                    <div class="qv-actions">
                        <a href="{{ route('customer.shop') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-shop me-2"></i> Browse Shop &amp; Selection
                        </a>
                        <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">
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
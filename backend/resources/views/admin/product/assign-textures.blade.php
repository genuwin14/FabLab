@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="mb-4">
                        <h4 class="fw-bold text-primary mb-1">Assign Textures</h4>
                        <p class="text-muted small mb-0">Choose which textures customers can apply to: <strong>{{ $product->name }}</strong></p>
                    </div>

                    <!-- Product Info Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                        style="width: 80px; height: 80px; background-size: cover; background-position: center; {{ $product->image ? "background-image: url('{$product->image}');" : '' }}">
                                        @if(!$product->image)
                                            <i class="bi bi-image text-muted opacity-50 fs-2"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                                    <div class="d-flex gap-3 text-muted small">
                                        <span><i class="bi bi-upc-scan me-1"></i> {{ $product->sku }}</span>
                                        <span><i class="bi bi-tag me-1"></i> {{ $product->category->name ?? 'N/A' }}</span>
                                        <span><i class="bi bi-currency-peso me-1"></i> ₱{{ number_format($product->price, 2) }}</span>
                                        @if(!$product->is_customizable)
                                            <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Not marked as customizable</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Texture Assignment Form -->
                    <form action="{{ route('admin.products.textures.store', $product->product_id) }}" method="POST">
                        @csrf

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-layers text-primary me-2"></i>
                                        Available Textures
                                    </h6>
                                    <p class="text-muted small mb-0 mt-1">Click a texture card to toggle its assignment to this product.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-check2-all me-1"></i> Select All
                                    </button>
                                    <button type="button" id="clearAllBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="bi bi-x-lg me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                @if($textures->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No textures available. Please <a href="{{ route('admin.textures.index') }}">add textures</a> first.
                                    </div>
                                @else
                                    <div class="row g-3">
                                        @php
                                            $assignedIds = $product->textures->pluck('texture_id')->all();
                                        @endphp
                                        @foreach($textures as $texture)
                                            @php $isChecked = in_array($texture->texture_id, $assignedIds); @endphp
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <label class="texture-card-label d-block h-100" style="cursor: pointer;">
                                                    <input type="checkbox"
                                                        name="textures[]"
                                                        value="{{ $texture->texture_id }}"
                                                        class="texture-checkbox d-none"
                                                        {{ $isChecked ? 'checked' : '' }}>
                                                    <div class="texture-card card border-2 rounded-4 h-100 overflow-hidden {{ $isChecked ? 'border-primary shadow-sm' : 'border-transparent' }}">
                                                        <div class="position-relative" style="height: 130px;">
                                                            @if($texture->image_path)
                                                                <img src="{{ $texture->image_path }}" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="{{ $texture->name }}">
                                                            @else
                                                                <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-light text-muted">
                                                                    <i class="bi bi-image fs-1"></i>
                                                                </div>
                                                            @endif
                                                            <div class="texture-check-badge position-absolute top-0 end-0 m-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                                style="width: 28px; height: 28px; {{ $isChecked ? '' : 'display: none !important;' }}">
                                                                <i class="bi bi-check-lg"></i>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="fw-bold text-dark small mb-0 text-truncate">{{ $texture->name }}</div>
                                                            @if($texture->price_modifier > 0)
                                                                <small class="text-warning">+₱{{ number_format($texture->price_modifier, 2) }}</small>
                                                            @else
                                                                <small class="text-muted">No price change</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="alert alert-info mt-4 mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Tip:</strong> Only textures assigned here will appear in the customizer for this product.
                                        @if(!$product->is_customizable)
                                            This product is currently <strong>not marked as customizable</strong> — assignments will be saved but the customizer won't be reachable until you toggle that flag.
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="bi bi-arrow-left me-2"></i>Back to Products
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check-lg me-2"></i>Save Texture Assignments
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>

    <style>
        .texture-card {
            transition: all 0.15s ease;
        }
        .texture-card-label:hover .texture-card {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        }
        .texture-card.border-primary {
            border-color: #0d6efd !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.texture-checkbox');

            function syncCard(checkbox) {
                const card = checkbox.closest('label').querySelector('.texture-card');
                const badge = checkbox.closest('label').querySelector('.texture-check-badge');
                if (checkbox.checked) {
                    card.classList.add('border-primary', 'shadow-sm');
                    card.classList.remove('border-transparent');
                    badge.style.display = 'flex';
                } else {
                    card.classList.remove('border-primary', 'shadow-sm');
                    card.classList.add('border-transparent');
                    badge.style.display = 'none';
                }
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => syncCard(cb));
            });

            const selectAllBtn = document.getElementById('selectAllBtn');
            const clearAllBtn = document.getElementById('clearAllBtn');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(cb => { cb.checked = true; syncCard(cb); });
                });
            }
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(cb => { cb.checked = false; syncCard(cb); });
                });
            }
        });
    </script>
@endsection

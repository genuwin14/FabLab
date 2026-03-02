@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 flex-shrink-0"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background-color: #f1f4f8;">
            <!-- Top Navbar -->
            <header class="sticky-top border-bottom border-white border-opacity-10"
                style="z-index: 1030; background-color: #05111a;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 overflow-y-auto custom-scrollbar">
                <div class="container-fluid">
                    <div class="row g-4">
                        @forelse($designs as $design)
                            <div class="col-sm-6 col-md-4 col-xl-3">
                                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden design-card">
                                    <div class="position-relative">
                                        <div class="ratio ratio-1x1 bg-dark">
                                            <img src="{{ $design->snapshot }}" class="object-fit-cover w-100 h-100"
                                                alt="Design Snapshot">
                                        </div>
                                        <div class="position-absolute top-0 start-0 m-3" style="z-index: 5;">
                                            <button
                                                class="btn btn-dark-glass text-white rounded-circle backdrop-blur tiny p-2 btn-preview-design"
                                                data-id="{{ $design->custom_design_id }}"
                                                data-product-id="{{ $design->product_id }}"
                                                data-recipe="{{ json_encode($design->recipe) }}"
                                                data-snapshot="{{ $design->snapshot }}"
                                                data-shape="{{ $design->recipe['base_style'] ?? 't-shirt' }}"
                                                title="View 3D Model">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-dark-glass text-white rounded-pill backdrop-blur tiny">
                                                {{ $design->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <!-- Bottom Info Overlay -->
                                        <div class="position-absolute bottom-0 start-0 w-100 p-3"
                                            style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%); z-index: 5;">
                                            <h6 class="fw-bold text-white mb-1 text-truncate"
                                                style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                                {{ $design->product->name ?? 'Custom Design' }}
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-white-50 tiny mb-0">
                                                    {{ count($design->recipe['elements']['text'] ?? []) }} Text |
                                                    {{ count($design->recipe['elements']['logos'] ?? []) }} Logos
                                                </p>
                                                <span
                                                    class="fw-bold text-accent">₱{{ number_format($design->calculated_price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body p-3">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('customer.customize.index', ['design_id' => $design->custom_design_id]) }}"
                                                class="btn btn-soft-primary flex-grow-1 rounded-3 tiny fw-bold py-2">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                            <button class="btn btn-soft-accent rounded-3 tiny fw-bold btn-order-again py-2"
                                                data-product-id="{{ $design->product_id }}"
                                                data-recipe="{{ json_encode($design->recipe) }}"
                                                data-snapshot="{{ $design->snapshot }}" title="Add to Cart"
                                                style="width: 42px;">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                            <button class="btn btn-soft-danger rounded-3 tiny fw-bold py-2 btn-delete-design"
                                                data-id="{{ $design->custom_design_id }}" title="Delete Design"
                                                style="width: 42px;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-palette text-muted display-1 opacity-25"></i>
                                </div>
                                <h5 class="text-muted">You haven't saved any designs yet.</h5>
                                <p class="text-muted small">Start creating your first custom product now!</p>
                                <a href="{{ route('customer.shop') }}" class="btn btn-primary rounded-pill px-4 mt-2">Go to
                                    Shop</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('customer.prod-customize.modals.preview-3d')
    @include('customer.prod-customize.modals.delete-confirm')

    <style>
        .design-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .design-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .bg-dark-glass {
            background: rgba(0, 0, 0, 0.5);
        }

        .backdrop-blur {
            backdrop-filter: blur(4px);
        }

        .btn-soft-primary {
            background-color: #e7f1ff;
            color: #0d6efd;
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: #0d6efd;
            color: white;
        }

        .btn-soft-danger {
            background-color: #fff1f2;
            color: #ef4444;
            border: none;
            transition: all 0.2s;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
        }

        .btn-soft-accent {
            background-color: #fff9e6;
            color: #ffc508;
            border: none;
            transition: all 0.2s;
        }

        .btn-soft-accent:hover {
            background-color: #ffc508;
            color: #0e2e45;
        }

        .btn-soft-secondary {
            background-color: #f1f4f8;
            color: #6c757d;
            border: none;
        }

        .btn-dark-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-dark-glass:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            /* Lighter glass on hover */
            color: #f1f4f8 !important;
            /* Soft red icon on hover */
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.1);
        }
    </style>

    @push('scripts')
        <!-- Three.js Libraries -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

        <!-- Modular Customizer Scripts -->
        <script src="{{ asset('js/customizer/state.js') }}"></script>
        <script src="{{ asset('js/customizer/core.js') }}"></script>
        <script src="{{ asset('js/customizer/models/mug.js') }}"></script>
        <script src="{{ asset('js/customizer/models/t-shirt.js') }}"></script>
        <script src="{{ asset('js/customizer/models/shorts.js') }}"></script>
        <script src="{{ asset('js/customizer/models/umbrella.js') }}"></script>
        <script src="{{ asset('js/customizer/rendering.js') }}"></script>
        <script src="{{ asset('js/customizer/logic.js') }}"></script>
        <script src="{{ asset('js/customizer/persistence.js') }}"></script>

        <script>
            $(document).ready(function () {
                let currentDesignData = null;
                let currentInitialShape = 't-shirt';
                let currentDesignId = null;
                let currentProductId = null;
                let currentSnapshot = null;
                let isEngineInitializing = false;

                $('.btn-preview-design').on('click', function () {
                    if (isEngineInitializing) return;

                    const btn = $(this);
                    currentDesignId = btn.data('id');
                    currentDesignData = btn.data('recipe');
                    currentInitialShape = btn.data('shape');
                    currentProductId = btn.data('product-id');
                    currentSnapshot = btn.data('snapshot');

                    $('#previewDesignModal').modal('show');
                    $('#preview-three-container').empty();
                });

                // Initialize 3D ONLY when modal is fully visible
                $('#previewDesignModal').off('shown.bs.modal').on('shown.bs.modal', function () {
                    // Safety check to prevent double init
                    if (isEngineInitializing || $('#preview-three-container canvas').length > 0) return;

                    isEngineInitializing = true;
                    window.CustomizerConfig = window.CustomizerConfig || {};
                    window.CustomizerConfig.initialShape = currentInitialShape;
                    window.CustomizerConfig.productId = currentDesignId;

                    init('preview-three-container');

                    if (currentDesignData) {
                        loadDesignRecipePreview(currentDesignData);
                    }

                    $('#preview-btn-edit').attr('href', `/customer/customize?design_id=${currentDesignId}`);
                });

                // Clear focus to prevent ARIA warnings
                $('#previewDesignModal').on('hide.bs.modal', function () {
                    if (document.activeElement && document.activeElement instanceof HTMLElement) {
                        document.activeElement.blur();
                    }
                });

                // Clear scene when modal is hidden
                $('#previewDesignModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                    isEngineInitializing = false;
                    if (typeof renderer !== 'undefined' && renderer) {
                        renderer.dispose();
                        if (renderer.domElement && renderer.domElement.parentNode) {
                            renderer.domElement.parentNode.removeChild(renderer.domElement);
                        }
                        renderer = null;
                    }
                    $('#preview-three-container').empty();
                });
                /**
                 * Common Add to Cart Logic
                 */
                function addDesignToCart(productId, recipe, snapshot, btn) {
                    const originalContent = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm"></span>');
                    btn.prop('disabled', true);

                    if (!productId) {
                        showToast('Product information missing', 'error');
                        btn.html(originalContent);
                        btn.prop('disabled', false);
                        return;
                    }

                    $.ajax({
                        url: "{{ route('customer.cart.add') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_id: productId,
                            quantity: 1,
                            custom_recipe: typeof recipe === 'string' ? recipe : JSON.stringify(recipe),
                            custom_snapshot: snapshot
                        },
                        success: function (response) {
                            if (response.success) {
                                showToast('Design added to cart!', 'success');
                                $('.cart-badge').text(response.cart_count);
                            }
                        },
                        error: function (xhr) {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding to cart';
                            showToast(msg, 'error');
                        },
                        complete: function () {
                            btn.html(originalContent);
                            btn.prop('disabled', false);
                        }
                    });
                }

                // Grid Cart Click
                $('.btn-order-again').on('click', function () {
                    const btn = $(this);
                    addDesignToCart(btn.data('product-id'), btn.data('recipe'), btn.data('snapshot'), btn);
                });

                // Preview Modal Cart Click
                $('#preview-btn-add-to-cart').on('click', function () {
                    addDesignToCart(currentProductId, currentDesignData, currentSnapshot, $(this));
                });

                let designToDelete = null;
                let cardToDelete = null;

                $('.btn-delete-design').on('click', function () {
                    const btn = $(this);
                    designToDelete = btn.data('id');
                    cardToDelete = btn.closest('.col-sm-6');

                    $('#deleteConfirmModal').modal('show');
                });

                $('#confirm-delete-btn').on('click', function () {
                    const btn = $(this);
                    const originalContent = btn.html();

                    btn.prop('disabled', true);
                    btn.html('<span class="spinner-border spinner-border-sm"></span> Deleting...');

                    $.ajax({
                        url: `/customer/customize/${designToDelete}`,
                        method: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            $('#deleteConfirmModal').modal('hide');
                            if (response.success) {
                                showToast(response.message, 'success');
                                cardToDelete.fadeOut(300, function () {
                                    $(this).remove();
                                    if ($('.design-card').length === 0) {
                                        location.reload();
                                    }
                                });
                            }
                        },
                        error: function () {
                            $('#deleteConfirmModal').modal('hide');
                            showToast('Error deleting design', 'error');
                        },
                        complete: function () {
                            btn.prop('disabled', false);
                            btn.html(originalContent);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
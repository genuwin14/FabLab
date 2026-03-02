@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm flex-shrink-0" style="width: 280px; z-index: 1040;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background-color: #f1f4f8;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 overflow-y-auto custom-scrollbar">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">My Personal Designs</h4>
                            <p class="text-muted small mb-0">Manage and view your custom creations.</p>
                        </div>
                        <a href="{{ route('customer.customize.index') }}"
                            class="btn btn-primary rounded-pill px-4 fw-bold small">
                            <i class="bi bi-plus-lg me-1"></i> Create New
                        </a>
                    </div>

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
                                                data-recipe="{{ json_encode($design->recipe) }}"
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
                                    </div>
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate">
                                            {{ $design->product->name ?? 'Custom Design' }}
                                        </h6>
                                        <p class="text-muted tiny mb-3">
                                            {{ count($design->recipe['elements']['text'] ?? []) }} Text |
                                            {{ count($design->recipe['elements']['logos'] ?? []) }} Logos
                                        </p>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('customer.customize.index', ['design_id' => $design->custom_design_id]) }}"
                                                class="btn btn-soft-primary flex-grow-1 rounded-pill tiny fw-bold">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                            @if($design->product_id)
                                                <button class="btn btn-accent rounded-pill tiny fw-bold btn-order-again"
                                                    data-id="{{ $design->product_id }}"
                                                    data-recipe="{{ json_encode($design->recipe) }}"
                                                    data-snapshot="{{ $design->snapshot }}">
                                                    Order
                                                </button>
                                            @endif
                                            <button class="btn btn-soft-danger rounded-circle tiny p-2 btn-delete-design"
                                                data-id="{{ $design->custom_design_id }}" title="Delete Design">
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
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
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
            background: rgba(239, 68, 68, 0.8) !important;
            /* Soft red hover for exit */
            color: white !important;
            border-color: transparent;
            transform: rotate(90deg);
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

                $('.btn-preview-design').on('click', function () {
                    const btn = $(this);
                    currentDesignId = btn.data('id');
                    currentDesignData = btn.data('recipe');
                    currentInitialShape = btn.data('shape');

                    $('#previewDesignModal').modal('show');

                    $('#preview-three-container').empty();
                    $('#preview-loader').show();
                });

                // Initialize 3D ONLY when modal is fully visible
                $('#previewDesignModal').off('shown.bs.modal').on('shown.bs.modal', function () {
                    // Safety check to prevent double init
                    if ($('#preview-three-container canvas').length > 0) return;

                    window.CustomizerConfig = window.CustomizerConfig || {};
                    window.CustomizerConfig.initialShape = currentInitialShape;
                    window.CustomizerConfig.productId = currentDesignId;

                    init('preview-three-container');

                    if (currentDesignData) {
                        loadDesignRecipePreview(currentDesignData);
                    }

                    $('#preview-btn-edit').attr('href', `/customer/customize?design_id=${currentDesignId}`);
                });

                // Clear scene when modal is hidden
                $('#previewDesignModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                    if (typeof renderer !== 'undefined' && renderer) {
                        renderer.dispose();
                        if (renderer.domElement && renderer.domElement.parentNode) {
                            renderer.domElement.parentNode.removeChild(renderer.domElement);
                        }
                        renderer = null;
                    }
                    $('#preview-three-container').empty();
                    $('#preview-loader').show();
                });
                $('.btn-order-again').on('click', function () {
                    const btn = $(this);
                    const originalContent = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm"></span>');
                    btn.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('customer.cart.add') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_id: btn.data('id'),
                            quantity: 1,
                            custom_recipe: JSON.stringify(btn.data('recipe')),
                            custom_snapshot: btn.data('snapshot')
                        },
                        success: function (response) {
                            if (response.success) {
                                showToast('Design added to cart!', 'success');
                                $('.cart-badge').text(response.cart_count);
                            }
                        },
                        error: function () {
                            showToast('Error adding to cart', 'error');
                        },
                        complete: function () {
                            btn.html(originalContent);
                            btn.prop('disabled', false);
                        }
                    });
                });

                $('.btn-delete-design').on('click', function () {
                    const btn = $(this);
                    const designId = btn.data('id');
                    const card = btn.closest('.col-sm-6');

                    if (confirm('Are you sure you want to delete this design? This action cannot be undone.')) {
                        btn.prop('disabled', true);
                        btn.html('<span class="spinner-border spinner-border-sm"></span>');

                        $.ajax({
                            url: `/customer/customize/${designId}`,
                            method: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    showToast(response.message, 'success');
                                    card.fadeOut(300, function () {
                                        $(this).remove();
                                        if ($('.design-card').length === 0) {
                                            location.reload(); // Reload to show empty state
                                        }
                                    });
                                }
                            },
                            error: function () {
                                showToast('Error deleting design', 'error');
                                btn.prop('disabled', false);
                                btn.html('<i class="bi bi-trash"></i>');
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
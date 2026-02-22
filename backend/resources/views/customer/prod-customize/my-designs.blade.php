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
    </style>

    @push('scripts')
        <script>
            $(document).ready(function () {
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
            });
        </script>
    @endpush
@endsection
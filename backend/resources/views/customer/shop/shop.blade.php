@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm flex-shrink-0" style="width: 280px; z-index: 1040;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background-color: #f1f4f8;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 overflow-y-auto custom-scrollbar">
                <div class="container-fluid">
                    
                    <!-- Search & Categories Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Product Catalog</h4>
                            <p class="text-muted small mb-0">Explore our available items and materials.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('customer.shop') }}" method="GET" class="input-group" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" 
                                       placeholder="Search products..." value="{{ request('search') }}">
                            </form>
                        </div>
                    </div>

                    <!-- Category Quick Filters -->
                    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-4 no-scrollbar">
                        <a href="{{ route('customer.shop') }}" 
                           class="btn {{ !request('category') || request('category') == 'all' ? 'btn-primary' : 'btn-white border' }} rounded-pill px-4 small fw-bold text-decoration-none">
                            All Items
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('customer.shop', ['category' => $category->name, 'search' => request('search')]) }}" 
                               class="btn {{ request('category') == $category->name ? 'btn-primary' : 'btn-white border' }} rounded-pill px-4 small fw-bold text-decoration-none">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Products Grid (4 Columns) -->
                    <div class="row g-4">
                        @forelse($products as $product)
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm rounded-4 product-card overflow-hidden">
                                <!-- Image Preview -->
                                <div class="position-relative">
                                    <div class="ratio ratio-1x1 bg-light border-bottom">
                                        <div class="d-flex align-items-center justify-content-center overflow-hidden rounded-top-4"
                                             style="background-image: url('{{ $product->image ?: asset('img/FABLAB-LOGO.png') }}'); background-size: cover; background-position: center;">
                                            @if(!$product->image)
                                                <i class="bi bi-image text-muted opacity-50 display-6"></i>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Badge for Customizable -->
                                    @if($product->is_customizable)
                                    <div class="position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-accent text-primary rounded-pill shadow-sm small fw-bold">
                                            <i class="bi bi-palette-fill me-1"></i> Customizable
                                        </span>
                                    </div>
                                    @endif

                                    <!-- Status Badge -->
                                    <div class="position-absolute top-0 start-0 m-3">
                                        @if($product->stock <= 0)
                                            <span class="badge bg-danger rounded-pill shadow-sm small fw-bold">OUT OF STOCK</span>
                                        @elseif($product->stock <= $product->low_stock_threshold)
                                            <span class="badge bg-warning text-dark rounded-pill shadow-sm small fw-bold">LOW STOCK</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="mb-2">
                                        <div class="small text-muted mb-1">{{ $product->category->name ?? 'Uncategorized' }}</div>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                                    </div>
                                    
                                    <div class="mt-auto pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="small text-muted mb-0">Price per {{ $product->unit }}</div>
                                                <h5 class="fw-bold text-primary mb-0">₱{{ number_format($product->price, 2) }}</h5>
                                            </div>
                                            <div class="small fw-bold {{ $product->stock <= $product->low_stock_threshold ? 'text-danger' : 'text-success' }}">
                                                {{ $product->stock }} {{ $product->unit }} left
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer/Action Overlay on Hover -->
                                <div class="product-overlay">
                                    <div class="d-flex flex-column gap-2 p-3 w-100">
                                        <button class="btn btn-primary fw-bold rounded-pill w-100 small shadow-sm py-2 btn-add-to-cart" 
                                                data-id="{{ $product->product_id }}"
                                                data-name="{{ $product->name }}"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-white text-dark fw-bold rounded-pill w-100 small shadow-sm py-2 btn-quick-view"
                                                data-id="{{ $product->product_id }}"
                                                data-name="{{ $product->name }}"
                                                data-description="{{ $product->description }}"
                                                data-price="₱{{ number_format($product->price, 2) }}"
                                                data-image="{{ $product->image ?: asset('img/FABLAB-LOGO.png') }}"
                                                data-category="{{ $product->category->name ?? 'Uncategorized' }}"
                                                data-stock="{{ $product->stock }}"
                                                data-unit="{{ $product->unit }}"
                                                data-sku="{{ $product->sku }}"
                                                data-brand="{{ $product->brand }}">
                                            <i class="bi bi-eye me-1"></i> Quick View
                                        </button>
                                        @if($product->is_customizable)
                                        <a href="{{ route('customer.customize.index', ['product_id' => $product->product_id]) }}" class="btn btn-accent text-primary fw-bold rounded-pill w-100 small shadow-sm py-2 text-decoration-none text-center">
                                            <i class="bi bi-magic me-1"></i> Customize Now
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-bag-x text-muted display-1 opacity-25"></i>
                            </div>
                            <h5 class="text-muted">No products available at the moment.</h5>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-5 mb-4 pagination-container">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('customer.shop.components.quick-view-modal')

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"
        style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-circle text-warning display-4"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark">Sign Out?</h5>
                    <p class="text-muted small mb-4">Are you sure you want to log out of your FABLAB account?</p>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4 rounded-pill fw-bold small"
                            data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold small">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Shop Enhancements */
        .product-card {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }

        .product-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(14, 46, 69, 0.95), transparent);
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 100%;
            border-radius: 12px;
            pointer-events: none;
            z-index: 2;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
            pointer-events: all;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .btn-white {
            background: white;
            border-color: #dee2e6;
        }

        .btn-white:hover {
            background: #f8f9fa;
            border-color: #ffc508;
        }

        .pagination-rounded .page-link {
            color: #0e2e45;
            font-weight: 600;
        }

        .pagination-container .pagination {
            gap: 5px;
        }

        .pagination-container .page-link {
            border: none;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 8px 16px;
            color: #0e2e45;
            font-weight: 600;
        }

        .pagination-container .page-item.active .page-link {
            background-color: #0e2e45;
            color: white;
        }
    </style>
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-add-to-cart').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const productId = btn.data('id');
                const productName = btn.data('name');
                
                // Visual feedback
                const originalContent = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('customer.cart.add') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        quantity: 1
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            $('.cart-badge').text(response.cart_count);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding to cart';
                        showToast(message, 'error');
                    },
                    complete: function() {
                        btn.html(originalContent);
                        btn.prop('disabled', false);
                    }
                });
            });

            // Quick View Logic
            $('.btn-quick-view').on('click', function() {
                const btn = $(this);
                const data = btn.data();
                
                $('#qv-name').text(data.name);
                $('#qv-description').text(data.description || 'No description available.');
                $('#qv-price').text(data.price);
                $('#qv-image').attr('src', data.image);
                $('#qv-category').text(data.category);
                $('#qv-sku').text('SKU: ' + data.sku);
                $('#qv-brand').text(data.brand || 'No Brand');
                
                const stock = parseInt(data.stock);
                const unit = data.unit;
                const stockStatus = $('#qv-stock-status');
                const addBtn = $('#qv-add-to-cart-btn');
                
                if (stock <= 0) {
                    stockStatus.text('Out of Stock').removeClass('text-success text-warning').addClass('text-danger');
                    addBtn.prop('disabled', true).text('Out of Stock');
                } else {
                    stockStatus.text(stock + ' ' + unit + ' Available').removeClass('text-danger').addClass('text-success');
                    addBtn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i> Add to Cart');
                    // Set product id for the add to cart button in modal
                    addBtn.data('id', data.id);
                }

                const qvModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                qvModal.show();
            });

            // Handle Add to Cart from Quick View Modal
            $('#qv-add-to-cart-btn').on('click', function() {
                const productId = $(this).data('id');
                if (!productId) return;
                
                // Find the original button in the grid to trigger its logic
                const gridBtn = $(`.btn-add-to-cart[data-id="${productId}"]`);
                if (gridBtn.length) {
                    bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();
                    gridBtn.trigger('click');
                }
            });
        });
    </script>
    @endpush
@endsection
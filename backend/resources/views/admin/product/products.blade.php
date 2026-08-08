@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
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
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="productFilterForm" method="GET" action="{{ route('admin.products.index') }}"
                                class="row g-2 align-items-center">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <!-- Search: plain inline input on desktop; on mobile it
                                     collapses behind the search icon and opens as a dropdown. -->
                                <div class="col-auto col-lg dropdown search-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none search-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Search">
                                        <i class="bi bi-search text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu search-menu border-0 shadow p-2 p-lg-0">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text" name="search" value="{{ $search }}"
                                                class="form-control border-start-0 rounded-end-2 ps-0"
                                                placeholder="Search by name, SKU, or brand...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Category + Stock filters: inline selects on desktop;
                                     on mobile they collapse behind one filter icon. -->
                                <div class="col-auto dropdown filter-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none filter-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Filters">
                                        <i class="bi bi-funnel text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
                                        <div class="d-flex flex-column flex-lg-row gap-2">
                                            <select name="category_id" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('productFilterForm').submit()">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->category_id }}"
                                                        {{ (string) $categoryId === (string) $category->category_id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <select name="stock_status" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('productFilterForm').submit()">
                                                <option value="">Stock Status</option>
                                                <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                                <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                                <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('admin.products.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        title="Export">
                                        <i class="bi bi-download small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Export</span>
                                    </button>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#addProductModal" title="Add Product">
                                        <i class="bi bi-plus-lg small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Add Product</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden products-table-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Product Info</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">SKU</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Category</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Price (unit)</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Stock</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                            <th class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($products as $product)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                                            style="width: 48px; height: 48px; background-size: cover; background-position: center; {{ $product->image_url ? "background-image: url('{$product->image_url}');" : '' }}">
                                                            @if(!$product->image_url)
                                                                <i class="bi bi-image text-muted opacity-50"></i>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">{{ $product->name }}</h6>
                                                            <small
                                                                class="text-muted">{{ Str::limit($product->description, 30) }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="font-monospace small text-muted">{{ $product->sku }}</span>
                                                </td>
                                                <td>
                                                    @if($product->category)
                                                        <span
                                                            class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 rounded-pill fw-normal">
                                                            {{ $product->category->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">Uncategorized</span>
                                                    @endif
                                                </td>
                                                <td class="fw-bold text-primary">₱{{ number_format($product->price, 2) }}</td>
                                                <td>
                                                    <div class="d-flex flex-column" style="width: 100px;">
                                                        <span class="fw-bold text-dark small">{{ $product->stock }}
                                                            {{ $product->unit }}</span>
                                                        @php
                                                            $percentage = $product->stock > 0 ? min(100, ($product->stock / 100) * 100) : 0; // Simplified progress
                                                            $color = $product->stock <= $product->low_stock_threshold ? 'bg-danger' : 'bg-success';
                                                        @endphp
                                                        <div class="progress" style="height: 4px;">
                                                            <div class="progress-bar {{ $color }}" role="progressbar"
                                                                style="width: {{ $percentage }}%"
                                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($product->stock <= 0)
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill d-flex align-items-center gap-1 w-auto d-inline-flex">
                                                            <i class="bi bi-x-circle-fill" style="font-size: 0.75rem;"></i> Out of
                                                            Stock
                                                        </span>
                                                    @elseif($product->stock <= $product->low_stock_threshold)
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill d-flex align-items-center gap-1 w-auto d-inline-flex">
                                                            <i class="bi bi-exclamation-circle-fill"
                                                                style="font-size: 0.75rem;"></i> Low Stock
                                                        </span>
                                                    @else
                                                        @php
                                                            $statusClass = 'bg-success text-success';
                                                            $statusIcon = 'bi-check-circle-fill';

                                                            if (in_array($product->status, ['broken', 'defective'])) {
                                                                $statusClass = 'bg-danger text-danger';
                                                                $statusIcon = 'bi-x-circle';
                                                            } elseif ($product->status === 'maintenance') {
                                                                $statusClass = 'bg-warning text-warning';
                                                                $statusIcon = 'bi-tools';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="badge {{ $statusClass }} bg-opacity-10 px-2 py-1 rounded-pill d-flex align-items-center gap-1 w-auto d-inline-flex">
                                                            <i class="bi {{ $statusIcon }}" style="font-size: 0.75rem;"></i>
                                                            {{ ucfirst($product->status ?? 'active') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ route('admin.products.suppliers.assign', $product->product_id) }}"
                                                            class="btn btn-light btn-sm rounded-circle"
                                                            title="Manage Suppliers">
                                                            <i class="bi bi-truck text-primary"></i>
                                                        </a>
                                                        <a href="{{ route('admin.products.textures.assign', $product->product_id) }}"
                                                            class="btn btn-light btn-sm rounded-circle"
                                                            title="Manage Textures">
                                                            <i class="bi bi-layers text-info"></i>
                                                        </a>
                                                        <button class="btn btn-light btn-sm rounded-circle"
                                                            data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                            data-product="{{ json_encode($product) }}" title="Edit Product">
                                                            <i class="bi bi-pencil text-warning"></i>
                                                        </button>
                                                        <button class="btn btn-light btn-sm rounded-circle"
                                                            data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                                                            data-id="{{ $product->product_id }}"
                                                            data-name="{{ $product->name }}" title="Delete Product">
                                                            <i class="bi bi-trash text-danger"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No products found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <!-- Pagination sits INSIDE .table-responsive, so it
                                     rides the table's single horizontal scrollbar. -->
                                <div class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                        <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                            onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                            @foreach([10, 25, 50, 100] as $size)
                                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-muted small text-nowrap">
                                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} entries
                                        </span>
                                    </div>
                                    <nav class="flex-shrink-0">
                                        {{ $products->links() }}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Add Product Modal -->
    @include('admin.product.components.modal-add-product')
    <!-- Edit Product Modal -->
    @include('admin.product.components.modal-edit-product')
    <!-- Delete Product Modal -->
    @include('admin.product.components.modal-delete-product')

    <style>
        /* ============================================
           Shared Product Modal Theme (Add / Edit)
           ============================================ */
        .product-modal .modal-content { border-radius: 18px; }

        .product-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .product-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .product-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .product-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .product-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .product-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        /* Side panel */
        .product-side-panel {
            background-color: #f8f9fa;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .product-photo-edit {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #0e2e45;
            color: #ffc508;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .product-photo-edit:hover {
            background-color: #ffc508;
            color: #0e2e45;
        }

        /* Section titles */
        .product-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Form inputs */
        .product-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .product-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .product-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .product-modal .input-group > .product-field-input {
            border-radius: 0 10px 10px 0 !important;
        }
        .product-modal .input-group > .product-field-input:first-child {
            border-radius: 10px 0 0 10px !important;
        }
        .product-sku-btn {
            background-color: #0e2e45;
            color: #ffc508;
            border: 1px solid #0e2e45;
            border-radius: 0 10px 10px 0;
            transition: all 0.2s ease;
        }
        .product-sku-btn:hover {
            background-color: #ffc508;
            color: #0e2e45;
            border-color: #ffc508;
        }

        /* Customizable / setting card */
        .product-secured-card {
            padding: 12px 14px;
            border-radius: 12px;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .product-modal .form-check-input:checked {
            background-color: #0e2e45;
            border-color: #0e2e45;
        }

        /* Tabs (edit modal) */
        .product-tabs {
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            gap: 4px;
        }
        .product-tabs .nav-link {
            color: #6c757d;
            border: 0;
            border-bottom: 2px solid transparent;
            background: transparent;
            padding: 10px 16px;
            border-radius: 0;
            transition: all 0.2s ease;
        }
        .product-tabs .nav-link:hover { color: #0e2e45; }
        .product-tabs .nav-link.active {
            color: #0e2e45;
            background: transparent;
            border-bottom-color: #ffc508;
        }

        /* Footer */
        .product-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .product-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .product-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .product-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .product-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Delete Product Modal (mirrors logout modal)
           ============================================ */
        .product-delete-modal-dialog { max-width: 400px; }
        .product-delete-modal .modal-content { border-radius: 18px; }

        .product-delete-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .product-delete-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .product-delete-modal-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 16px;
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8088;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .product-delete-modal-body { background-color: #fff; }

        .product-delete-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .product-delete-modal-footer .btn { white-space: nowrap; }

        .product-delete-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .product-delete-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .product-delete-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .product-delete-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           ============================================ */

        /* Search field: a plain inline input on desktop, but on mobile it
           collapses behind the search icon and opens as a dropdown panel. */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu,
            .filter-dd .dropdown-menu.filter-menu {
                position: static !important;
                display: block !important;
                float: none;
                width: 100%;
                margin: 0;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                background: transparent;
            }
            /* keep the two selects at their natural inline width on desktop */
            .filter-dd .filter-menu .form-select { width: auto !important; }
        }
        @media (max-width: 991.98px) {
            .search-dd .dropdown-menu.search-menu {
                width: min(82vw, 360px);
            }
            .filter-dd .dropdown-menu.filter-menu {
                width: min(82vw, 320px);
            }

            /* Maximize the horizontally-scrolling table: pull the card out to
               the page gutter, force single-line cells and a comfortable min
               width so columns (Product Info, SKU…) keep their size and the
               user swipes instead of seeing squished / wrapped text. */
            .products-table-card {
                margin-left: -0.75rem;
                margin-right: -0.75rem;
                border-radius: 0 !important;
            }
            .products-table-card .table {
                min-width: 860px;
            }
            .products-table-card .table th,
            .products-table-card .table td {
                white-space: nowrap;
            }
            .products-table-card .table th:first-child,
            .products-table-card .table td:first-child {
                min-width: 240px;
            }

            /* Pagination lives inside .table-responsive and shares the table's
               single horizontal scrollbar. Match the table min-width so the
               bar spans the full scroll width and stays aligned under it. */
            .pagination-bar {
                flex-wrap: nowrap;
                min-width: 860px;
            }
            .pagination-bar .pagination {
                --bs-pagination-padding-x: 0.5rem;
                --bs-pagination-padding-y: 0.25rem;
                --bs-pagination-font-size: 0.8rem;
                margin-bottom: 0;
            }

            /* Smaller modal type so dialogs fit a phone screen. */
            .modal-title { font-size: 1rem; }
            .modal-body { font-size: 0.85rem; }
            .modal .form-label,
            .modal .form-control,
            .modal .form-select,
            .modal .input-group-text,
            .modal .btn,
            .modal small,
            .modal .small { font-size: 0.8rem; }
            .modal .product-section-title { font-size: 0.62rem; }
            .product-modal-header,
            .product-delete-modal-header {
                padding: 14px 16px;
            }
            .product-modal-footer,
            .product-delete-modal-footer {
                padding: 12px 16px;
            }

            /* Reduce modal spacing/size on small screens (complements the
               font rules above so dialogs fit a phone without huge gaps). */
            .product-modal .modal-dialog,
            .product-delete-modal .modal-dialog {
                margin: 0.5rem;
            }
            .product-modal .modal-body .p-4,
            .product-delete-modal .modal-body.p-4 {
                padding: 1rem !important;
            }
            .product-modal .row.g-3 {
                --bs-gutter-y: 0.5rem;
            }
            .product-modal .product-side-panel .ratio {
                max-width: 200px;
                margin-left: auto;
                margin-right: auto;
            }
            .product-modal .product-field-input.fs-5 {
                font-size: 0.85rem !important;
            }
        }
    </style>

    <script>
        function previewImage(input, previewId, placeholderId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var preview = document.getElementById(previewId);
                    preview.style.backgroundImage = 'url(' + e.target.result + ')';
                    document.getElementById(placeholderId).style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        const allMaterials = @json($rawMaterials);

        function addMaterialRow(data = null) {
            const body = document.getElementById('bomItemsBody');
            const emptyState = document.getElementById('bomEmptyState');
            if (emptyState) emptyState.style.display = 'none';

            const tr = document.createElement('tr');
            const rowIndex = body.children.length;

            let optionsHtml = '<option value="" disabled selected>Select Material</option>';
            allMaterials.forEach(m => {
                const selected = data && data.raw_material_id == m.raw_material_id ? 'selected' : '';
                optionsHtml += `<option value="${m.raw_material_id}" ${selected}>${m.name} (${m.unit})</option>`;
            });

            tr.innerHTML = `
                <td class="ps-4">
                    <select name="materials[${rowIndex}][raw_material_id]" class="form-select form-select-sm bg-white border" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="materials[${rowIndex}][quantity_required]" 
                        class="form-control form-control-sm bg-white border" 
                        value="${data ? data.pivot.quantity_required : 1}" required>
                </td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove(); checkBomEmptyState();">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            body.appendChild(tr);
        }

        function checkBomEmptyState() {
            const body = document.getElementById('bomItemsBody');
            const emptyState = document.getElementById('bomEmptyState');
            if (body.children.length === 0) {
                emptyState.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editProductModal = document.getElementById('editProductModal');
            editProductModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var product = JSON.parse(button.getAttribute('data-product'));

                var form = document.getElementById('editProductForm');
                form.action = '/admin/products/' + product.product_id;

                // Populate Fields
                document.getElementById('editName').value = product.name;
                document.getElementById('editSku').value = product.sku;
                document.getElementById('editCategoryId').value = product.category_id;
                document.getElementById('editBrand').value = product.brand;
                document.getElementById('editPrice').value = product.price;
                window.setMaterialUnit(document.getElementById('editUnit'), product.unit);
                document.getElementById('editStock').value = product.stock;
                document.getElementById('editLowStock').value = product.low_stock_threshold;
                document.getElementById('editUnitsOnDisplay').value = product.units_on_display ?? 0;
                document.getElementById('editUnitsSponsored').value = product.units_sponsored ?? 0;
                document.getElementById('editUnitsDamaged').value = product.units_damaged ?? 0;
                document.getElementById('editUnitsConsumed').value = product.units_consumed ?? 0;
                document.getElementById('editDescription').value = product.description;
                document.getElementById('editIsCustomizable').checked = product.is_customizable == 1;
                document.getElementById('editStatus').value = product.status || "active";
                document.getElementById('editDepartment').value = product.department || '';

                // Populate BOM
                const bomBody = document.getElementById('bomItemsBody');
                bomBody.innerHTML = '';
                if (product.raw_materials && product.raw_materials.length > 0) {
                    product.raw_materials.forEach(material => {
                        addMaterialRow(material);
                    });
                }
                checkBomEmptyState();

                // Handle Image
                var preview = document.getElementById('editImagePreview');
                var placeholder = document.getElementById('editImagePlaceholder');

                if (product.image_url) {
                    preview.style.backgroundImage = 'url(' + product.image_url + ')';
                    placeholder.style.display = 'none';
                } else {
                    preview.style.backgroundImage = "url('{{ asset('img/FABLAB-LOGO.png') }}')";
                    placeholder.style.display = 'flex';
                }
            });

            // Delete Modal Logic
            var deleteProductModal = document.getElementById('deleteProductModal');
            deleteProductModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var form = document.getElementById('deleteProductForm');
                form.action = '/admin/products/' + id;

                document.getElementById('deleteProductName').textContent = name;
            });
        });
    </script>
@endsection
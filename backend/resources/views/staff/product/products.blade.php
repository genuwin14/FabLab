@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="productFilterForm" method="GET" action="{{ route('staff.products.index') }}"
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
                                    <a href="{{ route('staff.products.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        title="Export">
                                        <i class="bi bi-download small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Export</span>
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
                                                            <div class="mt-1">
                                                                @include('shared.customizable-badge', ['product' => $product])
                                                            </div>
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
                                                            $percentage = $product->stock > 0 ? min(100, ($product->stock / 100) * 100) : 0;
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
                                                        <button class="btn btn-light btn-sm rounded-circle"
                                                            data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                            data-product="{{ json_encode($product) }}" title="Edit Product">
                                                            <i class="bi bi-pencil text-warning"></i>
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

    <!-- Edit Product Modal -->
    @include('staff.product.components.modal-edit-product')

    <style>
        /* ============================================
           Shared Product Modal Theme (Edit)
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
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md
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
            .product-modal-header {
                padding: 14px 16px;
            }
            .product-modal-footer {
                padding: 12px 16px;
            }

            /* Reduce modal spacing/size on small screens (complements the
               font rules above so dialogs fit a phone without huge gaps). */
            .product-modal .modal-dialog {
                margin: 0.5rem;
            }
            .product-modal .modal-body .p-4 {
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

        document.addEventListener('DOMContentLoaded', function () {
            var editProductModal = document.getElementById('editProductModal');
            editProductModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var product = JSON.parse(button.getAttribute('data-product'));

                var form = document.getElementById('editProductForm');
                form.action = '/staff/products/' + product.product_id;

                document.getElementById('editName').value = product.name;
                document.getElementById('editSku').value = product.sku;
                document.getElementById('editCategoryId').value = product.category_id;
                document.getElementById('editBrand').value = product.brand;
                document.getElementById('editPrice').value = product.price;
                window.setMaterialUnit(document.getElementById('editUnit'), product.unit);
                document.getElementById('editStock').value = product.stock;
                document.getElementById('editLowStock').value = product.low_stock_threshold;
                document.getElementById('editDescription').value = product.description;
                document.getElementById('editIsCustomizable').checked = product.is_customizable == 1;
                document.getElementById('editStatus').value = product.status || "active";

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
        });
    </script>
@endsection

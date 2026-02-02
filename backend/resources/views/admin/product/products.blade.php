@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Products</h4>
                            <p class="text-muted small mb-0">Manage your product catalog and inventory.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Server-side export placeholder -->
                            <button class="btn btn-outline-secondary d-flex align-items-center gap-2 rounded-pill px-3">
                                <i class="bi bi-download small"></i>
                                <span class="small fw-bold">Export</span>
                            </button>
                            <button class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="bi bi-plus-lg small"></i>
                                <span class="small fw-bold">Add Product</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filters & Search -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 rounded-end-pill ps-0"
                                            placeholder="Search products...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select rounded-pill">
                                        <option selected>All Categories</option>
                                        <option value="1">Electronics</option>
                                        <option value="2">Raw Materials</option>
                                        <option value="3">Finished Goods</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select rounded-pill">
                                        <option selected>Stock Status</option>
                                        <option value="in_stock">In Stock</option>
                                        <option value="low_stock">Low Stock</option>
                                        <option value="out_of_stock">Out of Stock</option>
                                    </select>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-light rounded-circle" data-bs-toggle="tooltip" title="Refresh">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 text-muted small text-uppercase border-0 rounded-start-2">
                                                Product Info</th>
                                            <th class="text-muted small text-uppercase border-0">SKU</th>
                                            <th class="text-muted small text-uppercase border-0">Category</th>
                                            <th class="text-muted small text-uppercase border-0">Price (unit)</th>
                                            <th class="text-muted small text-uppercase border-0">Stock</th>
                                            <th class="text-muted small text-uppercase border-0">Status</th>
                                            <th
                                                class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($products as $product)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                                            style="width: 48px; height: 48px; background-size: cover; background-position: center; {{ $product->image ? "background-image: url('{$product->image}');" : '' }}">
                                                            @if(!$product->image)
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
                                                    @if($product->stock <= 0)
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
                                                        <span
                                                            class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill d-flex align-items-center gap-1 w-auto d-inline-flex">
                                                            <i class="bi bi-check-circle-fill" style="font-size: 0.75rem;"></i>
                                                            Active
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm rounded-circle" type="button"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical text-muted"></i>
                                                        </button>
                                                        <ul
                                                            class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                                            <li>
                                                                <button class="dropdown-item small" data-bs-toggle="modal"
                                                                    data-bs-target="#editProductModal"
                                                                    data-product="{{ json_encode($product) }}">
                                                                    <i class="bi bi-pencil me-2 text-warning"></i> Edit
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item small text-danger"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                                                                    data-id="{{ $product->product_id }}"
                                                                    data-name="{{ $product->name }}">
                                                                    <i class="bi bi-trash me-2"></i> Delete
                                                                </button>
                                                            </li>
                                                        </ul>
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
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">Showing 1 to 10 of 42 entries</span>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0 gap-1">
                                        <li class="page-item disabled"><a class="page-link rounded-2 border-0"
                                                href="#">Prev</a></li>
                                        <li class="page-item active"><a class="page-link rounded-2 border-0 bg-primary"
                                                href="#">1</a></li>
                                        <li class="page-item"><a class="page-link rounded-2 border-0 text-dark"
                                                href="#">2</a></li>
                                        <li class="page-item"><a class="page-link rounded-2 border-0 text-dark"
                                                href="#">3</a></li>
                                        <li class="page-item"><a class="page-link rounded-2 border-0 text-primary"
                                                href="#">Next</a></li>
                                    </ul>
                                </nav>
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
                document.getElementById('editCost').value = product.cost;
                document.getElementById('editUnit').value = product.unit;
                document.getElementById('editStock').value = product.stock;
                document.getElementById('editLowStock').value = product.low_stock_threshold;
                document.getElementById('editDescription').value = product.description;
                document.getElementById('editIsCustomizable').checked = product.is_customizable == 1;

                if (product.supplier_id) {
                    document.getElementById('editSupplierId').value = product.supplier_id;
                } else {
                    document.getElementById('editSupplierId').value = "";
                }

                // Handle Image
                var preview = document.getElementById('editImagePreview');
                var placeholder = document.getElementById('editImagePlaceholder');

                if (product.image) {
                    preview.style.backgroundImage = 'url(' + product.image + ')';
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
<!-- Edit Product Modal -->
<div class="modal fade product-modal" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="product-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="product-eyebrow">Staff</span>
                        <span class="product-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editProductModalLabel">Edit Product</h5>
                    </div>
                    <button type="button" class="product-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-0">
                        <!-- Left: Image & Settings -->
                        <div class="col-lg-4 product-side-panel p-4">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block w-100">
                                    <div class="ratio ratio-1x1 bg-white rounded-3 border overflow-hidden"
                                        id="editImagePreview"
                                        style="background-size: cover; background-position: center;">
                                        <div class="d-flex align-items-center justify-content-center h-100"
                                            id="editImagePlaceholder">
                                            <i class="bi bi-camera text-muted fs-1 opacity-50"></i>
                                        </div>
                                    </div>
                                    <label class="product-photo-edit" style="cursor: pointer;">
                                        <i class="bi bi-pencil-fill"></i>
                                        <input type="file" name="image_file" class="d-none"
                                            onchange="previewImage(this, 'editImagePreview', 'editImagePlaceholder')">
                                    </label>
                                </div>
                                <div class="small text-muted mt-2">Product Image</div>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <div class="product-secured-card">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="editIsCustomizable"
                                            name="is_customizable">
                                        <label class="form-check-label small fw-bold" for="editIsCustomizable">
                                            Customizable Product
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                    <textarea name="description" id="editDescription"
                                        class="form-control product-field-input" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Form Fields -->
                        <div class="col-lg-8 p-4">
                            <h6 class="product-section-title">
                                <i class="bi bi-box-seam me-2"></i>Core Details
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="editName"
                                        class="form-control product-field-input fw-bold text-dark fs-5" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">SKU / Code <span class="text-danger">*</span></label>
                                    <input type="text" name="sku" id="editSku"
                                        class="form-control product-field-input font-monospace" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="editCategoryId"
                                        class="form-select product-field-input" required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                    <input type="text" name="brand" id="editBrand"
                                        class="form-control product-field-input">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                    <select name="status" id="editStatus"
                                        class="form-select product-field-input">
                                        <option value="active">Active</option>
                                        <option value="functional">Functional</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="broken">Broken / Defective</option>
                                    </select>
                                </div>
                            </div>

                            <h6 class="product-section-title">
                                <i class="bi bi-tag me-2"></i>Pricing & Inventory
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text product-input-addon">₱</span>
                                        <input type="number" step="0.01" name="price" id="editPrice"
                                            class="form-control product-field-input fw-bold text-success" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    @include('shared.unit-select', [
                                        'id' => 'editUnit',
                                        'class' => 'product-field-input',
                                        'hint' => false,
                                    ])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Stock</label>
                                    <input type="number" name="stock" id="editStock"
                                        class="form-control product-field-input" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Alert</label>
                                    <input type="number" name="low_stock_threshold" id="editLowStock"
                                        class="form-control product-field-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="product-modal-footer">
                        <button type="button" class="btn product-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn product-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mobile-responsive modal rules (ResponsiveMobileNote §6). */
    @media (max-width: 991.98px) {
        /* §6a — shrink type + spacing on phones */
        #editProductModal .modal-title { font-size: 1rem; }
        #editProductModal .modal-body { font-size: 0.85rem; }
        #editProductModal .form-label,
        #editProductModal .form-control,
        #editProductModal .form-select,
        #editProductModal .input-group-text,
        #editProductModal .btn,
        #editProductModal small,
        #editProductModal .small { font-size: 0.8rem; }
        #editProductModal .product-section-title { font-size: 0.62rem; }
        #editProductModal .modal-dialog { margin: 0.5rem; }
        #editProductModal .product-modal-header { padding: 14px 16px; }
        #editProductModal .product-side-panel.p-4,
        #editProductModal .col-lg-8.p-4 { padding: 1rem !important; }
        #editProductModal .row.g-3 { --bs-gutter-y: 0.5rem; }

        /* §6a — cap the big square image preview */
        #editProductModal .product-side-panel .ratio {
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        #editProductModal .product-field-input.fs-5 { font-size: 0.85rem !important; }

        /* §6c — footer with 2 pill buttons stacks full-width */
        #editProductModal .product-modal-footer {
            padding: 12px 16px;
            flex-direction: column;
            align-items: stretch;
        }
        #editProductModal .product-modal-footer > .btn { width: 100%; }
    }
</style>

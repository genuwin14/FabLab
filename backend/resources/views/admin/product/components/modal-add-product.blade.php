<!-- Add Product Modal -->
<div class="modal fade product-modal" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="product-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="product-eyebrow">Admin</span>
                        <span class="product-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addProductModalLabel">Add New Product</h5>
                    </div>
                    <button type="button" class="product-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-0">
                        <!-- Left: Image & Settings -->
                        <div class="col-lg-4 product-side-panel p-4">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block w-100">
                                    <div class="ratio ratio-1x1 bg-white rounded-3 border overflow-hidden"
                                        style="background-image: url('{{ asset('img/FABLAB-LOGO.png') }}'); background-size: cover; background-position: center;"
                                        id="addImagePreview">
                                        <div class="d-flex align-items-center justify-content-center h-100"
                                            id="addImagePlaceholder">
                                            <i class="bi bi-camera text-muted fs-1 opacity-50"></i>
                                        </div>
                                    </div>
                                    <label class="product-photo-edit" style="cursor: pointer;">
                                        <i class="bi bi-pencil-fill"></i>
                                        <input type="file" name="image_file" class="d-none"
                                            onchange="previewImage(this, 'addImagePreview', 'addImagePlaceholder')">
                                    </label>
                                </div>
                                <div class="small text-muted mt-2">Product Image</div>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <div class="product-secured-card">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="addIsCustomizable"
                                            name="is_customizable">
                                        <label class="form-check-label small fw-bold" for="addIsCustomizable">
                                            Customizable Product
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                    <textarea name="description" class="form-control product-field-input" rows="5"
                                        placeholder="Enter detailed product description..."></textarea>
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
                                    <input type="text" name="name"
                                        class="form-control product-field-input fw-bold text-dark fs-5"
                                        placeholder="e.g. White Ceramic Mug 11oz" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">SKU / Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="sku" id="addSku"
                                            class="form-control product-field-input font-monospace"
                                            placeholder="e.g. PRD-123456" required>
                                        <button class="btn product-sku-btn" type="button"
                                            id="btnGenerateSku" title="Auto-generate SKU">
                                            <i class="bi bi-magic"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select product-field-input" required>
                                        <option selected disabled value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                    <input type="text" name="brand" class="form-control product-field-input"
                                        placeholder="e.g. Yiwu / Epson">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                    <select name="status" class="form-select product-field-input">
                                        <option value="active" selected>Active</option>
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
                                    <label class="form-label small fw-bold text-muted text-uppercase">Selling Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text product-input-addon">₱</span>
                                        <input type="number" step="0.01" name="price"
                                            class="form-control product-field-input fw-bold text-success"
                                            placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                    <select name="unit" class="form-select product-field-input" required>
                                        <option value="pcs" selected>Pieces (pcs)</option>
                                        <option value="set">Set</option>
                                        <option value="box">Box</option>
                                        <option value="roll">Roll</option>
                                        <option value="ream">Ream</option>
                                        <option value="kg">Kg</option>
                                        <option value="meter">Meter</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Current Stock</label>
                                    <input type="number" name="stock" class="form-control product-field-input"
                                        placeholder="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Alert</label>
                                    <input type="number" name="low_stock_threshold"
                                        class="form-control product-field-input" placeholder="e.g. 20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="product-modal-footer">
                        <button type="button" class="btn product-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn product-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnGenerate = document.getElementById('btnGenerateSku');
        const skuInput = document.getElementById('addSku');

        if (btnGenerate) {
            btnGenerate.addEventListener('click', function () {
                const now = new Date();
                const year = now.getFullYear().toString().slice(-2);
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const day = now.getDate().toString().padStart(2, '0');
                const random = Math.random().toString(36).substring(2, 6).toUpperCase();

                const generatedSku = `PRD-${year}${month}${day}-${random}`;
                skuInput.value = generatedSku;

                skuInput.classList.add('is-valid');
                setTimeout(() => skuInput.classList.remove('is-valid'), 1500);
            });
        }

        const addProductModal = document.getElementById('addProductModal');
        if (addProductModal) {
            addProductModal.addEventListener('show.bs.modal', function () {
                if (!skuInput.value) {
                    btnGenerate.click();
                }
            });
        }
    });
</script>

<!-- Edit Product Modal -->
<div class="modal fade product-modal" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="product-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="product-eyebrow">Admin</span>
                        <span class="product-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editProductModalLabel">Edit Product</h5>
                    </div>
                    <button type="button" class="product-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <ul class="nav product-tabs px-4 pt-3" id="editProductTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold small text-uppercase" id="basic-tab"
                            data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
                            Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small text-uppercase" id="materials-tab"
                            data-bs-toggle="tab" data-bs-target="#materials-info" type="button" role="tab">
                            Materials (BOM)
                        </button>
                    </li>
                </ul>

                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="tab-content" id="editProductTabsContent">
                        <!-- Tab 1: Basic Info -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
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
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Department</label>
                                            <select id="editDepartment" name="department" class="form-select product-field-input">
                                                <option value="">— Uncategorized —</option>
                                                @foreach(\App\Enums\Department::cases() as $dept)
                                                    <option value="{{ $dept->value }}">{{ $dept->value }}</option>
                                                @endforeach
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

                                    <h6 class="product-section-title mt-4">
                                        <i class="bi bi-bar-chart-fill me-2"></i>Report Tracking
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">On Display</label>
                                            <input type="number" min="0" name="units_on_display" id="editUnitsOnDisplay"
                                                class="form-control product-field-input">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Sponsored</label>
                                            <input type="number" min="0" name="units_sponsored" id="editUnitsSponsored"
                                                class="form-control product-field-input">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Damaged</label>
                                            <input type="number" min="0" name="units_damaged" id="editUnitsDamaged"
                                                class="form-control product-field-input">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Consumed</label>
                                            <input type="number" min="0" name="units_consumed" id="editUnitsConsumed"
                                                class="form-control product-field-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Materials (BOM) -->
                        <div class="tab-pane fade" id="materials-info" role="tabpanel">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">Bill of Materials</h6>
                                        <p class="text-muted small mb-0">
                                            Define which raw materials are required to build this product. Tick
                                            <strong>Only when designed</strong> for anything spent decorating it rather
                                            than making it &mdash; a plain order then leaves it on the shelf.
                                        </p>
                                    </div>
                                    <button type="button" class="btn product-btn-save rounded-pill px-3"
                                        onclick="addMaterialRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Material
                                    </button>
                                </div>

                                <div class="table-responsive border rounded-3 overflow-hidden">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr class="bg-primary bg-opacity-10">
                                                <th class="small text-uppercase text-primary fw-bold ps-4 py-3" style="width: 44%">Material</th>
                                                <th class="small text-uppercase text-primary fw-bold py-3" style="width: 22%">Quantity Required</th>
                                                <th class="small text-uppercase text-primary fw-bold py-3 text-center" style="width: 24%">Only When Designed</th>
                                                <th class="small text-uppercase text-primary fw-bold text-end pe-4 py-3" style="width: 10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="bomItemsBody">
                                            <!-- BOM items injected here -->
                                        </tbody>
                                    </table>
                                    <div id="bomEmptyState" class="text-center py-4 text-muted small">
                                        No materials linked to this product yet.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="product-modal-footer">
                        <button type="button" class="btn product-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn product-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Update Product & BOM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

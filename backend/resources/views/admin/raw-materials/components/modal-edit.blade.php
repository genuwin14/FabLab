<!-- Edit Raw Material Modal -->
<div class="modal fade material-modal" id="editRawMaterialModal" tabindex="-1" aria-labelledby="editRawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="material-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-eyebrow">Admin</span>
                        <span class="material-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editRawMaterialModalLabel">Edit Raw Material</h5>
                    </div>
                    <button type="button" class="material-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form id="editRawMaterialForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="p-4">
                        <h6 class="material-section-title">
                            <i class="bi bi-box-seam me-2"></i>Material Details
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Material Name <span class="text-danger">*</span></label>
                                <input type="text" id="editMaterialName" name="name"
                                    class="form-control material-field-input fw-bold text-dark" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier <span class="text-danger">*</span></label>
                                <select id="editMaterialSupplier" name="supplier_id"
                                    class="form-select material-field-input" required>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Department</label>
                                <select id="editMaterialDepartment" name="department" class="form-select material-field-input">
                                    <option value="">— Uncategorized —</option>
                                    @foreach(\App\Enums\Department::cases() as $dept)
                                        <option value="{{ $dept->value }}">{{ $dept->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea id="editMaterialDescription" name="description"
                                    class="form-control material-field-input" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Update Image (Optional)</label>
                                <input type="file" class="form-control material-field-input" name="image_file"
                                    accept="image/*" onchange="previewMaterialImage(this, 'editMaterialPreview')">
                                <div class="mt-2 text-center">
                                    <img id="editMaterialPreview" src="#" alt="Preview"
                                        class="rounded-3 d-none shadow-sm" style="max-height: 150px; max-width: 100%;">
                                </div>
                            </div>
                        </div>

                        <h6 class="material-section-title">
                            <i class="bi bi-tag me-2"></i>Pricing & Inventory
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cost per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text material-input-addon">₱</span>
                                    <input type="number" step="0.01" id="editMaterialCost" name="cost_per_unit"
                                        class="form-control material-field-input fw-bold text-success" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                <input type="text" id="editMaterialUnit" name="unit"
                                    class="form-control material-field-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Threshold</label>
                                <input type="number" step="0.01" id="editMaterialThreshold" name="low_stock_threshold"
                                    class="form-control material-field-input" required>
                            </div>
                        </div>

                        {{-- Stock and the four report counters are no longer typed
                             here. They move only through Record Usage, which writes
                             a ledger row for every change — see the Usage Log tab. --}}
                        <div class="usage-locked-strip mt-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-lock text-muted flex-shrink-0"></i>
                                <div class="flex-grow-1 min-w-0">
                                    <p class="text-muted mb-0 text-uppercase fw-semibold"
                                        style="letter-spacing: 0.06em; font-size: 0.62rem;">Current Stock</p>
                                    <h6 class="fw-bold text-dark mb-0" id="editMaterialStockDisplay">—</h6>
                                </div>
                                <button type="button" class="btn btn-light btn-sm rounded-pill px-3 flex-shrink-0"
                                    data-bs-dismiss="modal" id="editMaterialUsageLink">
                                    <i class="bi bi-clipboard-check text-primary me-1"></i>
                                    <span class="fw-bold" style="font-size: 0.75rem;">Record Usage</span>
                                </button>
                            </div>
                            <p class="text-muted mb-0 mt-2" style="font-size: 0.72rem;">
                                Stock, consumed, damaged, sponsored and display counts are kept by the usage ledger
                                so the report always matches the shelf.
                            </p>
                        </div>
                    </div>

                    <div class="material-modal-footer">
                        <button type="button" class="btn material-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn material-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Update Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

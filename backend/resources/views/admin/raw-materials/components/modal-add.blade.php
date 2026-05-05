<!-- Add Raw Material Modal -->
<div class="modal fade material-modal" id="addRawMaterialModal" tabindex="-1" aria-labelledby="addRawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="material-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-eyebrow">Admin</span>
                        <span class="material-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addRawMaterialModalLabel">Add New Raw Material</h5>
                    </div>
                    <button type="button" class="material-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('admin.raw-materials.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="p-4">
                        <h6 class="material-section-title">
                            <i class="bi bi-box-seam me-2"></i>Material Details
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Material Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control material-field-input fw-bold text-dark"
                                    placeholder="e.g. Leather Fabric" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select material-field-input" required>
                                    <option value="" disabled selected>Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea name="description" class="form-control material-field-input" rows="3"
                                    placeholder="Optional notes..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Material Image</label>
                                <input type="file" class="form-control material-field-input" name="image_file"
                                    accept="image/*" onchange="previewMaterialImage(this, 'addMaterialPreview')">
                                <div class="mt-2 text-center">
                                    <img id="addMaterialPreview" src="#" alt="Preview"
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
                                    <input type="number" step="0.01" name="cost_per_unit"
                                        class="form-control material-field-input fw-bold text-success" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                <input type="text" name="unit" class="form-control material-field-input"
                                    placeholder="e.g. meters" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Initial Stock</label>
                                <input type="number" step="0.01" name="stock_quantity"
                                    class="form-control material-field-input" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Threshold</label>
                                <input type="number" step="0.01" name="low_stock_threshold"
                                    class="form-control material-field-input" value="10" required>
                            </div>
                        </div>
                    </div>

                    <div class="material-modal-footer">
                        <button type="button" class="btn material-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn material-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Save Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewMaterialImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

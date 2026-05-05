<!-- Edit Texture Modal -->
<div class="modal fade texture-modal" id="editTextureModal" tabindex="-1" aria-labelledby="editTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="texture-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="texture-eyebrow">Admin</span>
                        <span class="texture-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editTextureModalLabel">Edit Texture</h5>
                    </div>
                    <button type="button" class="texture-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form id="editTextureForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="p-4">
                        <h6 class="texture-section-title">
                            <i class="bi bi-layers me-2"></i>Texture Details
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Texture Name <span class="text-danger">*</span></label>
                                <input type="text" id="editTextureName" name="name"
                                    class="form-control texture-field-input fw-bold text-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier</label>
                                <select id="editTextureSupplier" name="supplier_id"
                                    class="form-select texture-field-input">
                                    <option value="">-- No Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea id="editTextureDescription" name="description"
                                    class="form-control texture-field-input" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Update Image (Optional)</label>
                                <input type="file" class="form-control texture-field-input" name="image_file"
                                    accept="image/*" onchange="previewImage(this, 'editTexturePreview')">
                                <div class="mt-2 text-center">
                                    <img id="editTexturePreview" src="#" alt="Preview"
                                        class="rounded-3 shadow-sm" style="max-height: 150px; max-width: 100%;">
                                </div>
                            </div>
                        </div>

                        <h6 class="texture-section-title">
                            <i class="bi bi-tag me-2"></i>Pricing & Inventory
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cost per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text texture-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" id="editTextureCost" name="cost_per_unit"
                                        class="form-control texture-field-input fw-bold text-success">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Price Modifier</label>
                                <div class="input-group">
                                    <span class="input-group-text texture-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" id="editTexturePriceModifier" name="price_modifier"
                                        class="form-control texture-field-input"
                                        placeholder="Extra cost when applied">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Stock Quantity</label>
                                <input type="number" step="0.01" min="0" id="editTextureStock" name="stock_quantity"
                                    class="form-control texture-field-input">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Alert</label>
                                <input type="number" step="0.01" min="0" id="editTextureThreshold" name="low_stock_threshold"
                                    class="form-control texture-field-input">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                <input type="text" id="editTextureUnit" name="unit"
                                    class="form-control texture-field-input">
                            </div>
                        </div>
                    </div>

                    <div class="texture-modal-footer">
                        <button type="button" class="btn texture-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn texture-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Update Texture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

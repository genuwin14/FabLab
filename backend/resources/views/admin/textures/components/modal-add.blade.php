<!-- Add Texture Modal -->
<div class="modal fade texture-modal" id="addTextureModal" tabindex="-1" aria-labelledby="addTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="texture-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="texture-eyebrow">Admin</span>
                        <span class="texture-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addTextureModalLabel">Add New Texture</h5>
                    </div>
                    <button type="button" class="texture-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('admin.textures.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="p-4">
                        <h6 class="texture-section-title">
                            <i class="bi bi-layers me-2"></i>Texture Details
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Texture Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control texture-field-input fw-bold text-dark"
                                    placeholder="e.g. Matte Finish" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier</label>
                                <select name="supplier_id" class="form-select texture-field-input">
                                    <option value="">-- No Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Department</label>
                                <select name="department" class="form-select texture-field-input">
                                    <option value="">— Uncategorized —</option>
                                    @foreach(\App\Enums\Department::cases() as $dept)
                                        <option value="{{ $dept->value }}">{{ $dept->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea name="description" class="form-control texture-field-input" rows="2"
                                    placeholder="Describe the visual characteristics..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Texture Image (Preview)</label>
                                <input type="file" class="form-control texture-field-input" name="image_file"
                                    accept="image/*" onchange="previewImage(this, 'addTexturePreview')">
                                <div class="mt-2 text-center">
                                    <img id="addTexturePreview" src="#" alt="Preview"
                                        class="rounded-3 d-none shadow-sm" style="max-height: 150px; max-width: 100%;">
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
                                    <input type="number" step="0.01" min="0" name="cost_per_unit"
                                        class="form-control texture-field-input fw-bold text-success" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Price Modifier</label>
                                <div class="input-group">
                                    <span class="input-group-text texture-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" name="price_modifier"
                                        class="form-control texture-field-input" value="0"
                                        placeholder="Extra cost when applied">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Stock Quantity</label>
                                <input type="number" step="0.01" min="0" name="stock_quantity"
                                    class="form-control texture-field-input" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Alert</label>
                                <input type="number" step="0.01" min="0" name="low_stock_threshold"
                                    class="form-control texture-field-input" value="10">
                            </div>
                            <div class="col-12">
                                @include('shared.unit-select', [
                                    'id' => 'addTextureUnit',
                                    'class' => 'texture-field-input',
                                    'selected' => 'pcs',
                                    'hint' => false,
                                ])
                            </div>
                        </div>

                        <h6 class="texture-section-title mt-4">
                            <i class="bi bi-bar-chart-fill me-2"></i>Report Tracking
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">On Display</label>
                                <input type="number" step="0.01" min="0" name="units_on_display"
                                    class="form-control texture-field-input" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Sponsored</label>
                                <input type="number" step="0.01" min="0" name="units_sponsored"
                                    class="form-control texture-field-input" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Damaged</label>
                                <input type="number" step="0.01" min="0" name="units_damaged"
                                    class="form-control texture-field-input" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Consumed</label>
                                <input type="number" step="0.01" min="0" name="units_consumed"
                                    class="form-control texture-field-input" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="texture-modal-footer">
                        <button type="button" class="btn texture-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn texture-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Save Texture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

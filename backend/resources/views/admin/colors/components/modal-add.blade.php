<!-- Add Color Modal -->
<div class="modal fade color-modal" id="addColorModal" tabindex="-1" aria-labelledby="addColorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="color-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-eyebrow">Admin</span>
                        <span class="color-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addColorModalLabel">Add New Color</h5>
                    </div>
                    <button type="button" class="color-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('admin.colors.store') }}" method="POST">
                    @csrf

                    <div class="p-4">
                        <h6 class="color-section-title">
                            <i class="bi bi-palette me-2"></i>Color Details
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="addColorName">
                                    Color Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="addColorName"
                                    class="form-control color-field-input fw-bold text-dark"
                                    placeholder="e.g. Navy Blue" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="addColorHexText">
                                    Swatch <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2 hex-pair" data-hex-pair>
                                    <input type="color" id="addColorHexPicker"
                                        class="form-control form-control-color color-field-input"
                                        value="{{ old('hex_code', '#1b2a4a') }}" aria-label="Pick a colour">
                                    <input type="text" name="hex_code" id="addColorHexText"
                                        class="form-control color-field-input font-monospace"
                                        placeholder="#1B2A4A" value="{{ old('hex_code', '#1b2a4a') }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" required>
                                </div>
                                <small class="text-muted">Pick a shade or paste a brand hex code.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="addColorDescription">
                                    Description
                                </label>
                                <input type="text" name="description" id="addColorDescription"
                                    class="form-control color-field-input"
                                    placeholder="e.g. Matte cotton, special order" value="{{ old('description') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="addColorPrice">
                                    Surcharge
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text color-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" max="999999.99"
                                        name="price_modifier" id="addColorPrice"
                                        class="form-control color-field-input"
                                        value="{{ old('price_modifier', '0.00') }}">
                                </div>
                                <small class="text-muted">Added on top of the product price. Leave at 0 for a free colour.</small>
                            </div>
                        </div>
                    </div>

                    <div class="color-modal-footer">
                        <button type="button" class="btn color-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn color-btn-save rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Color
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

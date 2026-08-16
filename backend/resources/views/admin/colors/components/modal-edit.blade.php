<!-- Edit Color Modal — fields are filled from the card's data attributes on show -->
<div class="modal fade color-modal" id="editColorModal" tabindex="-1" aria-labelledby="editColorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="color-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-eyebrow">Admin</span>
                        <span class="color-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editColorModalLabel">Edit Color</h5>
                    </div>
                    <button type="button" class="color-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="p-4">
                        <h6 class="color-section-title">
                            <i class="bi bi-palette me-2"></i>Color Details
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="editColorName">
                                    Color Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="editColorName"
                                    class="form-control color-field-input fw-bold text-dark" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="editColorHexText">
                                    Swatch <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2 hex-pair" data-hex-pair>
                                    <input type="color" id="editColorHexPicker"
                                        class="form-control form-control-color color-field-input"
                                        aria-label="Pick a colour">
                                    <input type="text" name="hex_code" id="editColorHexText"
                                        class="form-control color-field-input font-monospace"
                                        pattern="^#[0-9A-Fa-f]{6}$" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="editColorDescription">
                                    Description
                                </label>
                                <input type="text" name="description" id="editColorDescription"
                                    class="form-control color-field-input">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="editColorPrice">
                                    Surcharge
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text color-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" max="999999.99"
                                        name="price_modifier" id="editColorPrice"
                                        class="form-control color-field-input">
                                </div>
                                <small class="text-muted">
                                    Repricing a colour does not change designs already in a cart or on an order.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="color-modal-footer">
                        <button type="button" class="btn color-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn color-btn-save rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade equipment-modal" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="equipment-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="equipment-eyebrow">Admin</span>
                        <span class="equipment-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="editEquipmentModalLabel">Edit Equipment</h5>
                    </div>
                    <button type="button" class="equipment-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form id="editEquipmentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4">
                        <h6 class="equipment-section-title">
                            <i class="bi bi-tools me-2"></i>Equipment Details
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted text-uppercase">Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" id="editEquipmentName" name="name"
                                    class="form-control equipment-field-input fw-bold text-dark" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Brand / Model</label>
                                <input type="text" id="editEquipmentBrand" name="brand"
                                    class="form-control equipment-field-input">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Property Number</label>
                                <input type="text" id="editEquipmentPropertyNo" name="property_no"
                                    class="form-control equipment-field-input font-monospace">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Date Acquired</label>
                                <input type="date" id="editEquipmentDate" name="date_acquired"
                                    class="form-control equipment-field-input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text equipment-input-addon">₱</span>
                                    <input type="number" step="0.01" min="0" id="editEquipmentCost" name="cost"
                                        class="form-control equipment-field-input fw-bold text-success" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                <select id="editEquipmentStatus" name="status" class="form-select equipment-field-input" required>
                                    <option value="Serviceable">Serviceable</option>
                                    <option value="Non-Serviceable">Non-Serviceable</option>
                                    <option value="Functional">Functional</option>
                                    <option value="Returned to supplier for repair">Returned to supplier for repair</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                                <input type="text" id="editEquipmentNotes" name="notes"
                                    class="form-control equipment-field-input">
                            </div>
                        </div>
                    </div>

                    <div class="equipment-modal-footer">
                        <button type="button" class="btn equipment-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn equipment-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Update Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

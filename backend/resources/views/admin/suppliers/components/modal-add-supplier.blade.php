<!-- Add Supplier Modal -->
<div class="modal fade supplier-modal" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="supplier-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="supplier-eyebrow">Admin</span>
                        <span class="supplier-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addSupplierModalLabel">
                            Add New Supplier
                        </h5>
                    </div>
                    <button type="button" class="supplier-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.suppliers.store') }}" method="POST" class="m-0">
                @csrf

                <div class="modal-body p-4 bg-white">
                    <h6 class="supplier-section-title">
                        <i class="bi bi-building me-2"></i>Company Details
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                class="form-control supplier-field-input fw-bold text-dark"
                                placeholder="e.g. Ace Hardware" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control supplier-field-input"
                                placeholder="e.g. John Doe">
                        </div>
                    </div>

                    <h6 class="supplier-section-title">
                        <i class="bi bi-telephone me-2"></i>Contact Information
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text supplier-input-addon">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control supplier-field-input"
                                    placeholder="email@company.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text supplier-input-addon">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="text" name="phone" class="form-control supplier-field-input"
                                    placeholder="+63 900 000 0000">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Physical Address</label>
                            <textarea name="address" class="form-control supplier-field-input" rows="2"
                                placeholder="Full address..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="supplier-modal-footer">
                    <button type="button" class="btn supplier-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn supplier-btn-save rounded-pill px-4">
                        <i class="bi bi-check2 me-1"></i>Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

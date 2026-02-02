<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editSupplierForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Company Name</label>
                            <input type="text" name="name" id="editSupplierName"
                                class="form-control rounded-pill bg-light border-0 px-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Contact Person</label>
                            <input type="text" name="contact_person" id="editSupplierPerson"
                                class="form-control rounded-pill bg-light border-0 px-3">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" id="editSupplierEmail"
                                class="form-control rounded-pill bg-light border-0 px-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                            <input type="text" name="phone" id="editSupplierPhone"
                                class="form-control rounded-pill bg-light border-0 px-3">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Physical Address</label>
                            <textarea name="address" id="editSupplierAddress"
                                class="form-control bg-light border-0 rounded-3 px-3 py-2" rows="2"></textarea>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="button" class="btn btn-light rounded-pill px-4 me-2"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Supplier</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
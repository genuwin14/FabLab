<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="updateStatusModalLabel">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="updateStatusForm" method="POST">
                    @csrf
                    <input type="hidden" id="updateOrderId" name="order_id">

                    <div class="mb-4">
                        <p class="text-center text-muted fw-medium fs-6" id="modalConfirmationText">
                            Are you sure you want to proceed?
                        </p>
                    </div>

                    <input type="hidden" name="status" id="updateStatusInput">

                    <div class="mb-4 d-none" id="paymentRefContainer">
                        <label for="paymentReference" class="form-label text-muted small fw-bold text-uppercase">Payment
                            Reference #</label>
                        <input type="text" class="form-control form-control-lg shadow-sm border-0 bg-light fw-medium"
                            id="paymentReference" name="payment_reference" placeholder="Enter Cashier Ref #">
                        <div class="form-text small text-primary fst-italic"><i
                                class="bi bi-info-circle me-1"></i>Required to start processing</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0 position-absolute top-0 end-0 z-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <!-- <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 72px; height: 72px;">
                        <i class="bi bi-arrow-repeat text-primary display-6"></i>
                    </div> -->
                    <h4 class="fw-bold text-dark mb-2">Update Status</h4>
                    <p class="text-muted mb-0" id="modalConfirmationText">
                        Are you sure you want to proceed?
                    </p>
                </div>

                <form id="updateStatusForm" method="POST" class="text-start">
                    @csrf
                    <input type="hidden" id="updateOrderId" name="order_id">
                    <input type="hidden" name="status" id="updateStatusInput">

                    <div class="mb-4 d-none bg-light p-3 rounded-4" id="paymentRefContainer">
                        <label for="paymentReference"
                            class="form-label text-muted small fw-bold text-uppercase mb-2">Payment Reference</label>
                        <input type="text"
                            class="form-control shadow-none border-0 bg-white p-3 fw-bold text-dark rounded-3"
                            id="paymentReference" name="payment_reference" placeholder="Enter Reference #">
                        <div class="form-text small text-primary fst-italic mt-2">
                            <i class="bi bi-info-circle me-1"></i>Required to start processing
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                            Confirm Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
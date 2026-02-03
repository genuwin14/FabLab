<div class="modal fade" id="checkoutPreviewModal" tabindex="-1" aria-labelledby="checkoutPreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="checkoutPreviewModalLabel">Confirm Your Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        Please review your order details below before placing it.
                        <strong>Payment is Cash on Pickup.</strong>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-uppercase text-muted small">Order Items</h6>
                <div class="table-responsive bg-light rounded-3 p-3 mb-4">
                    <table class="table table-borderless table-sm align-middle mb-0" id="previewTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="previewItemsBody">
                            <!-- Items will be injected via JS -->
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="2" class="text-end fw-bold pt-3">Total Amount:</td>
                                <td class="text-end fw-bold pt-3 fs-5 text-primary" id="previewTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted">Customer Details</h6>
                            <p class="mb-0 fw-medium text-dark">{{ auth()->user()->name }}</p>
                            <p class="mb-0 text-muted small">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted">Payment Method</h6>
                            <div class="d-flex align-items-center text-success fw-bold">
                                <i class="bi bi-cash me-2 fs-5"></i>
                                Cash on Pickup
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Back to
                    Cart</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm"
                    id="confirmPlaceOrderBtn">
                    Place Order Now
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="orderDetailModalLabel">Order Details: <span class="text-primary"
                        id="viewOrderNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold small text-uppercase text-muted mb-2">Customer Info</h6>
                            <p class="mb-1 fw-bold text-dark" id="viewCustomerName"></p>
                            <!-- <p class="mb-0 text-muted small">customer@example.com</p> -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold small text-uppercase text-muted mb-2">Order Date</h6>
                            <p class="mb-0 fw-medium text-dark" id="viewOrderDate"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold small text-uppercase text-muted mb-2">Payment Info</h6>
                            <p class="mb-1 fw-bold text-success" id="viewPaymentMethod">Cash on Pickup</p>
                            <p class="mb-0 small text-muted d-none" id="viewPaymentRefContainer">
                                Ref: <span class="fw-bold text-dark" id="viewPaymentRef"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 small text-uppercase text-muted">Items</h6>
                <div class="table-responsive border rounded-3 mb-4">
                    <table class="table table-borderless table-sm align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr class="text-muted small">
                                <th class="ps-3 py-2">Product</th>
                                <th class="text-center py-2">Qty</th>
                                <th class="text-end py-2">Price</th>
                                <th class="text-end pe-3 py-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="viewOrderItems">
                            <!-- Items injected via JS -->
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end fw-bold pt-3">Total Amount:</td>
                                <td class="text-end fw-bold pt-3 fs-5 text-primary pe-3" id="viewOrderTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold small"
                        onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print Packing Slip
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
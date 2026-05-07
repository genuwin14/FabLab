<!-- Cancel Purchase Order Modal -->
<div class="modal fade purchase-cancel-modal" id="cancelPOModal" tabindex="-1" aria-labelledby="cancelPOModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered purchase-cancel-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="purchase-cancel-modal-header">
                <div class="purchase-cancel-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="cancelPOModalLabel">Cancel Purchase Order</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 purchase-cancel-modal-body">
                <p class="text-dark mb-1 text-center">
                    Mark <span class="fw-bold font-monospace text-dark">{{ $purchaseOrder->po_number }}</span> as
                    <span class="fw-bold text-danger">Cancelled</span>?
                </p>
                <p class="text-muted small text-center mb-0">
                    This action cannot be undone if the order was already processed.
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="purchase-cancel-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 purchase-cancel-keep-btn"
                    data-bs-dismiss="modal">
                    Keep Order
                </button>
                <form action="{{ route('staff.purchase.updateStatus', $purchaseOrder->purchase_order_id) }}"
                    method="POST" class="m-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 purchase-cancel-confirm-btn">
                        <i class="bi bi-x-lg me-2"></i>Yes, Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

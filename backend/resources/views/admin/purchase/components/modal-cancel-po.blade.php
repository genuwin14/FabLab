<!-- Cancel Purchase Order Modal -->
<div class="modal fade" id="cancelPOModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <!-- <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Cancel Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> -->
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width: 64px; height: 64px;">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-2">Are you sure?</h6>
                <p class="text-muted small mb-0">
                    This will mark the purchase order as <strong>Cancelled</strong>. This action cannot be undone if the
                    order was already processed.
                </p>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Keep
                    Order</button>
                <form action="{{ route('admin.purchase.updateStatus', $purchaseOrder->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        Yes, Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
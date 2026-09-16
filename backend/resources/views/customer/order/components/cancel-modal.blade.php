<!-- Cancel Order Modal -->
<div class="modal fade qv-modal" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-body p-4 p-lg-5 text-center">
                <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <span class="qv-icon qv-icon-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>

                <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">My Orders</span>
                <h5 id="cancelOrderModalLabel" class="qv-title mb-2">Cancel Order</h5>
                <p class="text-muted small mb-4">This action cannot be undone.</p>

                <div class="qv-tile mb-4">
                    <div class="qv-tile-label">Order</div>
                    <div id="cancelOrderNumber" class="qv-tile-value font-monospace"></div>
                </div>

                <p class="text-muted small mb-4">
                    Once cancelled, this order can no longer be reactivated.
                </p>

                {{-- The form wraps only the confirm button, so it must not be the
                     thing that stacks — .qv-actions handles the stacking and the
                     form goes inside it as a full-width grid cell. --}}
                <div class="qv-actions">
                    <form id="cancelOrderForm" method="POST" action="" class="m-0 d-grid">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="bi bi-x-lg me-2"></i> Yes, Cancel Order
                        </button>
                    </form>
                    <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">
                        No, Keep It
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

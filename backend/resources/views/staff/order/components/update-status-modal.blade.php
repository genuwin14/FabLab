<div class="modal fade order-modal" id="updateStatusModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Staff</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            Update Order Status
                        </h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2" id="modalConfirmationText">
                    Are you sure you want to proceed?
                </div>
            </div>

            <form id="updateStatusForm" method="POST" class="m-0">
                @csrf
                <input type="hidden" id="updateOrderId" name="order_id">
                <input type="hidden" name="status" id="updateStatusInput">

                <div class="modal-body p-4 bg-white">
                    <!-- Payment Reference (Hidden by default) -->
                    <div id="paymentRefContainer" class="d-none">
                        <h6 class="order-section-title">
                            <i class="bi bi-credit-card-2-front me-2"></i>Payment Reference
                        </h6>
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-3 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div class="small fw-bold">Reference number is required to start processing this order.</div>
                        </div>
                        <input type="text"
                            class="form-control order-field-input"
                            id="paymentReference" name="payment_reference"
                            placeholder="Enter Reference #">
                    </div>

                    @include('partials.order-materials-panel', ['panelId' => 'updateMaterialsPanel'])

                    <!-- Default body when payment ref hidden -->
                    <div id="updateStatusInfo" class="text-center py-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 64px; height: 64px; background-color: rgba(255, 197, 8, 0.12); color: #997404;">
                            <i class="bi bi-arrow-repeat fs-3"></i>
                        </div>
                        <p class="text-muted small mb-0">
                            Confirm to mark this order with the new status.
                        </p>
                    </div>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn order-btn-save rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i>Confirm Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Mobile-responsive modal rules (ResponsiveMobileNote §6). */
    @media (max-width: 991.98px) {
        /* §6a — shrink type + spacing on phones */
        #updateStatusModal .modal-title { font-size: 1rem; }
        #updateStatusModal .modal-body { font-size: 0.85rem; }
        #updateStatusModal .form-label,
        #updateStatusModal .form-control,
        #updateStatusModal .form-select,
        #updateStatusModal .input-group-text,
        #updateStatusModal .btn,
        #updateStatusModal small,
        #updateStatusModal .small { font-size: 0.8rem; }
        #updateStatusModal .order-section-title { font-size: 0.62rem; }
        #updateStatusModal .modal-dialog { margin: 0.5rem; }
        #updateStatusModal .order-modal-header { padding: 14px 16px; }
        #updateStatusModal .modal-body.p-4 { padding: 1rem !important; }

        /* §6c — footer with 2 pill buttons stacks full-width */
        #updateStatusModal .order-modal-footer {
            padding: 12px 16px;
            flex-direction: column;
            align-items: stretch;
        }
        #updateStatusModal .order-modal-footer > .btn { width: 100%; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const updateModal = document.getElementById('updateStatusModal');
        if (!updateModal) return;

        // Toggle the default info block when the payment-ref section appears.
        // The materials panel counts too: "confirm to mark this order with the
        // new status" is not worth saying underneath a table that already
        // spells out exactly what the step will do.
        const paymentRef = document.getElementById('paymentRefContainer');
        const infoBlock = document.getElementById('updateStatusInfo');
        const observer = new MutationObserver(function () {
            if (paymentRef.classList.contains('d-none')) {
                infoBlock.classList.remove('d-none');
            } else {
                infoBlock.classList.add('d-none');
            }
        });
        observer.observe(paymentRef, { attributes: true, attributeFilter: ['class'] });
    });
</script>

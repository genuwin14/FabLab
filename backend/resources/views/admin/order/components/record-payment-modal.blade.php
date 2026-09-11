{{-- Recording the customer's payment on an approved cashier order.

     The customer pays at PAXS against the transaction slip and
     walks away with an official receipt. The admin types that receipt's
     number here; it is what tells staff the order may go into production,
     and what the customer shows at the counter to collect it. Sits with the
     admin next to Review, and wears the shared .order-modal theme. --}}

<div class="modal fade order-modal" id="recordPaymentModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="recordPaymentTitle">Record Payment</h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    <span id="recordPaymentOrderNumber" class="fw-bold" style="color: #ffc508;"></span>
                    <span class="order-eyebrow-divider mx-1">·</span>
                    <span id="recordPaymentSubtitle">Paid at PAXS</span>
                </div>
            </div>

            <form method="POST" id="recordPaymentForm" class="m-0">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <h6 class="order-section-title">
                        <i class="bi bi-receipt me-2"></i>Official Receipt
                    </h6>

                    <div class="alert alert-warning border-0 d-flex align-items-start mb-3 rounded-3">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div class="small fw-semibold" id="recordPaymentNote">
                            Enter the number on the official receipt PAXS issued. Staff can start production
                            once it is recorded, and the customer will be told it is the number to bring when collecting.
                        </div>
                    </div>

                    <label for="recordPaymentInput" class="form-label small fw-semibold text-muted">Receipt Number</label>
                    <input type="text" class="form-control order-field-input font-monospace" id="recordPaymentInput"
                        name="payment_reference" placeholder="e.g. OR-123456" maxlength="255" autocomplete="off" required>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn order-btn-save rounded-pill px-4" id="recordPaymentSubmit">
                        <i class="bi bi-check-lg me-1"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('recordPaymentModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const existing = trigger.getAttribute('data-receipt') || '';
            const correcting = existing !== '';

            this.querySelector('#recordPaymentForm').action = trigger.getAttribute('data-url');
            this.querySelector('#recordPaymentOrderNumber').textContent = '#' + trigger.getAttribute('data-order-number');
            this.querySelector('#recordPaymentInput').value = existing;
            this.querySelector('#recordPaymentTitle').textContent = correcting ? 'Correct Receipt Number' : 'Record Payment';
            this.querySelector('#recordPaymentSubtitle').textContent = correcting ? 'Already recorded as paid' : 'Paid at PAXS';
            this.querySelector('#recordPaymentNote').textContent = correcting
                ? 'Replace the receipt number with the one on the official receipt. The customer is not notified again.'
                : 'Enter the number on the official receipt PAXS issued. Staff can start production once it is recorded, and the customer will be told it is the number to bring when collecting.';
            this.querySelector('#recordPaymentSubmit').innerHTML = correcting
                ? '<i class="bi bi-check-lg me-1"></i>Save Receipt Number'
                : '<i class="bi bi-check-lg me-1"></i>Record Payment';
        });

        modal.addEventListener('shown.bs.modal', function () {
            this.querySelector('#recordPaymentInput').focus();
        });
    })();
</script>

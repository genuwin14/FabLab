<div class="modal fade order-modal" id="reviewOrderModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            Review Order
                            <span id="reviewOrderNumber" class="ms-1" style="color: #ffc508;"></span>
                        </h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    Customer: <span id="reviewCustomerName" class="text-white fw-bold"></span>
                </div>
            </div>

            <form id="reviewOrderForm" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="status" id="reviewStatus" value="approved">

                <div class="modal-body p-4 bg-white">
                    <h6 class="order-section-title">
                        <i class="bi bi-box-seam me-2"></i>Stock Availability Check
                    </h6>

                    <div class="table-responsive border rounded-3 mb-3 overflow-hidden modal-table-scroll">
                        <table class="table table-hover align-middle mb-0 modal-table">
                            <thead>
                                <tr class="bg-primary bg-opacity-10">
                                    <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Product</th>
                                    <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">
                                        Req. Qty</th>
                                    <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">
                                        Stock</th>
                                    <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0">
                                        Availability</th>
                                </tr>
                            </thead>
                            <tbody id="reviewItemsBody" class="border-top-0">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Cancellation Reason (Hidden by default) -->
                    <div id="cancellationSection" class="d-none">
                        <h6 class="order-section-title">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Cancellation Reason
                        </h6>
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-3 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div class="small fw-bold">You are about to cancel this order. Stock will be restored.</div>
                        </div>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @php
                                $commonReasons = [
                                    'Insufficient stock',
                                    'Invalid payment reference',
                                    'Customer request',
                                    'Unavailable at this time',
                                    'Incomplete order details'
                                ];
                            @endphp
                            @foreach($commonReasons as $reason)
                                <button type="button" class="order-reason-chip"
                                    onclick="document.getElementById('reviewReason').value = '{{ $reason }}'">
                                    {{ $reason }}
                                </button>
                            @endforeach
                        </div>
                        <textarea name="reason" id="reviewReason" class="form-control order-field-input" rows="3"
                            placeholder="e.g., Insufficient stock for item X..."></textarea>
                    </div>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Close
                    </button>

                    <!-- Initial action buttons -->
                    <div id="actionButtons" class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger fw-semibold rounded-pill px-4"
                            onclick="showCancellation()">
                            <i class="bi bi-x-lg me-1"></i>Reject / Cancel
                        </button>
                        <button type="button" class="btn order-btn-save rounded-pill px-4" id="btnApproveOrder"
                            onclick="submitReviewWithLoading('approved')">
                            <span class="d-none spinner-border spinner-border-sm me-2" role="status"
                                aria-hidden="true"></span>
                            <span class="btn-text"><i class="bi bi-check-lg me-1"></i>Approve Order</span>
                        </button>
                    </div>

                    <!-- Cancel confirmation buttons (Hidden) -->
                    <div id="confirmCancelButton" class="d-none gap-2">
                        <button type="button" class="btn order-btn-cancel rounded-pill px-4"
                            onclick="hideCancellation()">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="btn btn-danger fw-semibold rounded-pill px-4"
                            onclick="submitReview('cancelled')">
                            <i class="bi bi-trash me-1"></i>Confirm Cancellation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showCancellation() {
        document.getElementById('cancellationSection').classList.remove('d-none');
        document.getElementById('actionButtons').classList.add('d-none');
        const confirmBox = document.getElementById('confirmCancelButton');
        confirmBox.classList.remove('d-none');
        confirmBox.classList.add('d-flex');
        document.getElementById('reviewReason').required = true;
    }

    function hideCancellation() {
        document.getElementById('cancellationSection').classList.add('d-none');
        document.getElementById('actionButtons').classList.remove('d-none');
        const confirmBox = document.getElementById('confirmCancelButton');
        confirmBox.classList.add('d-none');
        confirmBox.classList.remove('d-flex');
        document.getElementById('reviewReason').required = false;
        document.getElementById('reviewReason').value = '';
    }

    function submitReview(status) {
        document.getElementById('reviewStatus').value = status;
        document.getElementById('reviewOrderForm').submit();
    }

    function submitReviewWithLoading(status) {
        document.getElementById('reviewStatus').value = status;

        const btn = document.getElementById('btnApproveOrder');
        const spinner = btn.querySelector('.spinner-border');
        const text = btn.querySelector('.btn-text');

        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Processing...';

        document.getElementById('reviewOrderForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const reviewModal = document.getElementById('reviewOrderModal');
        if (reviewModal) {
            reviewModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('cancellationSection').classList.add('d-none');
                document.getElementById('actionButtons').classList.remove('d-none');
                const confirmBox = document.getElementById('confirmCancelButton');
                confirmBox.classList.add('d-none');
                confirmBox.classList.remove('d-flex');
                document.getElementById('reviewReason').value = '';
                document.getElementById('reviewReason').required = false;

                const btn = document.getElementById('btnApproveOrder');
                if (btn) {
                    btn.disabled = false;
                    btn.querySelector('.spinner-border').classList.add('d-none');
                    btn.querySelector('.btn-text').innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve Order';
                }
            });
        }
    });
</script>

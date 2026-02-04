<div class="modal fade" id="reviewOrderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 ps-4 pe-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Review Order <span id="reviewOrderNumber"
                            class="text-primary"></span></h5>
                    <p class="text-muted small mb-0">Customer: <span id="reviewCustomerName" class="fw-bold"></span></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Stock Check Table -->
                <div class="bg-light rounded-3 p-3 mb-4">
                    <h6 class="fw-bold mb-3 small text-uppercase text-muted">Stock Availability Check</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-0">Product</th>
                                    <th class="text-center">Req. Qty</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-end pe-0">Availability</th>
                                </tr>
                            </thead>
                            <tbody id="reviewItemsBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="reviewOrderForm" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="reviewStatus" value="approved">

                    <!-- Reason Field (Hidden by default) -->
                    <div id="cancellationSection" class="d-none">
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div class="small fw-bold">You are about to cancel this order.</div>
                        </div>
                        <label class="form-label fw-bold small text-muted">Reason for Cancellation</label>
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
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold"
                                    style="font-size: 0.7rem; --bs-btn-padding-y: .1rem; --bs-btn-padding-x: .5rem;"
                                    onclick="document.getElementById('reviewReason').value = '{{ $reason }}'">
                                    {{ $reason }}
                                </button>
                            @endforeach
                        </div>
                        <textarea name="reason" id="reviewReason" class="form-control bg-light border-0" rows="3"
                            placeholder="e.g., Insufficient stock for item X..."></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-light fw-bold rounded-pill px-4"
                            data-bs-dismiss="modal">Close</button>

                        <!-- Initial Buttons -->
                        <div id="actionButtons">
                            <button type="button" class="btn btn-outline-danger fw-bold rounded-pill px-4 me-2"
                                onclick="showCancellation()">
                                Reject / Cancel
                            </button>
                            <button type="button" class="btn btn-primary fw-bold rounded-pill px-4" id="btnApproveOrder"
                                onclick="submitReviewWithLoading('approved')">
                                <span class="d-none spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                <span class="btn-text"><i class="bi bi-check-lg me-1"></i> Approve Order</span>
                            </button>
                        </div>

                        <!-- Cancel Confirmation Button (Hidden) -->
                        <div id="confirmCancelButton" class="d-none">
                            <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4 me-2"
                                onclick="hideCancellation()">
                                Back
                            </button>
                            <button type="button" class="btn btn-danger fw-bold rounded-pill px-4"
                                onclick="submitReview('cancelled')">
                                Confirm Cancellation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showCancellation() {
        document.getElementById('cancellationSection').classList.remove('d-none');
        document.getElementById('actionButtons').classList.add('d-none');
        document.getElementById('confirmCancelButton').classList.remove('d-none');
        document.getElementById('reviewReason').required = true;
    }

    function hideCancellation() {
        document.getElementById('cancellationSection').classList.add('d-none');
        document.getElementById('actionButtons').classList.remove('d-none');
        document.getElementById('confirmCancelButton').classList.add('d-none');
        document.getElementById('reviewReason').required = false;
        document.getElementById('reviewReason').value = '';
    }

    function submitReview(status) {
        document.getElementById('reviewStatus').value = status;
        document.getElementById('reviewOrderForm').submit();
    }

    function submitReviewWithLoading(status) {
        document.getElementById('reviewStatus').value = status;

        // Show loading state
        var btn = document.getElementById('btnApproveOrder');
        var spinner = btn.querySelector('.spinner-border');
        var text = btn.querySelector('.btn-text');

        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Processing...';

        document.getElementById('reviewOrderForm').submit();
    }

    // Reset modal state on close
    document.addEventListener('DOMContentLoaded', function () {
        const reviewModal = document.getElementById('reviewOrderModal');
        if (reviewModal) {
            reviewModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('cancellationSection').classList.add('d-none');
                document.getElementById('actionButtons').classList.remove('d-none');
                document.getElementById('confirmCancelButton').classList.add('d-none');
                document.getElementById('reviewReason').value = '';
                document.getElementById('reviewReason').required = false;
            });
        }
    });
</script>
<div class="modal fade qv-modal" id="approvalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 p-lg-5 text-center">

                {{-- A cashier order waits on the admin; a PR order waits on the
                     customer to bring back a number, so it gets its own copy.
                     The icon and eyebrow change with it. --}}
                <div data-checkout-outcome="cash">
                    <span class="qv-icon qv-icon-success mb-4">
                        <i class="bi bi-clock-history"></i>
                    </span>

                    <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">Order Placed</span>
                    <h4 class="qv-title mb-3">Order Waiting for Approval</h4>

                    <p class="text-muted small mb-4">
                        Your order has been submitted successfully and is waiting for <strong>Admin Approval</strong>.
                    </p>

                    <div class="qv-tile text-start mb-4">
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            You can check the status of your order in the <strong>My Orders</strong> page.
                        </p>
                    </div>
                </div>

                <div data-checkout-outcome="pr" hidden>
                    <span class="qv-icon qv-icon-accent mb-4">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>

                    <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">Purchase Request</span>
                    <h4 class="qv-title mb-3">Order Held for Your PR</h4>

                    <p class="text-muted small mb-4">
                        File your Purchase Request with <strong>{{ config('fablab.procurement_email') }}</strong>,
                        then enter the PR number on your order to release it for review.
                    </p>

                    <div class="qv-tile text-start mb-4" style="border-color: rgba(255,197,8,.45); background: rgba(255,197,8,.08);">
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-clock-history me-1"></i>
                            You have <strong>{{ config('fablab.pr_deadline_days') }} days</strong>. Your items are held
                            until then — without a PR number the order closes and the stock is released.
                        </p>
                    </div>
                </div>

                <div class="qv-actions">
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-primary btn-lg shadow-sm">
                        Go to My Orders
                    </a>
                    <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal"
                        onclick="location.reload()">
                        Close &amp; Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

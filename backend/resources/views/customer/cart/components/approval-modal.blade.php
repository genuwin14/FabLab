<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-clock-history text-success display-4"></i>
                    </div>
                </div>

                {{-- A cashier order waits on the admin; a PR order waits on the
                     customer to bring back a number, so it gets its own copy. --}}
                <div data-checkout-outcome="cash">
                    <h4 class="fw-bold text-dark mb-3">Order Waiting for Approval</h4>

                    <p class="text-muted mb-4">
                        Your order has been submitted successfully and is waiting for <strong>Admin Approval</strong>.
                    </p>

                    <div class="alert alert-light border-0 rounded-3 mb-4">
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            You can check the status of your order in the <strong>My Orders</strong> page.
                        </p>
                    </div>
                </div>

                <div data-checkout-outcome="pr" hidden>
                    <h4 class="fw-bold text-dark mb-3">Order Held for Your PR</h4>

                    <p class="text-muted mb-4">
                        File your Purchase Request with <strong>{{ config('fablab.procurement_email') }}</strong>,
                        then enter the PR number on your order to release it for review.
                    </p>

                    <div class="alert alert-warning border-0 rounded-3 mb-4">
                        <p class="mb-0 small">
                            <i class="bi bi-clock-history me-1"></i>
                            You have <strong>{{ config('fablab.pr_deadline_days') }} days</strong>. Your items are held
                            until then — without a PR number the order closes and the stock is released.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('customer.orders.index') }}"
                        class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                        Go to My Orders
                    </a>
                    <button type="button" class="btn btn-light rounded-pill py-3 fw-bold text-muted"
                        data-bs-dismiss="modal" onclick="location.reload()">
                        Close & Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
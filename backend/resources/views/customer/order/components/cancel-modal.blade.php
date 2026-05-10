<!-- Cancel Order Modal -->
<div class="modal fade customer-cancel-order-modal" id="cancelOrderModal" tabindex="-1"
    aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered customer-cancel-order-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="customer-cancel-order-modal-header">
                <div class="customer-cancel-order-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="cancelOrderModalLabel">Cancel Order</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 customer-cancel-order-modal-body">
                <p class="text-dark mb-1 text-center">
                    Are you sure you want to cancel Order
                    <span id="cancelOrderNumber" class="fw-bold font-monospace text-dark"></span>?
                </p>
                <p class="text-muted small text-center mb-0">
                    Once cancelled, this order can no longer be reactivated.
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="customer-cancel-order-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 customer-cancel-order-keep-btn"
                    data-bs-dismiss="modal">
                    No, Keep It
                </button>
                <form id="cancelOrderForm" method="POST" action="" class="m-0">
                    @csrf
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 customer-cancel-order-confirm-btn">
                        <i class="bi bi-x-lg me-2"></i>Yes, Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
       Customer Cancel Order Modal (admin theme)
       ============================================ */
    .customer-cancel-order-modal-dialog { max-width: 420px; }
    .customer-cancel-order-modal .modal-content { border-radius: 18px; }

    .customer-cancel-order-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }
    .customer-cancel-order-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
    }
    .customer-cancel-order-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        border-radius: 16px;
        background: rgba(220, 53, 69, 0.15);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff8088;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .customer-cancel-order-modal-body { background-color: #fff; }

    .customer-cancel-order-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .customer-cancel-order-keep-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .customer-cancel-order-keep-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .customer-cancel-order-confirm-btn {
        background-color: #dc3545;
        border: 1px solid #dc3545;
        color: #fff;
        transition: all 0.2s ease;
    }
    .customer-cancel-order-confirm-btn:hover {
        background-color: #b02a37;
        border-color: #b02a37;
        color: #fff;
    }
</style>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-2">Cancel Order?</h4>
                <p class="text-muted mb-4">
                    Are you sure you want to cancel Order <span id="cancelOrderNumber"
                        class="fw-bold text-dark"></span>?
                    This action cannot be undone.
                </p>

                <form id="cancelOrderForm" method="POST" action="">
                    @csrf
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2">
                            Yes, Cancel Order
                        </button>
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2" data-bs-dismiss="modal">
                            No, Keep It
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
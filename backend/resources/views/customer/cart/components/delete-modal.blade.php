<!-- Delete Item Confirmation Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true"
    style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-trash3 text-danger display-4"></i>
                </div>
                <h5 class="fw-bold mb-2 text-dark">Remove Item?</h5>
                <p class="text-muted small mb-4">Are you sure you want to remove this item from your cart?</p>

                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold small"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4 rounded-pill fw-bold small"
                        id="confirmDeleteBtn">Remove</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Delete Item Confirmation Modal -->
<div class="modal fade qv-modal" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel"
    aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-body p-4 p-lg-5 text-center">
                <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <span class="qv-icon qv-icon-danger mb-4">
                    <i class="bi bi-trash3"></i>
                </span>

                <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">Cart</span>
                <h5 id="deleteItemModalLabel" class="qv-title mb-2">Remove Item?</h5>
                <p class="text-muted small mb-4">Are you sure you want to remove this item from your cart?</p>

                <div class="qv-actions">
                    <button type="button" class="btn btn-danger btn-lg" id="confirmDeleteBtn">
                        <i class="bi bi-trash3 me-2"></i> Remove
                    </button>
                    <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">Keep It</button>
                </div>
            </div>
        </div>
    </div>
</div>

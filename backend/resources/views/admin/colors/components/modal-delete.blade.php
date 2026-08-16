<!-- Retire Color Modal -->
<div class="modal fade color-delete-modal" id="deleteColorModal" tabindex="-1" aria-labelledby="deleteColorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered color-delete-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="color-delete-modal-header">
                <div class="color-delete-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteColorModalLabel">Retire Color</h5>
                <p class="text-white-50 small mb-0">It stops being offered on new designs</p>
            </div>

            <div class="modal-body p-4 color-delete-modal-body">
                <p class="text-dark mb-0 text-center">
                    Retire <span id="deleteColorName" class="fw-bold text-dark"></span>?
                    Customers won't see it in the customizer any more. Designs and orders already saved in this
                    colour keep it.
                </p>
            </div>

            <div class="color-delete-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 color-delete-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <form method="POST" action="" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 color-delete-confirm-btn">
                        <i class="bi bi-trash me-2"></i>Retire Color
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

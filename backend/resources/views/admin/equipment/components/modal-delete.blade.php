<!-- Delete Equipment Modal -->
<div class="modal fade" id="deleteEquipmentModal" tabindex="-1" aria-labelledby="deleteEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden rounded-4">
            <div class="p-4 text-center" style="background: linear-gradient(135deg, #b02a37 0%, #dc3545 100%);">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle"
                     style="width: 56px; height: 56px; background: rgba(255,255,255,0.15);">
                    <i class="bi bi-exclamation-triangle-fill text-white fs-3"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteEquipmentModalLabel">Delete Equipment</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-dark mb-0">
                    Are you sure you want to delete
                    <span id="deleteEquipmentName" class="fw-bold text-dark"></span>?
                </p>
            </div>
            <div class="d-flex justify-content-end gap-2 p-3 border-top">
                <button type="button" class="btn btn-light fw-semibold rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteEquipmentForm" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger fw-semibold rounded-pill px-4">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

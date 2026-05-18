<!-- Preview Design Modal -->
<div class="modal fade" id="previewDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0" style="background-color: #05111a; border-radius: 20px;">
            <div class="modal-header border-0 position-absolute w-100 p-4" style="z-index: 1051;">
                <h5 class="modal-title text-white fw-bold">3D Design Preview</h5>
                <button type="button" class="btn btn-dark-glass text-white rounded-circle p-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 600px; position: relative;">
                <!-- Three.js Container -->
                <div id="preview-three-container" style="width: 100%; height: 100%;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 preview-modal-footer">
                <button type="button" class="btn btn-soft-secondary rounded-pill px-4 tiny fw-bold"
                    data-bs-dismiss="modal">Close</button>
                <a href="#" id="preview-btn-edit" class="btn btn-soft-primary rounded-pill px-4 tiny fw-bold">
                    <i class="bi bi-pencil-square me-1"></i> Edit Design
                </a>
                <button type="button" id="preview-btn-add-to-cart"
                    class="btn btn-accent rounded-pill px-4 tiny fw-bold">
                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>
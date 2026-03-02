<!-- Preview Design Modal -->
<div class="modal fade" id="previewDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0" style="background-color: #05111a; border-radius: 20px;">
            <div class="modal-header border-0 position-absolute w-100 p-4" style="z-index: 1051;">
                <h5 class="modal-title text-white fw-bold">3D Design Preview</h5>
                <button type="button" class="btn btn-dark-glass text-white rounded-circle p-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 600px; position: relative;">
                <!-- Loading Spinner -->
                <div id="preview-loader"
                    class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center"
                    style="z-index: 1050; background: #05111a;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    </div>
                    <p class="text-white small fw-bold opacity-75">Generating 3D Visualization...</p>
                </div>
                <!-- Three.js Container -->
                <div id="preview-three-container" style="width: 100%; height: 100%;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4 tiny fw-bold"
                    data-bs-dismiss="modal">Close Preview</button>
                <a href="#" id="preview-btn-edit" class="btn btn-primary rounded-pill px-4 tiny fw-bold">Edit Design</a>
            </div>
        </div>
    </div>
</div>
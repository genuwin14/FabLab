<!-- Preview Design Modal -->
{{-- The stage stays dark — a 3D model reads badly on white — but the chrome
     around it is the same Quick View theme the rest of the customer dialogs
     use: floating close, eyebrow over the title, pill actions. The inline
     background outranks .qv-modal's white .modal-content on purpose. --}}
<div class="modal fade qv-modal" id="previewDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="background-color: #05111a;">
            <div class="modal-header border-0 position-absolute w-100 p-4 d-block" style="z-index: 1051;">
                <span class="qv-eyebrow"
                    style="background: rgba(255,255,255,.12); color: #fff;">My Designs</span>
                <h5 class="modal-title text-white fw-bold mt-2 mb-0">3D Design Preview</h5>
                <button type="button" class="qv-close qv-close-dark" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- modal-body-fill: the Three.js canvas sizes itself off this box,
                 so it stays a fixed frame rather than a scroll area. The cap
                 keeps 600px from pushing the modal past a short viewport. -->
            <div class="modal-body p-0 modal-body-fill"
                style="height: min(600px, calc(100dvh - 12rem)); position: relative;">
                <!-- Three.js Container -->
                <div id="preview-three-container" style="width: 100%; height: 100%;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-block">
                <div class="qv-actions qv-actions-row">
                    <button type="button" class="btn btn-soft-secondary tiny text-nowrap" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="preview-btn-edit" class="btn btn-soft-primary tiny text-nowrap">
                        <i class="bi bi-pencil-square me-1"></i> Edit Design
                    </a>
                    <button type="button" id="preview-btn-add-to-cart" class="btn btn-accent tiny text-nowrap">
                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

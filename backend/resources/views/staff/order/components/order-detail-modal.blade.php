<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header: Dark/Premium Style -->
            <div class="modal-header border-0 p-4 text-white" style="background-color: #05111a;">
                <div class="d-flex align-items-center">
                    <!-- <div class="bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                        <i class="bi bi-receipt text-primary fs-4"></i>
                    </div> -->
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="orderDetailModalLabel">Order Workspace: <span class="text-primary" id="viewOrderNumber"></span></h5>
                        <p class="text-white opacity-75 small mb-0">Review customer requirements and design specifications</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Column: Customer & Order Info -->
                    <div class="col-md-4 border-end bg-light p-4">
                        <div class="mb-4">
                            <h6 class="fw-bold small text-uppercase text-dark opacity-50 mb-3">Customer Profile</h6>
                            <div class="card border-0 shadow-sm rounded-3 p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div id="customerAvatar" class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 me-3" style="width: 54px; height: 54px;">
                                        <!-- Initial injected by JS -->
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-5" id="viewCustomerName"></div>
                                        <div class="text-secondary small" id="viewCustomerEmail"></div>
                                    </div>
                                </div>
                                <hr class="opacity-10 my-2">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-geo-alt text-primary small"></i>
                                    <span class="text-muted small" id="viewCustomerAddress">No address provided</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-telephone text-primary small"></i>
                                    <span class="text-secondary small" id="viewCustomerPhone">No phone provided</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold small text-uppercase text-dark opacity-50 mb-3">Order Metadata</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-white shadow-sm">
                                        <div class="tiny text-muted text-uppercase fw-bold">Placed On</div>
                                        <div class="small fw-bold text-dark" id="viewOrderDate"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-white shadow-sm">
                                        <div class="tiny text-muted text-uppercase fw-bold">Payment</div>
                                        <div class="small fw-bold text-success" id="viewPaymentMethod">Cash</div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="viewPaymentRefContainer">
                                    <div class="p-2 border rounded-3 bg-white shadow-sm border-primary">
                                        <div class="tiny text-primary text-uppercase fw-bold">Reference Number</div>
                                        <div class="fw-bold text-dark" id="viewPaymentRef"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 small py-2">
                            <i class="bi bi-info-circle-fill me-2"></i> Ensure all design snapshots match the inventory logs before processing.
                        </div>
                    </div>

                    <!-- Right Column: Item Workspace -->
                    <div class="col-md-8 p-4 bg-white" style="max-height: 70vh; overflow-y: auto;">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Order Contents</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-top border-light">
                                <thead class="bg-light">
                                    <tr class="text-dark opacity-50 tiny text-uppercase fw-bold">
                                        <th class="ps-3 py-3 border-0">Product & Specification</th>
                                        <th class="text-center py-3 border-0">Qty</th>
                                        <th class="text-end py-3 border-0">Price</th>
                                        <th class="text-end pe-3 py-3 border-0">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="viewOrderItems" class="border-0">
                                    <!-- Items injected via JS -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-end pe-3">
                            <span class="text-uppercase tiny fw-bold text-primary opacity-75 d-block mb-1">Order Total</span>
                            <h3 class="fw-bold text-primary mb-0" id="viewOrderTotal"></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Footer Section -->
            <div class="modal-footer bg-light border-top-0 p-4">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-shield-check me-1"></i> Verified Order Details
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold small shadow-sm" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Print Slip
                        </button>
                        <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Close Workspace</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Modal: Design Detail Zoom & 3D Preview -->
<div class="modal fade" id="designDetailPopup" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold small text-uppercase text-muted">Inspection Engine: 3D Visualization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="row g-4">
                    <div class="col-md-8">
                        <!-- 3D Viewer Container -->
                        <div id="staff-three-container" class="bg-dark rounded-4 position-relative border shadow-inner" style="height: 500px; overflow: hidden; background: radial-gradient(circle, #1a2a3a 0%, #05111a 100%);">
                            <!-- Canvas will be injected here -->
                            <div id="preview-loader" class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div class="tiny fw-bold text-uppercase opacity-50">Initializing 3D Scene...</div>
                            </div>
                            
                            <!-- Snapshot Fallback (Visible until 3D loads) -->
                            <img id="detailPopupImage" src="" class="img-fluid w-100 h-100 d-none" style="object-fit: contain; position: absolute; top:0; left:0; z-index: 1;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold small text-muted mb-3 d-flex justify-content-between">
                            <span>RECIPE CONFIGURATION</span>
                            <span class="badge bg-primary rounded-pill tiny">ACTIVE DESIGN</span>
                        </h6>
                        <div class="bg-dark rounded-3 p-3 overflow-auto border border-white border-opacity-10 shadow" style="height: 450px;">
                            <pre id="detailPopupRecipe" class="text-info tiny mb-0" style="white-space: pre-wrap; font-family: 'Courier New', monospace;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-md {
        font-size: 1.25rem;
    }
    .tiny {
        font-size: 0.75rem;
    }
    .btn-xs {
        padding: 0.2rem 0.6rem;
        font-size: 0.8rem;
    }
</style>
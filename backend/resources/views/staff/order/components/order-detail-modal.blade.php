<div class="modal fade order-modal" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Staff</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            Order Details
                            <span id="viewOrderNumber" class="ms-1" style="color: #ffc508;"></span>
                        </h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 small">
                    <span class="text-white-50">Customer:
                        <span id="viewCustomerName" class="text-white fw-bold"></span>
                    </span>
                </div>
            </div>

            <div class="modal-body p-4 bg-white">
                <!-- Meta Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-uppercase fw-bold text-muted"
                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Order Date</div>
                        <div class="fw-semibold text-dark small mt-1" id="viewOrderDate">—</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-uppercase fw-bold text-muted"
                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Payment Reference</div>
                        <div class="fw-semibold text-dark small mt-1 font-monospace" id="viewPaymentRefContainer">
                            <span id="viewPaymentRef">—</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-uppercase fw-bold text-muted"
                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Amount</div>
                        <div class="fw-bold text-primary mt-1" id="viewOrderTotal">₱0.00</div>
                    </div>
                </div>

                <!-- Customer Details -->
                <h6 class="order-section-title">
                    <i class="bi bi-person-circle me-2"></i>Customer Profile
                </h6>
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
                    style="background-color: #f8f9fa;">
                    <div id="customerAvatar"
                        class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 48px; height: 48px; background-color: #0e2e45; color: #ffc508; font-size: 0.9rem;">
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold text-dark" id="viewCustomerEmail" style="font-size: 0.85rem;"></div>
                        <div class="d-flex flex-wrap gap-3 mt-1 small text-muted">
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="bi bi-telephone"></i>
                                <span id="viewCustomerPhone">—</span>
                            </span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="bi bi-geo-alt"></i>
                                <span id="viewCustomerAddress">—</span>
                            </span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="bi bi-credit-card"></i>
                                <span id="viewPaymentMethod">Cash on Pickup</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <h6 class="order-section-title">
                    <i class="bi bi-box-seam me-2"></i>Order Items
                </h6>
                <div class="table-responsive border rounded-3 mb-0 overflow-hidden modal-table-scroll">
                    <table class="table table-hover align-middle mb-0 modal-table">
                        <thead>
                            <tr class="bg-primary bg-opacity-10">
                                <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Product</th>
                                <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">Qty</th>
                                <th class="text-end py-2 text-primary small text-uppercase fw-bold border-0">Unit Price
                                </th>
                                <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="viewOrderItems" class="border-top-0">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="order-modal-footer">
                <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn order-btn-save rounded-pill px-4" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print Slip
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Modal: Design Detail Zoom & 3D Preview -->
<div class="modal fade order-modal" id="designDetailPopup" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Staff</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            Design Inspection
                            <span class="ms-1" style="color: #ffc508;">3D</span>
                        </h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    Inspect the rendered 3D model and recipe configuration before processing.
                </div>
            </div>

            <div class="modal-body p-4 bg-white">
                <div class="row g-4">
                    <div class="col-md-8">
                        <!-- 3D Viewer Container -->
                        <div id="staff-three-container"
                            class="rounded-3 position-relative border overflow-hidden"
                            style="height: 460px; background: radial-gradient(circle, #1a2a3a 0%, #05111a 100%);">
                            <div id="preview-loader"
                                class="position-absolute top-50 start-50 translate-middle text-center text-white"
                                style="z-index: 2;">
                                <div class="spinner-border text-warning mb-2" role="status"></div>
                                <div class="fw-bold text-uppercase opacity-75"
                                    style="font-size: 0.7rem; letter-spacing: 0.06em;">Initializing 3D Scene...</div>
                            </div>
                            <img id="detailPopupImage" src=""
                                class="img-fluid w-100 h-100 d-none position-absolute top-0 start-0"
                                style="object-fit: contain; z-index: 1;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="order-section-title d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-code-slash me-2"></i>Recipe Config</span>
                            <span class="badge rounded-pill"
                                style="background-color: rgba(255, 197, 8, 0.15); color: #997404; font-size: 0.6rem;">ACTIVE</span>
                        </h6>
                        <div class="rounded-3 p-3 overflow-auto border design-recipe-box"
                            style="height: 410px; background-color: #05111a;">
                            <pre id="detailPopupRecipe" class="mb-0"
                                style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 0.7rem; color: #6ee7ff;"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-modal-footer">
                <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mobile-responsive modal rules (ResponsiveMobileNote §6). */
    @media (max-width: 991.98px) {
        /* §6a — shrink type + spacing on phones */
        .order-modal .modal-title { font-size: 1rem; }
        .order-modal .modal-body { font-size: 0.85rem; }
        .order-modal .form-label,
        .order-modal .form-control,
        .order-modal .form-select,
        .order-modal .input-group-text,
        .order-modal .btn,
        .order-modal small,
        .order-modal .small { font-size: 0.8rem; }
        .order-modal .order-section-title { font-size: 0.62rem; }
        .order-modal .modal-dialog { margin: 0.5rem; }
        .order-modal .order-modal-header { padding: 14px 16px; }
        .order-modal .modal-body.p-4 { padding: 1rem !important; }
        .order-modal .row.g-3 { --bs-gutter-y: 0.5rem; }
        .order-modal .row.g-4 { --bs-gutter-x: 1rem; --bs-gutter-y: 0.75rem; }

        /* §6c — footer with 2 pill buttons stacks full-width */
        .order-modal .order-modal-footer {
            padding: 12px 16px;
            flex-direction: column;
            align-items: stretch;
        }
        .order-modal .order-modal-footer > .btn { width: 100%; }

        /* §6c — in-modal table: restore scroll past .overflow-hidden */
        .order-modal .modal-table-scroll { overflow-x: auto !important; }
        .order-modal .modal-table { min-width: 520px; }
        .order-modal .modal-table th,
        .order-modal .modal-table td { white-space: nowrap; }

        /* §6c — two fixed-height panels (3D viewer + recipe) shrink */
        #designDetailPopup #staff-three-container { height: 280px !important; }
        #designDetailPopup .design-recipe-box { height: 220px !important; }
    }
</style>

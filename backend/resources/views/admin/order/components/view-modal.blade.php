<div class="modal fade order-modal" id="viewOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
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
                    <span id="viewStatusPill"></span>
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
                        <div class="fw-semibold text-dark small mt-1 font-monospace" id="viewPaymentRef">—</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-uppercase fw-bold text-muted"
                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Amount</div>
                        <div class="fw-bold text-primary mt-1" id="viewTotal">₱0.00</div>
                    </div>
                </div>

                <!-- Items -->
                <h6 class="order-section-title">
                    <i class="bi bi-box-seam me-2"></i>Order Items
                </h6>
                <div class="table-responsive border rounded-3 mb-3 overflow-hidden modal-table-scroll">
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
                        <tbody id="viewItemsBody" class="border-top-0">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Cancellation Reason (only when cancelled) -->
                <div id="viewReasonSection" class="d-none">
                    <h6 class="order-section-title">
                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Cancellation Reason
                    </h6>
                    <div class="alert alert-danger border-0 rounded-3 mb-0 small" id="viewReasonText"></div>
                </div>
            </div>

            <div class="order-modal-footer">
                <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Modal: Design Inspection (Zoom + 3D Preview) -->
<div class="modal fade order-modal" id="designDetailPopup" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
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
                    Inspect the rendered 3D model and recipe configuration of this customized item.
                </div>
            </div>

            <div class="modal-body p-4 bg-white">
                <div class="row g-3 g-md-4">
                    <div class="col-md-8">
                        <div id="admin-three-container"
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

<script>
    (function () {
        const STATUS_META = {
            'pending':          { label: 'Pending',          icon: 'bi-hourglass-split',   bg: 'rgba(255, 193, 7, 0.18)',  color: '#997404' },
            'approved':         { label: 'Approved',         icon: 'bi-clipboard-check',   bg: 'rgba(13, 110, 253, 0.12)', color: '#0d6efd' },
            'processing':       { label: 'Processing',       icon: 'bi-arrow-repeat',      bg: 'rgba(13, 202, 240, 0.15)', color: '#087990' },
            'ready_for_pickup': { label: 'Ready for Pickup', icon: 'bi-bag-check',         bg: 'rgba(255, 153, 0, 0.15)',  color: '#b95900' },
            'completed':        { label: 'Completed',        icon: 'bi-check-circle-fill', bg: 'rgba(25, 135, 84, 0.12)',  color: '#198754' },
            'cancelled':        { label: 'Cancelled',        icon: 'bi-x-circle-fill',     bg: 'rgba(220, 53, 69, 0.12)',  color: '#dc3545' },
        };

        function formatCurrency(value) {
            return '₱' + Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatDate(iso) {
            if (!iso) return '—';
            const d = new Date(iso);
            return d.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
        }

        function buildStatusPill(status) {
            const meta = STATUS_META[status] || STATUS_META['pending'];
            return `<span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill fw-semibold"
                        style="background-color: ${meta.bg}; color: ${meta.color}; font-size: 0.7rem;">
                        <i class="bi ${meta.icon}" style="font-size: 0.7rem;"></i>${meta.label}
                    </span>`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const viewModal = document.getElementById('viewOrderModal');
            if (!viewModal) return;

            viewModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const order = JSON.parse(button.getAttribute('data-order'));
                const items = JSON.parse(button.getAttribute('data-items'));

                document.getElementById('viewOrderNumber').textContent = '#' + order.order_number;
                document.getElementById('viewCustomerName').textContent =
                    order.user ? (order.user.fullname || order.user.name || 'Guest') : 'Guest';
                document.getElementById('viewStatusPill').innerHTML = buildStatusPill(order.status);
                document.getElementById('viewOrderDate').textContent = formatDate(order.created_at);
                document.getElementById('viewPaymentRef').textContent = order.payment_reference || '—';
                document.getElementById('viewTotal').textContent = formatCurrency(order.total_amount);

                const tbody = document.getElementById('viewItemsBody');
                tbody.innerHTML = '';
                items.forEach(item => {
                    const product = item.product || {};
                    const productName = product.name || '-';
                    const subtotal = (Number(item.price) || 0) * (Number(item.quantity) || 0);

                    const design = item.custom_design || item.customDesign;
                    const isCustom = !!(item.custom_design_id && design);
                    if (isCustom) design.product_name = productName;

                    const snapshot = isCustom ? design.snapshot : null;
                    const imgSrc = snapshot || product.image || '/img/FABLAB-LOGO.png';

                    const thumbHtml = isCustom && snapshot
                        ? `<img src="${imgSrc}" class="w-100 h-100 object-fit-cover rounded btn-popout-design" style="cursor: zoom-in;" data-design='${JSON.stringify(design).replace(/'/g, "&apos;")}' alt="">`
                        : `<img src="${imgSrc}" class="w-100 h-100 object-fit-cover rounded" alt="">`;

                    const customBadge = isCustom
                        ? '<span class="badge ms-1" style="background-color: rgba(255, 197, 8, 0.18); color: #997404; font-size: 0.55rem; vertical-align: middle;">TAILORED</span>'
                        : '<span class="badge ms-1" style="background-color: rgba(108, 117, 125, 0.12); color: #6c757d; font-size: 0.55rem; vertical-align: middle;">STANDARD</span>';

                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded border bg-white p-1 me-2" style="width: 40px; height: 40px;">
                                        ${thumbHtml}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">${productName}${customBadge}</div>
                                        <div class="text-muted font-monospace" style="font-size: 0.7rem;">SKU: ${product.sku ?? '-'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold text-dark">${item.quantity}</td>
                            <td class="text-end text-muted small">${formatCurrency(item.price)}</td>
                            <td class="text-end pe-3 fw-bold text-dark">${formatCurrency(subtotal)}</td>
                        </tr>
                    `;
                });

                const reasonSection = document.getElementById('viewReasonSection');
                const reasonText = document.getElementById('viewReasonText');
                if (order.status === 'cancelled' && order.reason) {
                    reasonSection.classList.remove('d-none');
                    reasonText.textContent = order.reason;
                } else {
                    reasonSection.classList.add('d-none');
                    reasonText.textContent = '';
                }
            });
        });
    })();
</script>

<div class="modal fade" id="checkoutPreviewModal" tabindex="-1" aria-labelledby="checkoutPreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent shadow-none">

            <!-- Receipt Container. modal-scroll-area: this modal has no
                 .modal-body, so the receipt itself is the scroll area — a long
                 order list scrolls on the receipt, not on the page. -->
            <div class="receipt-container modal-scroll-area mx-auto position-relative bg-white pt-4 px-4 pb-5 shadow-lg"
                style="width: 100%; max-width: 380px; clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%); border-radius: 5px;">

                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <img src="{{ asset('img/FABLAB-LOGO.png') }}" alt="Logo" class="mb-2"
                        style="width: 60px; height: 60px; object-fit: contain; filter: grayscale(100%);">
                    <h5 class="fw-bold text-uppercase mb-0" style="letter-spacing: 2px;">CSPC FABLAB</h5>
                    <p class="text-muted small mb-0">Camarines Sur Polytechnic Colleges</p>
                    <div class="my-3 border-bottom border-dark border-2 border-dashed"></div>
                    <h6 class="fw-bold text-uppercase mb-0">Order Preview</h6>
                    <p class="text-muted small" id="receiptDate"></p>
                </div>

                <!-- Customer Info -->
                <div class="mb-3 small">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Customer:</span>
                        <span class="fw-bold text-dark text-end">{{ auth()->user()->fullname }}</span>
                    </div>
                </div>

                <!-- Items Line -->
                <div class="border-top border-dark border-dashed my-2"></div>

                <!-- Items Table -->
                <div class="">
                    <table class="table table-borderless table-sm mb-0 small" id="previewTable">
                        <tbody id="previewItemsBody">
                            <!-- Items will be injected via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="border-top border-dark border-dashed my-2"></div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-uppercase small">Total Amount</span>
                    <span class="fw-bold fs-5" id="previewTotal"></span>
                </div>

                <div class="border-top border-dark border-dashed my-3"></div>

                <!-- Payment Info -->
                <div class="text-center mb-4">
                    <p class="small fw-bold mb-1">PAYMENT INSTRUCTION</p>
                    <p class="small text-muted mb-0">Please present this slip at the<br><strong>CSPC Cashier</strong>
                        for payment.</p>
                </div>

                <!-- Barcode Placeholder -->
                <div class="text-center opacity-75 mt-2">
                    <svg id="receiptBarcode"></svg>
                </div>

                <!-- Actions -->
                <div class="mt-4 d-grid gap-2">
                    <button type="button" class="btn btn-dark rounded-0 fw-bold py-2 text-uppercase"
                        id="confirmPlaceOrderBtn" style="letter-spacing: 1px;">
                        Confirm Order
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary rounded-0 fw-bold py-2 text-uppercase small border-0"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>

                <!-- Jagged Bottom Edge (CSS Trick) -->
                <div class="receipt-jagged-edge"></div>
            </div>

        </div>
    </div>
</div>

<style>
    .receipt-container {
        font-family: 'Courier New', Courier, monospace;
        position: relative;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    /* Jagged edge effect */
    .receipt-jagged-edge {
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 100%;
        height: 20px;
        background: radial-gradient(circle, transparent 50%, #fff 50%) 0 0/20px 20px repeat-x;
        transform: rotate(180deg);
    }
</style>

<!-- JsBarcode CDN -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkoutModalEl = document.getElementById('checkoutPreviewModal');
        checkoutModalEl.addEventListener('show.bs.modal', function (event) {
            // Set dynamic date
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            document.getElementById('receiptDate').innerText = new Date().toLocaleDateString('en-US', options);

            // Generate a temporary Reference Number for the barcode (e.g. TMP-Timestamp)
            const tempRef = 'REF-' + Date.now().toString().slice(-8);

            JsBarcode("#receiptBarcode", tempRef, {
                format: "CODE128",
                width: 1.5,
                height: 40,
                displayValue: true,
                fontSize: 14,
                fontOptions: "bold",
                background: "transparent",
                marginTop: 10,
                marginBottom: 10
            });
        });
    });
</script>
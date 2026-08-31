<div class="modal fade order-modal" id="reviewOrderModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            <span id="reviewModalTitle">Review Order</span>
                            <span id="reviewOrderNumber" class="ms-1" style="color: #ffc508;"></span>
                        </h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    Customer: <span id="reviewCustomerName" class="text-white fw-bold"></span>
                </div>
            </div>

            <form id="reviewOrderForm" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="status" id="reviewStatus" value="approved">

                <div class="modal-body p-4 bg-white">
                    <h6 class="order-section-title">
                        <i class="bi bi-box-seam me-2"></i>Stock Availability Check
                    </h6>

                    <div class="table-responsive border rounded-3 mb-3 overflow-hidden modal-table-scroll">
                        <table class="table table-hover align-middle mb-0 modal-table">
                            <thead>
                                <tr class="bg-primary bg-opacity-10">
                                    <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Product</th>
                                    <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">
                                        Req. Qty</th>
                                    <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">
                                        Stock</th>
                                    <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0">
                                        Availability</th>
                                </tr>
                            </thead>
                            <tbody id="reviewItemsBody" class="border-top-0">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    {{-- The reviewer is being asked to judge how much ink a
                         design takes. A 40px thumbnail is not enough to do that
                         on, so clicking one opens it here at a size you can
                         actually read coverage from.

                         Inline rather than a second modal: stacking a modal on
                         a modal fights the backdrop, and the materials table
                         this informs sits a few centimetres below anyway. --}}
                    <div id="reviewDesignPreview" class="review-design-preview mb-3" hidden>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="order-section-title mb-0">
                                <i class="bi bi-zoom-in me-2"></i><span id="reviewDesignPreviewTitle">Design</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted"
                                id="reviewDesignPreviewClose" aria-label="Close preview">
                                <i class="bi bi-x-lg"></i> Close
                            </button>
                        </div>
                        {{-- One fixed stage that all three layers sit inside,
                             stacked in z rather than in flow. Laid out normally
                             they queued up vertically instead — a 680px box
                             showing the model, the spinner and the snapshot at
                             the same time. --}}
                        <div class="review-design-stage border rounded-3 bg-light">
                            {{-- The live model, so the reviewer can turn the
                                 design round. Judging how much ink a print takes
                                 off one fixed angle means never seeing the back
                                 of a mug. Uses the same init() and
                                 loadDesignRecipePreview() the design popout on
                                 this page already runs, so there is one 3D
                                 pipeline rather than two.

                                 Always in the document, never toggled: init()
                                 measures this box to size the renderer, and
                                 anything that hides it first makes that zero.
                                 Empty, it draws nothing anyway. --}}
                            <div id="reviewDesignViewer"></div>

                            {{-- The snapshot, covering the model while the scene
                                 builds and left in place if it can't start. A
                                 preview is not worth failing an approval over. --}}
                            <img id="reviewDesignPreviewImage" src="" alt="Design preview">

                            <div id="reviewDesignPreviewLoader" hidden>
                                <div class="spinner-border text-warning mb-2" role="status"></div>
                                <div class="fw-bold text-uppercase text-muted"
                                    style="font-size: 0.65rem; letter-spacing: 0.06em;">Starting 3D preview…</div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1" id="reviewDesignPreviewHint" hidden>
                            <i class="bi bi-arrows-move me-1"></i>Drag to rotate, scroll to zoom.
                        </small>
                    </div>

                    @include('partials.order-materials-panel', ['panelId' => 'reviewMaterialsPanel'])

                    <!-- Cancellation Reason (Hidden by default) -->
                    <div id="cancellationSection" class="d-none">
                        <h6 class="order-section-title">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Cancellation Reason
                        </h6>
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-3 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div class="small fw-bold">You are about to cancel this order. Stock will be restored.</div>
                        </div>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @php
                                $commonReasons = [
                                    'Insufficient stock',
                                    'Invalid payment reference',
                                    'Customer request',
                                    'Unavailable at this time',
                                    'Incomplete order details'
                                ];
                            @endphp
                            @foreach($commonReasons as $reason)
                                <button type="button" class="order-reason-chip"
                                    onclick="document.getElementById('reviewReason').value = '{{ $reason }}'">
                                    {{ $reason }}
                                </button>
                            @endforeach
                        </div>
                        <textarea name="reason" id="reviewReason" class="form-control order-field-input" rows="3"
                            placeholder="e.g., Insufficient stock for item X..."></textarea>
                    </div>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Close
                    </button>

                    <!-- Initial action buttons -->
                    <div id="actionButtons" class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger fw-semibold rounded-pill px-4"
                            onclick="showCancellation()">
                            <i class="bi bi-x-lg me-1"></i>Reject / Cancel
                        </button>
                        <button type="button" class="btn order-btn-save rounded-pill px-4" id="btnApproveOrder"
                            onclick="submitReviewWithLoading('approved')">
                            <span class="d-none spinner-border spinner-border-sm me-2" role="status"
                                aria-hidden="true"></span>
                            <span class="btn-text"><i class="bi bi-check-lg me-1"></i>Approve Order</span>
                        </button>
                    </div>

                    <!-- Cancel confirmation buttons (Hidden) -->
                    <div id="confirmCancelButton" class="d-none gap-2">
                        <button type="button" class="btn order-btn-cancel rounded-pill px-4" id="btnBackToReview"
                            onclick="hideCancellation()">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </button>
                        <button type="button" class="btn btn-danger fw-semibold rounded-pill px-4"
                            onclick="submitReview('cancelled')">
                            <i class="bi bi-trash me-1"></i>Confirm Cancellation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showCancellation() {
        document.getElementById('cancellationSection').classList.remove('d-none');
        document.getElementById('actionButtons').classList.add('d-none');
        const confirmBox = document.getElementById('confirmCancelButton');
        confirmBox.classList.remove('d-none');
        confirmBox.classList.add('d-flex');
        document.getElementById('reviewReason').required = true;
    }

    function hideCancellation() {
        document.getElementById('cancellationSection').classList.add('d-none');
        document.getElementById('actionButtons').classList.remove('d-none');
        const confirmBox = document.getElementById('confirmCancelButton');
        confirmBox.classList.add('d-none');
        confirmBox.classList.remove('d-flex');
        document.getElementById('reviewReason').required = false;
        document.getElementById('reviewReason').value = '';
    }

    /**
     * The same modal serves two jobs: reviewing a pending order (approve or
     * reject), and cancelling one that is already in production. In cancel
     * mode there is nothing to approve, so it opens straight on the reason
     * step with no way back to the approve button.
     */
    function setReviewMode(mode) {
        const cancelOnly = mode === 'cancel';

        document.getElementById('reviewModalTitle').textContent = cancelOnly ? 'Cancel Order' : 'Review Order';
        document.getElementById('btnBackToReview').classList.toggle('d-none', cancelOnly);

        if (cancelOnly) {
            showCancellation();
        } else {
            hideCancellation();
        }
    }

    function submitReview(status) {
        document.getElementById('reviewStatus').value = status;
        document.getElementById('reviewOrderForm').submit();
    }

    function submitReviewWithLoading(status) {
        document.getElementById('reviewStatus').value = status;

        const btn = document.getElementById('btnApproveOrder');
        const spinner = btn.querySelector('.spinner-border');
        const text = btn.querySelector('.btn-text');

        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Processing...';

        document.getElementById('reviewOrderForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const reviewModal = document.getElementById('reviewOrderModal');
        if (reviewModal) {
            reviewModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('cancellationSection').classList.add('d-none');
                document.getElementById('actionButtons').classList.remove('d-none');
                const confirmBox = document.getElementById('confirmCancelButton');
                confirmBox.classList.add('d-none');
                confirmBox.classList.remove('d-flex');
                document.getElementById('reviewReason').value = '';
                document.getElementById('reviewReason').required = false;

                const btn = document.getElementById('btnApproveOrder');
                if (btn) {
                    btn.disabled = false;
                    btn.querySelector('.spinner-border').classList.add('d-none');
                    btn.querySelector('.btn-text').innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve Order';
                }

                // The next order reviewed is a different design, so the
                // enlarged preview must not still be showing the last one.
                closeReviewDesignPreview();
            });
        }

        const previewClose = document.getElementById('reviewDesignPreviewClose');
        if (previewClose) previewClose.addEventListener('click', closeReviewDesignPreview);

        // Delegated: the rows this fires from are rebuilt every time the modal
        // opens, so binding per-thumbnail would have to be redone each time.
        const itemsBody = document.getElementById('reviewItemsBody');
        if (itemsBody) {
            itemsBody.addEventListener('click', function (event) {
                const thumb = event.target.closest('.review-item-thumb');
                if (!thumb) return;

                // The design is looked up rather than read off an attribute: a
                // recipe is nested JSON, and round-tripping it through markup
                // is a quoting problem with nothing to gain.
                const entry = (window.reviewDesignsByIndex || {})[thumb.dataset.itemIndex];

                openReviewDesignPreview(
                    thumb.dataset.fullImage,
                    thumb.dataset.itemLabel,
                    entry ? entry.design : null,
                    entry ? entry.productName : null
                );
            });
        }
    });

    /**
     * Open the enlarged preview.
     *
     * `design` is the order line's custom design, or null for a plain item.
     * With one, the model is built so the reviewer can turn it round; without,
     * the product photo is all there is to show.
     */
    function openReviewDesignPreview(src, label, design, productName) {
        const panel = document.getElementById('reviewDesignPreview');
        if (!panel || (!src && !design)) return;

        const image = document.getElementById('reviewDesignPreviewImage');
        const loader = document.getElementById('reviewDesignPreviewLoader');
        const hint = document.getElementById('reviewDesignPreviewHint');

        document.getElementById('reviewDesignPreviewTitle').textContent = label || 'Design';
        image.src = src || '';
        image.hidden = false;
        panel.hidden = false;
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // A plain item has no recipe to build a model from, and the 3D engine
        // may not have loaded at all. Either way the snapshot stands in.
        if (!design || typeof init !== 'function' || typeof loadDesignRecipePreview !== 'function') {
            loader.hidden = true;
            hint.hidden = true;
            return;
        }

        loader.hidden = false;
        hint.hidden = false;

        // Let the panel lay out before measuring the container, or the canvas
        // is sized against a box that has not been given its height yet.
        setTimeout(() => buildReviewDesignScene(design, productName), 60);
    }

    function buildReviewDesignScene(design, productName) {
        const image = document.getElementById('reviewDesignPreviewImage');
        const loader = document.getElementById('reviewDesignPreviewLoader');

        try {
            disposeReviewDesignScene();

            let recipe = design.recipe;
            if (typeof recipe === 'string') {
                try { recipe = JSON.parse(recipe); } catch (e) { recipe = {}; }
            }
            recipe = recipe || {};

            // Same shape resolution the design popout on this page uses. Polo
            // is checked before any shirt match, because a polo has its own
            // model and would otherwise fall through to the t-shirt.
            const name = (productName || '').toLowerCase();
            let baseShape = 't-shirt';
            if (name.includes('polo')) baseShape = 'polo';
            else if (name.includes('mug')) baseShape = 'mug';
            else if (name.includes('umbrella')) baseShape = 'umbrella';
            else if (name.includes('bag')) baseShape = 'bag';
            else if (name.includes('shorts')) baseShape = 'shorts';
            else if (recipe.base_style) baseShape = recipe.base_style;

            // Merge rather than replace: a wholesale assignment drops the
            // texture catalogue and every previewed design renders blank white.
            window.CustomizerConfig = Object.assign(window.CustomizerConfig || {}, {
                initialShape: baseShape
            });

            init('reviewDesignViewer');

            setTimeout(() => {
                loadDesignRecipePreview(recipe);
                loader.hidden = true;
                // Uncover the model now there is something worth seeing.
                image.hidden = true;
            }, 800);
        } catch (err) {
            console.error('Review 3D preview failed to initialize:', err);
            loader.hidden = true;
            document.getElementById('reviewDesignPreviewHint').hidden = true;
            // The snapshot is already covering the empty stage, so there is
            // nothing to restore — just stop pretending a model is coming.
            image.hidden = false;
        }
    }

    /**
     * Tear the scene down. There is one global renderer shared with the design
     * popout on this page, so leaving ours running would have two modals
     * fighting over it — and a WebGL context leaks until it is disposed.
     */
    function disposeReviewDesignScene() {
        const container = document.getElementById('reviewDesignViewer');
        const canvas = container ? container.querySelector('canvas') : null;

        if (canvas) canvas.remove();

        if (typeof renderer !== 'undefined' && renderer) {
            renderer.dispose();
            renderer = null;
        }
    }

    function closeReviewDesignPreview() {
        const panel = document.getElementById('reviewDesignPreview');
        if (!panel) return;

        disposeReviewDesignScene();

        panel.hidden = true;
        document.getElementById('reviewDesignPreviewLoader').hidden = true;
        document.getElementById('reviewDesignPreviewHint').hidden = true;
        // Dropping the src releases the snapshot, which is a full-size data URI
        // and not something to keep decoded between reviews.
        const image = document.getElementById('reviewDesignPreviewImage');
        image.removeAttribute('src');
        image.hidden = false;
    }
</script>

<style>
    .review-design-preview[hidden] { display: none; }

    /* Tall enough to judge ink coverage on, capped so the modal still scrolls
       normally on a laptop. */
    /* The stage owns the height. Every layer inside is absolutely positioned
       so they overlap instead of queueing up, which is what made the box grow
       to fit all three at once. Tall enough to turn a model round in without
       the modal growing a second scrollbar. */
    .review-design-stage {
        position: relative;
        height: 340px;
        overflow: hidden;
    }

    .review-design-stage > #reviewDesignViewer {
        position: absolute;
        inset: 0;
    }

    #reviewDesignViewer canvas { display: block; width: 100% !important; height: 100% !important; cursor: grab; }
    #reviewDesignViewer canvas:active { cursor: grabbing; }

    /* The snapshot covers the model while it builds, so it sits above the
       canvas and is removed once there is something better to look at. */
    .review-design-stage > #reviewDesignPreviewImage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 100%;
        max-height: 100%;
        border-radius: 6px;
    }

    .review-design-stage > #reviewDesignPreviewLoader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        /* Above the snapshot it is spinning over. */
        z-index: 2;
    }

    .review-design-stage > [hidden] { display: none !important; }

    @media (max-width: 575.98px) {
        .review-design-stage { height: 240px; }
    }

    /* The thumbnail is the affordance, so it has to look like one. */
    #reviewItemsBody .review-item-thumb { cursor: zoom-in; position: relative; }
    #reviewItemsBody .review-item-thumb:hover { border-color: #0e2e45 !important; }
    #reviewItemsBody .review-item-thumb::after {
        content: '\F2FE';
        font-family: 'bootstrap-icons';
        position: absolute;
        right: -5px;
        bottom: -5px;
        width: 17px;
        height: 17px;
        font-size: 9px;
        line-height: 17px;
        text-align: center;
        border-radius: 50%;
        background: #0e2e45;
        color: #fff;
        opacity: 0;
        transition: opacity .15s;
    }
    #reviewItemsBody .review-item-thumb:hover::after { opacity: 1; }


</style>

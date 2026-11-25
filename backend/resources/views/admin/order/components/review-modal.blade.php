<div class="modal fade order-modal" id="reviewOrderModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down modal-lg">
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
                <div class="d-flex flex-wrap column-gap-3 row-gap-1 text-white-50 small mt-2">
                    <span>Customer: <span id="reviewCustomerName" class="text-white fw-bold"></span></span>
                    <span>Ordered for: <span id="reviewOrderedFor" class="text-white fw-bold"></span></span>
                    <span>Paid through: <span id="reviewChannel" class="text-white fw-bold"></span></span>
                    <span>Updates via: <span id="reviewUpdates" class="text-white fw-bold"></span></span>
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

                    {{-- The panel: the cost the system works out is an estimate
                         the shop confirms. This is where it does. Each line
                         arrives at the unit price the customer saw, and what is
                         here when Approve is pressed becomes the confirmed price:
                         on the slip, in the sales figures, and told to the
                         customer. Hidden when cancelling, with nothing to price. --}}
                    <div id="reviewPriceSection">
                        <h6 class="order-section-title">
                            <i class="bi bi-tags me-2"></i>Price Confirmation
                        </h6>
                        <p class="small text-muted mb-2">
                            The customer saw the system's estimate. Approving confirms it, so change a unit price first
                            if the job will cost more or less. The customer is told the confirmed total, and it is the
                            amount on the transaction slip.
                        </p>
                        <div class="table-responsive border rounded-3 mb-3 overflow-hidden">
                            <table class="table align-middle mb-0 modal-table">
                                <thead>
                                    <tr class="bg-primary bg-opacity-10">
                                        <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Item</th>
                                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">Qty</th>
                                        <th class="text-end py-2 text-primary small text-uppercase fw-bold border-0">Estimate (each)</th>
                                        <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0"
                                            style="width: 10rem;">Confirmed (each)</th>
                                    </tr>
                                </thead>
                                <tbody id="reviewPriceBody">
                                    <!-- Populated by fillPriceConfirmation() -->
                                </tbody>
                                <tfoot>
                                    <tr class="border-top">
                                        <td colspan="2" class="ps-3 py-2 small text-muted">
                                            Estimate <span id="reviewEstimatedTotal" class="fw-semibold text-dark"></span>
                                        </td>
                                        <td colspan="2" class="text-end pe-3 py-2">
                                            <span class="small text-muted me-1">Confirmed total</span>
                                            <span id="reviewConfirmedTotal" class="fw-bold text-primary"></span>
                                            <div id="reviewPriceChanged" class="small fw-semibold d-none" style="color: #997404;">
                                                Changed from the estimate
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- A tailored line's thumbnail opens the Design Inspection
                         popup the View modal uses — the model rebuilt from its
                         recipe, with the recipe and the charges floating over
                         the scene — stacked above this modal, so the reviewer
                         can turn the design round before judging the materials
                         panel below. The popup and the code that drives it live
                         with the View modal and the page script; the thumbnails
                         here only point at them. --}}

                    @include('partials.order-materials-panel', ['panelId' => 'reviewMaterialsPanel'])

                    <!-- Cancellation Reason (Hidden by default) -->
                    {{-- Rejecting or cancelling always needs a reason: the
                         customer reads it on their order. submitReview()
                         checks it before sending, because the form goes
                         out by script and the browser's own `required`
                         check never runs for that. --}}
                    <div id="cancellationSection" class="d-none">
                        <h6 class="order-section-title d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Cancellation Reason
                            <span class="order-required-pill ms-2">Required</span>
                        </h6>
                        <div class="alert alert-warning border-0 d-flex align-items-center mb-3 rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div class="small fw-bold">You are about to cancel this order. Stock will be restored.</div>
                        </div>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @php
                                $commonReasons = [
                                    'Insufficient stock',
                                    'Invalid receipt number',
                                    'Customer request',
                                    'Unavailable at this time',
                                    'Incomplete order details'
                                ];
                            @endphp
                            @foreach($commonReasons as $reason)
                                <button type="button" class="order-reason-chip" data-reason-for="reviewReason"
                                    data-reason="{{ $reason }}">
                                    {{ $reason }}
                                </button>
                            @endforeach
                        </div>
                        <textarea name="reason" id="reviewReason" class="form-control order-field-input" rows="3"
                            maxlength="1000" aria-describedby="reviewReasonHelp" data-required-reason
                            placeholder="e.g., Insufficient stock for item X..."></textarea>
                        <div class="invalid-feedback">Give a reason before cancelling. The customer reads it on their order.</div>
                        <div class="form-text" id="reviewReasonHelp">The customer sees this reason on their order.</div>
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
        document.getElementById('reviewReason').classList.remove('is-invalid');
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
        document.getElementById('reviewPriceSection').classList.toggle('d-none', cancelOnly);

        if (cancelOnly) {
            showCancellation();
        } else {
            hideCancellation();
        }
    }

    function submitReview(status) {
        // No reason, no cancellation — and the modal stays open to take one.
        if (status === 'cancelled' && !window.requireReason(document.getElementById('reviewReason'))) {
            return;
        }

        document.getElementById('reviewStatus').value = status;
        document.getElementById('reviewOrderForm').submit();
    }

    function peso(value) {
        return '₱' + Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * One row per order line in the Price Confirmation table, at the unit
     * price the customer saw. Built as nodes, not markup: product names are
     * typed in by staff.
     */
    function fillPriceConfirmation(items) {
        const body = document.getElementById('reviewPriceBody');
        let estimateTotal = 0;

        body.replaceChildren.apply(body, items.map(function (item) {
            const product = item.product || {};
            const variant = item.product_variant || item.productVariant || null;
            const quantity = Number(item.quantity) || 0;
            const estimate = Number(item.estimated_price ?? item.price) || 0;
            estimateTotal += estimate * quantity;

            const cell = function (className, text) {
                const td = document.createElement('td');
                td.className = className;
                if (text !== undefined) td.textContent = text;
                return td;
            };

            const label = (product.name || 'Item')
                + (variant && variant.label ? ' · ' + variant.label : '')
                + (item.custom_design_id ? ' (customized)' : '');

            const input = document.createElement('input');
            input.type = 'number';
            input.min = '0';
            input.step = '0.01';
            input.required = true;
            input.name = 'confirmed_prices[' + item.order_item_id + ']';
            input.value = Number(item.price).toFixed(2);
            input.className = 'form-control form-control-sm text-end review-confirmed-price';
            input.dataset.quantity = String(quantity);
            input.dataset.estimate = String(estimate);
            input.setAttribute('aria-label', 'Confirmed unit price for ' + label);

            const priceCell = cell('pe-3 py-2');
            priceCell.append(input);

            const row = document.createElement('tr');
            row.append(
                cell('ps-3 py-2 small fw-semibold text-dark', label),
                cell('text-center py-2 small fw-bold', String(quantity)),
                cell('text-end py-2 small text-muted', peso(estimate)),
                priceCell
            );
            return row;
        }));

        document.getElementById('reviewEstimatedTotal').textContent = peso(estimateTotal);
        updateConfirmedTotal();
    }

    function updateConfirmedTotal() {
        let total = 0;
        let changed = false;

        document.querySelectorAll('#reviewPriceBody .review-confirmed-price').forEach(function (input) {
            const unit = parseFloat(input.value);
            const price = Number.isFinite(unit) ? unit : 0;
            const differs = Math.abs(price - Number(input.dataset.estimate)) >= 0.005;

            total += price * Number(input.dataset.quantity);
            changed = changed || differs;
            input.classList.toggle('is-changed', differs);
            input.classList.remove('is-invalid');
        });

        document.getElementById('reviewConfirmedTotal').textContent = peso(total);
        document.getElementById('reviewPriceChanged').classList.toggle('d-none', !changed);
    }

    /** Every confirmed price a number of zero or more, or approval waits. */
    function confirmedPricesValid() {
        let valid = true;

        document.querySelectorAll('#reviewPriceBody .review-confirmed-price').forEach(function (input) {
            const unit = parseFloat(input.value);
            const ok = input.value.trim() !== '' && Number.isFinite(unit) && unit >= 0;
            input.classList.toggle('is-invalid', !ok);
            if (!ok && valid) input.focus();
            valid = valid && ok;
        });

        return valid;
    }

    function submitReviewWithLoading(status) {
        if (status === 'approved' && !confirmedPricesValid()) {
            return;
        }

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
        // This form only ever goes out through submitReview() and
        // submitReviewWithLoading(). With a single editable material line,
        // Enter in that box would otherwise submit it natively — as an
        // approval, since that is the status field's default — even halfway
        // through rejecting.
        document.getElementById('reviewOrderForm')?.addEventListener('submit', function (event) {
            event.preventDefault();
        });

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
                document.getElementById('reviewReason').classList.remove('is-invalid');

                const btn = document.getElementById('btnApproveOrder');
                if (btn) {
                    btn.disabled = false;
                    btn.querySelector('.spinner-border').classList.add('d-none');
                    btn.querySelector('.btn-text').innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve Order';
                }
            });
        }

        document.getElementById('reviewPriceBody')?.addEventListener('input', updateConfirmedTotal);

        // Delegated: the rows this fires from are rebuilt every time the modal
        // opens, so binding per-thumbnail would have to be redone each time.
        const itemsBody = document.getElementById('reviewItemsBody');
        if (itemsBody) {
            const inspect = function (thumb) {
                // The design is looked up rather than read off an attribute: a
                // recipe is nested JSON, and round-tripping it through markup
                // is a quoting problem with nothing to gain.
                const design = (window.reviewDesignsByIndex || {})[thumb.dataset.itemIndex];
                if (design && typeof openDesignInspection === 'function') openDesignInspection(design);
            };

            itemsBody.addEventListener('click', function (event) {
                const thumb = event.target.closest('.review-item-thumb');
                if (thumb) inspect(thumb);
            });

            // The thumbnail is announced as a button, so it has to open from
            // the keyboard as well as the mouse.
            itemsBody.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                const thumb = event.target.closest('.review-item-thumb');
                if (!thumb) return;
                event.preventDefault();
                inspect(thumb);
            });
        }
    });
</script>

<style>
    /* The body scrolls under a fixed header and footer (modal-dialog-scrollable),
       so Approve stays in reach however long the materials panel gets. Bootstrap
       expects .modal-body directly inside .modal-content; here a <form> sits
       between them, so it has to pass the flex constraint down or the body just
       grows and the whole page scrolls instead. */
    #reviewOrderModal .modal-content > form { display: flex; flex-direction: column; min-height: 0; flex: 1 1 auto; }
    #reviewOrderModal .modal-content > form > .modal-body { overflow-y: auto; min-height: 0; }

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

    /* A re-priced line stands out from the ones left at the estimate. */
    #reviewPriceBody .review-confirmed-price { max-width: 9rem; margin-left: auto; }
    #reviewPriceBody .review-confirmed-price.is-changed {
        border-color: #ffc508;
        background-color: #fff8e0;
        font-weight: 600;
    }
</style>

@include('admin.order.components.required-reason')

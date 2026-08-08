{{--
    Record Usage — the replacement for typing a new stock figure by hand.

    Caller passes $routePrefix ('admin' | 'staff'). Admin additionally gets the
    Stock correction reason, which reconciles against a physical count.
--}}
@php
    use App\Enums\StockMovementReason;

    $canCorrect = $routePrefix === 'admin';
    $reasons = StockMovementReason::selectable($canCorrect);

    // Handed to the script below so the form can relabel itself and preview the
    // resulting stock without a round trip.
    $reasonMeta = collect($reasons)->mapWithKeys(fn (StockMovementReason $r) => [$r->value => [
        'hint' => $r->hint(),
        'reduces' => $r->reducesStock(),
        'correction' => $r === StockMovementReason::Correction,
    ]])->all();
@endphp

<div class="modal fade material-modal" id="recordUsageModal" tabindex="-1" aria-labelledby="recordUsageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="material-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-eyebrow">{{ ucfirst($routePrefix) }}</span>
                        <span class="material-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="recordUsageModalLabel">Record Usage</h5>
                    </div>
                    <button type="button" class="material-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form id="recordUsageForm" method="POST">
                    @csrf

                    <div class="p-4">
                        <!-- Which material, and what's on the shelf right now -->
                        <div class="usage-material-strip mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="usage-material-icon flex-shrink-0">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <p class="text-muted mb-0 text-uppercase fw-semibold"
                                        style="letter-spacing: 0.06em; font-size: 0.62rem;">Material</p>
                                    <h6 class="fw-bold text-dark mb-0 text-truncate" id="usageMaterialName">—</h6>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <p class="text-muted mb-0 text-uppercase fw-semibold"
                                        style="letter-spacing: 0.06em; font-size: 0.62rem;">In Stock</p>
                                    <h6 class="fw-bold mb-0" id="usageCurrentStock">—</h6>
                                </div>
                            </div>
                        </div>

                        <h6 class="material-section-title">
                            <i class="bi bi-clipboard-check me-2"></i>Movement
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="usageReason">
                                    Reason <span class="text-danger">*</span>
                                </label>
                                <select id="usageReason" name="reason" class="form-select material-field-input" required>
                                    @foreach($reasons as $reason)
                                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                                    @endforeach
                                </select>
                                <p class="text-muted mb-0 mt-2 d-flex align-items-start gap-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle mt-1 flex-shrink-0"></i>
                                    <span id="usageReasonHint">{{ $reasons[0]->hint() }}</span>
                                </p>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="usageQuantity">
                                    <span id="usageQuantityLabel">Quantity Used</span> <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" id="usageQuantity" name="quantity"
                                        class="form-control material-field-input fw-bold text-dark" required>
                                    <span class="input-group-text material-input-addon usage-unit-addon" id="usageUnitAddon">unit</span>
                                </div>
                                <div class="usage-preview mt-2" id="usagePreview"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase" for="usageNote">
                                    Note <span class="text-muted fw-normal text-lowercase">(optional)</span>
                                </label>
                                <textarea id="usageNote" name="note" rows="2" maxlength="500"
                                    class="form-control material-field-input"
                                    placeholder="e.g. Batch of 40 ID laces for the College of Education"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="material-modal-footer">
                        <button type="button" class="btn material-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn material-btn-save rounded-pill px-4" id="usageSubmitBtn">
                            <i class="bi bi-check2 me-1"></i>Record Movement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var REASONS = @json($reasonMeta);
        var ROUTE_PREFIX = @json($routePrefix);

        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('recordUsageModal');
            if (!modal) return;

            var form = document.getElementById('recordUsageForm');
            var reasonEl = document.getElementById('usageReason');
            var qtyEl = document.getElementById('usageQuantity');
            var hintEl = document.getElementById('usageReasonHint');
            var labelEl = document.getElementById('usageQuantityLabel');
            var previewEl = document.getElementById('usagePreview');
            var unitEl = document.getElementById('usageUnitAddon');
            var stockEl = document.getElementById('usageCurrentStock');
            var nameEl = document.getElementById('usageMaterialName');
            var submitEl = document.getElementById('usageSubmitBtn');

            var current = 0;
            var unit = '';

            function trim(n) {
                return parseFloat(n.toFixed(2)).toString();
            }

            // Mirrors the service: consumed/damaged/sponsored come off stock,
            // display only tags, and a correction sets the counted total.
            function render() {
                var meta = REASONS[reasonEl.value];
                if (!meta) return;

                hintEl.textContent = meta.hint;
                labelEl.textContent = meta.correction ? 'Counted Quantity' : 'Quantity';

                var qty = parseFloat(qtyEl.value);
                var valid = !isNaN(qty) && qty >= 0;
                var blocked = false;
                var message = '';
                var tone = 'muted';

                if (!valid || (qty === 0 && !meta.correction)) {
                    previewEl.textContent = '';
                    submitEl.disabled = false;
                    return;
                }

                if (meta.correction) {
                    var delta = qty - current;
                    if (Math.abs(delta) < 0.005) {
                        message = 'That already matches the recorded stock — nothing to correct.';
                        tone = 'warn';
                        blocked = true;
                    } else {
                        message = 'Stock goes from ' + trim(current) + ' to ' + trim(qty) + ' ' + unit
                            + ' (' + (delta > 0 ? '+' : '−') + trim(Math.abs(delta)) + ').';
                        tone = 'ok';
                    }
                } else if (qty > current) {
                    message = 'Only ' + trim(current) + ' ' + unit + ' in stock.';
                    tone = 'warn';
                    blocked = true;
                } else if (meta.reduces) {
                    message = 'Stock after this: ' + trim(current - qty) + ' ' + unit + '.';
                    tone = 'ok';
                } else {
                    message = 'Stock stays at ' + trim(current) + ' ' + unit + ' — display units are still owned.';
                    tone = 'muted';
                }

                previewEl.textContent = message;
                previewEl.className = 'usage-preview mt-2 usage-preview-' + tone;
                submitEl.disabled = blocked;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;

                current = parseFloat(button.getAttribute('data-stock')) || 0;
                unit = button.getAttribute('data-unit') || '';

                form.action = '/' + ROUTE_PREFIX + '/raw-materials/' + button.getAttribute('data-id') + '/usage';
                form.reset();

                nameEl.textContent = button.getAttribute('data-name');
                stockEl.textContent = trim(current) + ' ' + unit;
                stockEl.className = 'fw-bold mb-0 ' + (current <= 0 ? 'text-danger' : 'text-dark');
                unitEl.textContent = unit;

                reasonEl.selectedIndex = 0;
                previewEl.textContent = '';
                submitEl.disabled = false;
                render();
            });

            modal.addEventListener('shown.bs.modal', function () {
                qtyEl.focus();
            });

            reasonEl.addEventListener('change', render);
            qtyEl.addEventListener('input', render);
        });
    })();
</script>

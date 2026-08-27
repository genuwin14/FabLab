{{-- Where the customer hands back the number procurement issued. Holding one
     means the request was approved, which is the only thing that lets FabLab
     accept the order.

     Built on the same shape as the Cancel Order modal beside it — dark header,
     icon tile, white body, pill footer — in gold rather than red, since this
     one is the way forward rather than a warning. --}}
<div class="modal fade customer-pr-modal" id="prNumberModal" tabindex="-1" aria-labelledby="prNumberModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered customer-pr-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="customer-pr-modal-header">
                <div class="customer-pr-modal-icon">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="prNumberModalLabel">Enter Your PR Number</h5>
                <p class="text-white-50 small mb-0">
                    Order <span id="prOrderNumber" class="fw-bold font-monospace text-white"></span>
                </p>
            </div>

            <form method="POST" id="prNumberForm" class="m-0">
                @csrf
                <div class="modal-body p-4 customer-pr-modal-body">
                    <div class="customer-pr-modal-note mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        File your Purchase Request with
                        <strong>{{ config('fablab.procurement_email') }}</strong>.
                        Once procurement approves it, enter the number here to release your order for review.
                        <span id="prDeadlineNote"></span>
                    </div>

                    <label for="prNumberInput" class="form-label small fw-semibold text-muted">PR Number</label>
                    <input type="text" class="form-control customer-pr-field" id="prNumberInput" name="pr_number"
                        maxlength="100" required autocomplete="off" placeholder="e.g. PR-2026-0142">
                </div>

                <div class="customer-pr-modal-footer">
                    <button type="button" class="btn fw-semibold rounded-pill px-4 customer-pr-cancel-btn"
                        data-bs-dismiss="modal">
                        Not Yet
                    </button>
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 customer-pr-submit-btn">
                        <i class="bi bi-check-lg me-2"></i>Submit PR Number
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* ============================================
       Customer PR Number Modal (admin theme)
       ============================================ */
    .customer-pr-modal-dialog { max-width: 440px; }
    .customer-pr-modal .modal-content { border-radius: 18px; }

    .customer-pr-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }
    .customer-pr-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.4), transparent);
    }
    .customer-pr-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        border-radius: 16px;
        background: rgba(255, 197, 8, 0.15);
        border: 1px solid rgba(255, 197, 8, 0.3);
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .customer-pr-modal-body { background-color: #fff; }

    .customer-pr-modal-note {
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-left: 3px solid #ffc508;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.82rem;
        line-height: 1.45;
        color: #6c757d;
    }
    .customer-pr-modal-note strong { color: #0e2e45; }

    .customer-pr-field {
        background-color: #f8f9fa;
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        transition: all 0.2s ease;
    }
    .customer-pr-field:focus {
        background-color: #fff;
        border-color: #ffc508;
        box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12);
    }

    .customer-pr-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .customer-pr-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .customer-pr-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .customer-pr-submit-btn {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        transition: all 0.2s ease;
    }
    .customer-pr-submit-btn:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }

    /* Mobile: the two pill buttons don't fit one phone row — stack
       them full-width (ResponsiveMobileNote.md §6c). */
    @media (max-width: 991.98px) {
        .customer-pr-modal-dialog { margin: 0.5rem; }
        .customer-pr-modal-footer {
            flex-direction: column;
            align-items: stretch;
        }
        .customer-pr-modal-footer > .btn { width: 100%; }
    }
</style>

<script>
    document.getElementById('prNumberModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const deadline = trigger.getAttribute('data-deadline');

        this.querySelector('#prNumberForm').action = trigger.getAttribute('data-url');
        this.querySelector('#prOrderNumber').textContent = '#' + trigger.getAttribute('data-order-number');
        this.querySelector('#prDeadlineNote').textContent = deadline
            ? 'Your window closes on ' + deadline + '.'
            : '';
        this.querySelector('#prNumberInput').value = '';
    });
</script>

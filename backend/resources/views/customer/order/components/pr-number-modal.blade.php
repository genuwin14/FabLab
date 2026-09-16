{{-- Where the customer hands back the number procurement issued. Holding one
     means the request was approved, which is the only thing that lets FabLab
     accept the order.

     Built on the shop's Quick View theme (.qv-modal) like the rest of the
     customer dialogs — light card, floating close, eyebrow over the title,
     pill actions — with the gold accent that marks this as the way forward
     rather than a warning. --}}
<div class="modal fade qv-modal" id="prNumberModal" tabindex="-1" aria-labelledby="prNumberModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <form method="POST" id="prNumberForm" class="m-0">
                @csrf
                <div class="modal-body p-4 p-lg-5">
                    <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <div class="text-center">
                        <span class="qv-icon qv-icon-accent mb-4">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </span>

                        <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">
                            Purchase Request
                        </span>
                        <h5 id="prNumberModalLabel" class="qv-title mb-2">Enter Your PR Number</h5>
                        <div class="small text-muted mb-4">
                            Order <span id="prOrderNumber" class="fw-bold font-monospace text-dark"></span>
                        </div>
                    </div>

                    <div class="qv-tile text-start mb-4"
                        style="border-left: 3px solid #ffc508; background: #f8f9fa;">
                        <p class="mb-0 small text-muted" style="line-height: 1.45;">
                            <i class="bi bi-info-circle me-1"></i>
                            File your Purchase Request with
                            <strong class="text-primary">{{ config('fablab.procurement_email') }}</strong>.
                            Once procurement approves it, enter the number here to release your order for review.
                            <span id="prDeadlineNote"></span>
                        </p>
                    </div>

                    <h6 class="qv-label">PR Number</h6>
                    <input type="text" class="form-control customer-pr-field mb-4" id="prNumberInput" name="pr_number"
                        maxlength="100" required autocomplete="off" placeholder="e.g. PR-2026-0142">

                    <div class="qv-actions">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-check-lg me-2"></i> Submit PR Number
                        </button>
                        <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">
                            Not Yet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* The one field this modal owns. Everything else comes from .qv-modal. */
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

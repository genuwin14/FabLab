{{-- Where the customer hands back the number procurement issued. Holding one
     means the request was approved, which is the only thing that lets FabLab
     accept the order. --}}
<div class="modal fade" id="prNumberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" id="prNumberForm">
                @csrf
                <div class="modal-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 68px; height: 68px;">
                            <i class="bi bi-file-earmark-text text-warning fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Enter Your PR Number</h5>
                        <p class="text-muted small mb-0">
                            Order <span class="fw-semibold" id="prOrderNumber"></span>
                        </p>
                    </div>

                    <div class="alert alert-light border-0 rounded-3 small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        File your Purchase Request with
                        <strong>{{ config('fablab.procurement_email') }}</strong>.
                        Once procurement approves it, enter the number here to release your order for review.
                        <span id="prDeadlineNote"></span>
                    </div>

                    <div class="mb-4">
                        <label for="prNumberInput" class="form-label fw-semibold small">PR Number</label>
                        <input type="text" class="form-control form-control-lg rounded-3" id="prNumberInput"
                            name="pr_number" maxlength="100" required autocomplete="off"
                            placeholder="e.g. PR-2026-0142">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                            Submit PR Number
                        </button>
                        <button type="button" class="btn btn-light rounded-pill py-3 fw-bold text-muted"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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

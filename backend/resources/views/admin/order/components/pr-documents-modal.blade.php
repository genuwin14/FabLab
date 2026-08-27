{{-- Procurement paperwork for a Purchase Request order. Uploading is what
     moves the order, which is why it sits with the admin next to review
     rather than with staff. --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" id="uploadDocForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 68px; height: 68px;">
                            <i class="bi bi-upload text-warning fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="uploadDocTitle">Upload Document</h5>
                        <p class="text-muted small mb-0">
                            Order <span class="fw-semibold" id="uploadDocOrderNumber"></span>
                        </p>
                    </div>

                    <div class="alert alert-light border-0 rounded-3 small mb-4" id="uploadDocNote"></div>

                    <div class="mb-4">
                        <label for="uploadDocInput" class="form-label fw-semibold small">File</label>
                        <input type="file" class="form-control rounded-3" id="uploadDocInput" name="document"
                            accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">PDF or image, up to 5 MB.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold shadow-sm">
                            Upload &amp; Release
                        </button>
                        <button type="button" class="btn btn-light rounded-pill py-3 fw-bold text-muted"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="closePrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" id="closePrForm">
                @csrf
                <div class="modal-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 68px; height: 68px;">
                            <i class="bi bi-x-circle text-danger fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Close Held Order</h5>
                        <p class="text-muted small mb-0">
                            Order <span class="fw-semibold" id="closePrOrderNumber"></span>
                        </p>
                    </div>

                    <div class="alert alert-light border-0 rounded-3 small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        This order is still waiting on its PR number. Closing it now returns the reserved stock —
                        the deadline would do the same on its own.
                    </div>

                    <div class="mb-4">
                        <label for="closePrReason" class="form-label fw-semibold small">Reason</label>
                        <textarea class="form-control rounded-3" id="closePrReason" name="reason" rows="3" required
                            placeholder="Tell the customer why this is being closed."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-3 fw-bold shadow-sm">
                            Close Order
                        </button>
                        <button type="button" class="btn btn-light rounded-pill py-3 fw-bold text-muted"
                            data-bs-dismiss="modal">Keep Waiting</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const DOCS = {
            noa: {
                title: 'Upload Notice of Award',
                note: 'The Notice of Award confirms the request was awarded. Uploading it starts production.',
            },
            po: {
                title: 'Upload Purchase Order',
                note: 'The Purchase Order is the buyer\'s commitment. Uploading it releases the order for delivery.',
            },
        };

        document.getElementById('uploadDocModal')?.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const doc = DOCS[trigger.getAttribute('data-doc-type')] || DOCS.noa;

            this.querySelector('#uploadDocForm').action = trigger.getAttribute('data-url');
            this.querySelector('#uploadDocTitle').textContent = doc.title;
            this.querySelector('#uploadDocNote').textContent = doc.note;
            this.querySelector('#uploadDocOrderNumber').textContent = '#' + trigger.getAttribute('data-order-number');
            this.querySelector('#uploadDocInput').value = '';
        });

        document.getElementById('closePrModal')?.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            this.querySelector('#closePrForm').action = trigger.getAttribute('data-url');
            this.querySelector('#closePrOrderNumber').textContent = '#' + trigger.getAttribute('data-order-number');
            this.querySelector('#closePrReason').value = '';
        });
    })();
</script>

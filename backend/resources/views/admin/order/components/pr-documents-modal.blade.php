{{-- Procurement paperwork for a Purchase Request order. Uploading is what
     moves the order, which is why it sits with the admin next to review
     rather than with staff.

     Both modals wear the shared .order-modal theme defined on the orders
     page, so they match Review and View rather than inventing a look. --}}

<!-- Upload NOA / PO -->
<div class="modal fade order-modal" id="uploadDocModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
                        <span class="order-eyebrow-divider">/</span>
                        {{-- The order number sits on the line below rather than
                             in the title: this dialog is narrower than Review,
                             and the two together wrapped onto a second line. --}}
                        <h5 class="modal-title fw-bold mb-0 text-white" id="uploadDocTitle">Upload Document</h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    <span id="uploadDocOrderNumber" class="fw-bold" style="color: #ffc508;"></span>
                    <span class="order-eyebrow-divider mx-1">·</span>
                    <span id="uploadDocSubtitle"></span>
                </div>
            </div>

            <form method="POST" id="uploadDocForm" enctype="multipart/form-data" class="m-0">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <h6 class="order-section-title">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>Procurement Document
                    </h6>

                    <div class="alert alert-warning border-0 d-flex align-items-start mb-3 rounded-3">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div class="small fw-semibold" id="uploadDocNote"></div>
                    </div>

                    <label for="uploadDocInput" class="form-label small fw-semibold text-muted">File</label>
                    <input type="file" class="form-control order-field-input" id="uploadDocInput" name="document"
                        accept=".pdf,.jpg,.jpeg,.png" required>
                    <div class="form-text">PDF or image, up to 5 MB.</div>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn order-btn-save rounded-pill px-4">
                        <i class="bi bi-upload me-1"></i>Upload &amp; Release
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close an order still waiting on its PR number -->
<div class="modal fade order-modal" id="closePrModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="order-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="order-eyebrow">Admin</span>
                        <span class="order-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white">Close Held Order</h5>
                    </div>
                    <button type="button" class="order-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="text-white-50 small mt-2">
                    <span id="closePrOrderNumber" class="fw-bold" style="color: #ffc508;"></span>
                    <span class="order-eyebrow-divider mx-1">·</span>
                    <span>Still waiting on its PR number</span>
                </div>
            </div>

            <form method="POST" id="closePrForm" class="m-0">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <h6 class="order-section-title">
                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Reason for Closing
                    </h6>

                    <div class="alert alert-warning border-0 d-flex align-items-start mb-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div class="small fw-bold">
                            Closing now returns the reserved stock. The deadline would do the same on its own.
                        </div>
                    </div>

                    @php
                        $closeReasons = [
                            'Customer withdrew the request',
                            'Procurement rejected the PR',
                            'No longer needed',
                            'Ordered in error',
                        ];
                    @endphp
                    <div class="mb-2 d-flex flex-wrap gap-1">
                        @foreach($closeReasons as $reason)
                            <button type="button" class="order-reason-chip"
                                onclick="document.getElementById('closePrReason').value = '{{ $reason }}'">
                                {{ $reason }}
                            </button>
                        @endforeach
                    </div>

                    <textarea name="reason" id="closePrReason" class="form-control order-field-input" rows="3"
                        placeholder="Tell the customer why this is being closed..." required></textarea>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="btn order-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Keep Waiting
                    </button>
                    <button type="submit" class="btn btn-danger fw-semibold rounded-pill px-4">
                        <i class="bi bi-x-lg me-1"></i>Close Order
                    </button>
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
                subtitle: 'Releases the order for production',
                note: 'The Notice of Award confirms the request was awarded. Uploading it starts production.',
            },
            po: {
                title: 'Upload Purchase Order',
                subtitle: 'Releases the order for delivery',
                note: 'The Purchase Order is the buyer\'s commitment. Uploading it releases the order for delivery.',
            },
        };

        document.getElementById('uploadDocModal')?.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const doc = DOCS[trigger.getAttribute('data-doc-type')] || DOCS.noa;

            this.querySelector('#uploadDocForm').action = trigger.getAttribute('data-url');
            this.querySelector('#uploadDocTitle').textContent = doc.title;
            this.querySelector('#uploadDocSubtitle').textContent = doc.subtitle;
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

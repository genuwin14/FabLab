<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div id="statusIconContainer" class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i id="statusIcon" class="bi" style="font-size: 32px;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2" id="statusModalTitle">Confirm Action</h5>
                <p class="text-muted mb-4" id="statusModalMessage">Are you sure you want to perform this action?</p>
                
                <form id="statusForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="status" id="statusInput">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn rounded-pill px-4 fw-bold" id="statusConfirmBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusModal = document.getElementById('statusModal');
        statusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const status = button.getAttribute('data-status');
            const route = button.getAttribute('data-route');
            const userName = button.getAttribute('data-user-name');
            const role = button.getAttribute('data-role');

            const form = document.getElementById('statusForm');
            const iconContainer = document.getElementById('statusIconContainer');
            const icon = document.getElementById('statusIcon');
            const title = document.getElementById('statusModalTitle');
            const message = document.getElementById('statusModalMessage');
            const confirmBtn = document.getElementById('statusConfirmBtn');
            const statusInput = document.getElementById('statusInput');

            // Set form action and input
            form.action = route;
            statusInput.value = status;

            // Configure UI based on status
            if (status === 'disabled') {
                // Disabling Action (Warning/Danger)
                iconContainer.className = 'rounded-circle d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger';
                icon.className = 'bi bi-exclamation-triangle-fill';
                title.textContent = `Disable ${role}?`;
                message.innerHTML = `Are you sure you want to disable <strong>${userName}</strong>? <br>They will no longer be able to log in.`;
                confirmBtn.className = 'btn btn-danger rounded-pill px-4 fw-bold';
                confirmBtn.textContent = 'Disable Account';
            } else {
                // Enabling Action (Success)
                iconContainer.className = 'rounded-circle d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success';
                icon.className = 'bi bi-check-circle-fill';
                title.textContent = `Enable ${role}?`;
                message.innerHTML = `Are you sure you want to enable <strong>${userName}</strong>? <br>They will regain access to the system.`;
                confirmBtn.className = 'btn btn-success rounded-pill px-4 fw-bold';
                confirmBtn.textContent = 'Enable Account';
            }
        });
    });
</script>
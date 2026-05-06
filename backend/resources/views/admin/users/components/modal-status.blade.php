<div class="modal fade user-status-modal" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered user-status-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="user-status-modal-header" id="statusModalHeader">
                <div class="user-status-modal-icon icon-disable" id="statusIconContainer">
                    <i id="statusIcon" class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="statusModalTitle">Confirm Action</h5>
                <p class="text-white-50 small mb-0" id="statusModalSubtitle">This change is reversible</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 user-status-modal-body">
                <p class="text-dark mb-0 text-center" id="statusModalMessage">
                    Are you sure you want to perform this action?
                </p>
            </div>

            <!-- Footer with actions -->
            <form id="statusForm" method="POST" action="" class="m-0">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                <div class="user-status-modal-footer">
                    <button type="button" class="btn fw-semibold rounded-pill px-4 user-status-cancel-btn"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn fw-semibold rounded-pill px-4" id="statusConfirmBtn">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusModal = document.getElementById('statusModal');
        statusModal.addEventListener('show.bs.modal', function (event) {
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
            const subtitle = document.getElementById('statusModalSubtitle');
            const message = document.getElementById('statusModalMessage');
            const confirmBtn = document.getElementById('statusConfirmBtn');
            const statusInput = document.getElementById('statusInput');
            const header = document.getElementById('statusModalHeader');

            form.action = route;
            statusInput.value = status;

            if (status === 'disabled') {
                iconContainer.className = 'user-status-modal-icon icon-disable';
                icon.className = 'bi bi-exclamation-triangle-fill';
                title.textContent = `Disable ${role}?`;
                subtitle.textContent = 'They will lose access immediately';
                message.innerHTML = `Are you sure you want to disable <strong class="text-dark">${userName}</strong>? They will no longer be able to log in.`;
                confirmBtn.className = 'btn fw-semibold rounded-pill px-4 user-status-confirm-btn-disable';
                confirmBtn.innerHTML = '<i class="bi bi-slash-circle me-2"></i>Disable Account';
                header.style.setProperty('--accent-line', 'rgba(220, 53, 69, 0.4)');
            } else {
                iconContainer.className = 'user-status-modal-icon icon-enable';
                icon.className = 'bi bi-check-circle-fill';
                title.textContent = `Enable ${role}?`;
                subtitle.textContent = 'They will regain access immediately';
                message.innerHTML = `Are you sure you want to enable <strong class="text-dark">${userName}</strong>? They will regain access to the system.`;
                confirmBtn.className = 'btn fw-semibold rounded-pill px-4 user-status-confirm-btn-enable';
                confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Enable Account';
                header.style.setProperty('--accent-line', 'rgba(25, 135, 84, 0.4)');
            }
        });
    });
</script>

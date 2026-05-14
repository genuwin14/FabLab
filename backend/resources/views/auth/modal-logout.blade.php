<!-- Logout Confirmation Modal -->
<div class="modal fade logout-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered logout-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="logout-modal-header">
                <div class="logout-modal-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="logoutModalLabel">Sign Out</h5>
                <p class="text-white-50 small mb-0">End your current session</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 logout-modal-body">
                <p class="text-dark mb-0 text-center">
                    Are you sure you want to sign out? You'll need to log in again to continue.
                </p>
            </div>

            <!-- Loading State (hidden by default) -->
            <div class="logout-loading-state d-none p-4 text-center">
                <div class="logout-spinner mb-3">
                    <div class="spinner-border" role="status" style="color: #ffc508;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <h6 class="fw-bold text-dark mb-1">Logging out...</h6>
                <p class="text-muted small mb-0">Securing your session, please wait.</p>
            </div>

            <!-- Footer with actions -->
            <div class="logout-modal-footer">
                <button type="button" class="btn btn-light fw-semibold rounded-pill px-4 logout-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <form action="{{ route('logout') }}" method="POST" class="m-0 logout-form">
                    @csrf
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 logout-confirm-btn">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .logout-modal-dialog {
        max-width: 400px;
    }

    .logout-modal .modal-content {
        border-radius: 18px;
    }

    .logout-modal-footer .btn {
        white-space: nowrap;
    }

    /* Mobile: stack buttons full-width so they never overflow */
    @media (max-width: 400px) {
        .logout-modal-footer {
            flex-direction: column-reverse;
            gap: 8px !important;
        }

        .logout-modal-footer .btn,
        .logout-modal-footer .logout-form {
            width: 100%;
        }

        .logout-modal-footer .logout-form .btn {
            width: 100%;
        }

        .logout-modal-header {
            padding: 22px 18px 16px !important;
        }

        .logout-modal-body {
            padding: 1rem !important;
        }
    }

    /* Dark themed header matching sidebar/navbar */
    .logout-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }

    .logout-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
    }

    .logout-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        border-radius: 16px;
        background: rgba(255, 197, 8, 0.12);
        border: 1px solid rgba(255, 197, 8, 0.25);
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .logout-modal-body {
        background-color: #fff;
    }

    .logout-loading-state {
        background-color: #fff;
    }

    .logout-spinner .spinner-border {
        width: 2.5rem;
        height: 2.5rem;
        border-width: 3px;
    }

    .logout-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .logout-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .logout-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .logout-confirm-btn {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        transition: all 0.2s ease;
    }
    .logout-confirm-btn:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }
    .logout-confirm-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>

<script>
    (function () {
        const modalEl = document.getElementById('logoutModal');
        if (!modalEl) return;

        const form = modalEl.querySelector('.logout-form');
        const body = modalEl.querySelector('.logout-modal-body');
        const loadingState = modalEl.querySelector('.logout-loading-state');
        const footer = modalEl.querySelector('.logout-modal-footer');

        if (form) {
            form.addEventListener('submit', function () {
                // Hide confirmation UI, show "Logging out..." state
                if (body) body.classList.add('d-none');
                if (footer) footer.classList.add('d-none');
                if (loadingState) loadingState.classList.remove('d-none');

                // Prevent backdrop / esc from closing during logout
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) {
                    instance._config.backdrop = 'static';
                    instance._config.keyboard = false;
                }
            });
        }

        // Reset state when modal is closed (so subsequent opens are fresh)
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (body) body.classList.remove('d-none');
            if (footer) footer.classList.remove('d-none');
            if (loadingState) loadingState.classList.add('d-none');
        });
    })();
</script>

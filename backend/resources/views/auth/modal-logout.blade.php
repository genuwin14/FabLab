<!-- Logout Confirmation Modal -->
{{-- Shared by customer, staff and admin — the script below swaps the body and
     footer for a "Logging out…" state, so .logout-modal-body,
     .logout-loading-state, .logout-modal-footer and .logout-form are JS
     hooks, not just styling. --}}
<div class="modal fade qv-modal logout-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-body p-4 p-lg-5 pb-0 pb-lg-0 text-center logout-modal-body">
                <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <span class="qv-icon qv-icon-primary mb-4">
                    <i class="bi bi-box-arrow-right"></i>
                </span>

                <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">Account</span>
                <h5 id="logoutModalLabel" class="qv-title mb-2">Sign Out</h5>
                <p class="text-muted small mb-0">
                    Are you sure you want to sign out? You'll need to log in again to continue.
                </p>
            </div>

            <!-- Loading State (hidden by default) -->
            <div class="logout-loading-state d-none p-4 p-lg-5 text-center">
                <div class="logout-spinner mb-3">
                    <div class="spinner-border" role="status" style="color: #ffc508;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <h6 class="qv-title mb-1">Logging out...</h6>
                <p class="text-muted small mb-0">Securing your session, please wait.</p>
            </div>

            <!-- Footer with actions -->
            <div class="logout-modal-footer px-4 px-lg-5 pt-4 pb-4 pb-lg-5">
                <div class="qv-actions">
                    <form action="{{ route('logout') }}" method="POST" class="m-0 d-grid logout-form">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm logout-confirm-btn">
                            <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                        </button>
                    </form>
                    <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .logout-modal .logout-spinner .spinner-border {
        width: 2.5rem;
        height: 2.5rem;
        border-width: 3px;
    }

    /* Gold on hover, the way the sidebar's own sign-out link behaves. */
    .logout-modal .logout-confirm-btn:hover,
    .logout-modal .logout-confirm-btn:focus {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }

    .logout-modal .logout-confirm-btn:disabled {
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

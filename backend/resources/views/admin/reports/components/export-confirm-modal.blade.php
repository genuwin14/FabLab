<!-- Export Confirmation Modal -->
<div class="modal fade report-export-modal" id="exportConfirmModal" tabindex="-1"
    aria-labelledby="exportConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered report-export-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="report-export-modal-header">
                <div class="report-export-modal-icon" id="exportConfirmIconWrap">
                    <i class="bi bi-cloud-download-fill" id="exportConfirmIcon"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="exportConfirmLabel">Confirm Export</h5>
                <p class="text-white-50 small mb-0">The file will reflect the current filters</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 report-export-modal-body">
                <p class="text-muted small text-center mb-2" id="exportConfirmScopeKind">Scope</p>
                <p class="text-dark mb-1 text-center fw-semibold" id="exportConfirmScope">—</p>
                <p class="text-center mb-0">
                    <span class="badge rounded-pill px-3 py-2" id="exportConfirmFormatBadge">FORMAT</span>
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="report-export-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 report-export-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <a href="#" id="exportConfirmProceed"
                    class="btn fw-semibold rounded-pill px-4 report-export-confirm-btn">
                    <i class="bi bi-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .report-export-modal-dialog { max-width: 420px; }
    .report-export-modal .modal-content { border-radius: 18px; }

    .report-export-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }
    .report-export-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.4), transparent);
    }
    .report-export-modal-icon {
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
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .report-export-modal-body { background-color: #fff; }

    .report-export-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    .report-export-modal-footer .btn { white-space: nowrap; }

    .report-export-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .report-export-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .report-export-confirm-btn {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        transition: all 0.2s ease;
    }
    .report-export-confirm-btn:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }
</style>

<script>
    (function () {
        const modalEl = document.getElementById('exportConfirmModal');
        if (!modalEl) return;

        const iconEl = document.getElementById('exportConfirmIcon');
        const iconWrap = document.getElementById('exportConfirmIconWrap');
        const formatBadge = document.getElementById('exportConfirmFormatBadge');
        const scopeKindEl = document.getElementById('exportConfirmScopeKind');
        const scopeEl = document.getElementById('exportConfirmScope');
        const proceedEl = document.getElementById('exportConfirmProceed');

        const formatStyles = {
            PDF:  { badgeBg: '#dc3545', badgeFg: '#ffffff', icon: 'bi-file-earmark-pdf-fill' },
            Word: { badgeBg: '#0d6efd', badgeFg: '#ffffff', icon: 'bi-file-earmark-word-fill' },
        };

        function bindTrigger(btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url = btn.getAttribute('href') || btn.getAttribute('data-href') || '#';
                const format = btn.getAttribute('data-format') || 'File';
                const scope = btn.getAttribute('data-scope') || '';
                const scopeKind = btn.getAttribute('data-scope-kind') || 'Scope';

                const style = formatStyles[format] || { badgeBg: '#6c757d', badgeFg: '#fff', icon: 'bi-file-earmark' };

                // Header icon swap
                iconEl.className = 'bi ' + style.icon;

                // Format badge
                formatBadge.style.backgroundColor = style.badgeBg;
                formatBadge.style.color = style.badgeFg;
                formatBadge.textContent = format + ' Document';

                scopeKindEl.textContent = scopeKind;
                scopeEl.textContent = scope;
                proceedEl.setAttribute('href', url);

                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        }

        document.querySelectorAll('[data-export-trigger]').forEach(bindTrigger);

        proceedEl.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        });
    })();
</script>

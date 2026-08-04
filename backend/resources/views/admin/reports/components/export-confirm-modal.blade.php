<!-- Report Preview Modal -->
<div class="modal fade report-preview-modal" id="exportConfirmModal" tabindex="-1"
    aria-labelledby="exportConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered report-preview-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="report-preview-modal-header">
                <div class="report-preview-modal-icon" id="exportConfirmIconWrap">
                    <i class="bi bi-file-earmark" id="exportConfirmIcon"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <h5 class="modal-title fw-bold mb-1 text-white text-truncate" id="exportConfirmLabel">Report Preview</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-white-50 small text-truncate" id="exportConfirmScope">—</span>
                        <span class="text-white-50 small">·</span>
                        <span class="text-white-50 small" id="exportConfirmScopeKind">Scope</span>
                        <span class="badge rounded-pill px-2 py-1 small" id="exportConfirmFormatBadge">FORMAT</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Preview Body -->
            <!-- modal-body-fill: the iframe fills this body, so it manages its
                 own height instead of joining the global modal scroll. -->
            <div class="modal-body p-0 report-preview-modal-body modal-body-fill">
                <div class="report-preview-loading" id="exportPreviewLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                    <p class="text-muted small mt-3 mb-0">Generating preview…</p>
                </div>
                <iframe id="exportPreviewFrame"
                    class="report-preview-frame"
                    title="Report Preview"></iframe>
            </div>

            <!-- Footer with actions -->
            <div class="report-preview-modal-footer">
                <span class="text-muted small me-auto d-none d-md-inline">
                    <i class="bi bi-info-circle me-1"></i>Preview reflects current filters.
                </span>
                <button type="button" class="btn fw-semibold rounded-pill px-4 report-preview-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <a href="#" id="exportConfirmProceed"
                    class="btn fw-semibold rounded-pill px-4 report-preview-confirm-btn">
                    <i class="bi bi-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .report-preview-modal-dialog { max-width: 1100px; }
    .report-preview-modal .modal-content {
        border-radius: 18px;
        height: min(90vh, 850px);
        display: flex;
        flex-direction: column;
    }

    .report-preview-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        flex-shrink: 0;
    }
    .report-preview-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.4), transparent);
    }
    .report-preview-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 197, 8, 0.15);
        border: 1px solid rgba(255, 197, 8, 0.3);
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }
    .report-preview-modal-header .btn-close {
        flex-shrink: 0;
        opacity: 0.8;
    }
    .report-preview-modal-header .btn-close:hover { opacity: 1; }

    .report-preview-modal-body {
        background-color: #f1f4f8;
        flex: 1 1 auto;
        overflow: hidden;
        position: relative;
        min-height: 0;
    }
    .report-preview-frame {
        width: 100%;
        height: 100%;
        border: 0;
        background-color: #fff;
        display: block;
    }
    .report-preview-loading {
        position: absolute;
        inset: 0;
        background-color: rgba(241, 244, 248, 0.95);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .report-preview-modal-footer {
        background-color: #fff;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
    }
    .report-preview-modal-footer .btn { white-space: nowrap; }

    .report-preview-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .report-preview-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .report-preview-confirm-btn {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        transition: all 0.2s ease;
    }
    .report-preview-confirm-btn:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }

    /* ── Mobile ( < lg / 992px ) — see ResponsiveMobileNote.md §6 ── */
    @media (max-width: 991.98px) {
        .report-preview-modal-dialog { margin: 0.5rem; }
        .report-preview-modal .modal-content { height: min(92vh, 850px); }
        .report-preview-modal-header { padding: 14px 16px; }
        .report-preview-modal-header .modal-title { font-size: 1rem; }
        .report-preview-modal-footer { padding: 12px 16px; }
        .report-preview-modal-footer .btn { font-size: 0.85rem; padding-left: 1rem; padding-right: 1rem; }
    }
</style>

<script>
    (function () {
        const modalEl = document.getElementById('exportConfirmModal');
        if (!modalEl) return;

        const iconEl = document.getElementById('exportConfirmIcon');
        const formatBadge = document.getElementById('exportConfirmFormatBadge');
        const scopeKindEl = document.getElementById('exportConfirmScopeKind');
        const scopeEl = document.getElementById('exportConfirmScope');
        const proceedEl = document.getElementById('exportConfirmProceed');
        const frameEl = document.getElementById('exportPreviewFrame');
        const loadingEl = document.getElementById('exportPreviewLoading');

        const formatStyles = {
            PDF:  { badgeBg: '#dc3545', badgeFg: '#ffffff', icon: 'bi-file-earmark-pdf-fill' },
            Word: { badgeBg: '#0d6efd', badgeFg: '#ffffff', icon: 'bi-file-earmark-word-fill' },
        };

        function showLoading() {
            if (loadingEl) loadingEl.style.display = '';
        }
        function hideLoading() {
            if (loadingEl) loadingEl.style.display = 'none';
        }

        function bindTrigger(btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url = btn.getAttribute('href') || btn.getAttribute('data-href') || '#';
                const previewUrl = btn.getAttribute('data-preview-url') || '';
                const format = btn.getAttribute('data-format') || 'File';
                const scope = btn.getAttribute('data-scope') || '';
                const scopeKind = btn.getAttribute('data-scope-kind') || 'Scope';

                const style = formatStyles[format] || { badgeBg: '#6c757d', badgeFg: '#fff', icon: 'bi-file-earmark' };

                iconEl.className = 'bi ' + style.icon;

                formatBadge.style.backgroundColor = style.badgeBg;
                formatBadge.style.color = style.badgeFg;
                formatBadge.textContent = format;

                scopeKindEl.textContent = scopeKind;
                scopeEl.textContent = scope;
                proceedEl.setAttribute('href', url);

                showLoading();
                frameEl.src = 'about:blank';
                if (previewUrl) {
                    // assign on next tick so the about:blank reset is observable
                    requestAnimationFrame(() => { frameEl.src = previewUrl; });
                } else {
                    hideLoading();
                }

                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        }

        document.querySelectorAll('[data-export-trigger]').forEach(bindTrigger);

        frameEl.addEventListener('load', function () {
            if (frameEl.src && frameEl.src !== 'about:blank' && !frameEl.src.endsWith('about:blank')) {
                hideLoading();
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            frameEl.src = 'about:blank';
            showLoading();
        });

        proceedEl.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        });
    })();
</script>

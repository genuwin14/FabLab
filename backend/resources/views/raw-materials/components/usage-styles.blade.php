{{--
    Styles for the tabs, the ledger table and the two usage modals. Kept apart
    from the per-role index views because both include the same markup.

    Palette matches the rest of the app: #0e2e45 navy, #ffc508 accent.
--}}
<style>
    /* ============================================
       Page tabs (mirrors the Reports section tabs)
       ============================================ */
    .material-tabs {
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    .material-tabs .nav-link {
        border: 0;
        border-radius: 10px 10px 0 0;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.55rem 1rem;
        background: transparent;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .material-tabs .nav-link:hover {
        color: #0e2e45;
        background: rgba(255, 197, 8, 0.08);
    }
    .material-tabs .nav-link.active {
        color: #0e2e45;
        background: #fff;
        border-bottom: 3px solid #ffc508;
        box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.04) inset;
    }
    .material-tabs .nav-link i { font-size: 0.95rem; }

    .material-tab-count {
        background: rgba(14, 46, 69, 0.08);
        color: #0e2e45;
        border-radius: 999px;
        padding: 0.05rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .material-tabs .nav-link.active .material-tab-count {
        background: rgba(255, 197, 8, 0.25);
    }

    /* ============================================
       Ledger table
       ============================================ */
    .usage-reason-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.2rem 0.65rem;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .usage-reason-badge i { font-size: 0.78rem; }

    /* A reversed entry is history, not current truth — mute it. */
    .usage-row-reversed > td { opacity: 0.55; }
    .usage-row-reversed > td:nth-child(4) { text-decoration: line-through; }

    /* ============================================
       Record Usage modal
       ============================================ */
    .usage-material-strip {
        background: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        padding: 12px 14px;
    }
    .usage-material-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255, 197, 8, 0.15);
        color: #0e2e45;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* The read-only stock panel that replaced the editable field. */
    .usage-locked-strip {
        background: #f8f9fa;
        border: 1px dashed rgba(0, 0, 0, 0.12);
        border-radius: 12px;
        padding: 12px 14px;
    }

    .usage-unit-addon {
        border-radius: 0 10px 10px 0 !important;
        background-color: #f8f9fa;
        border: 1px solid transparent;
        color: #6c757d;
        text-transform: lowercase;
        min-width: 68px;
        justify-content: center;
    }
    /* The quantity input sits on the left of its addon, so undo the shared
       material-modal rule that rounds the right-hand edge. */
    .material-modal .input-group > #usageQuantity {
        border-radius: 10px 0 0 10px !important;
    }

    .usage-preview {
        font-size: 0.76rem;
        font-weight: 600;
        min-height: 1.1rem;
    }
    .usage-preview-ok { color: #0c6c3a; }
    .usage-preview-warn { color: #a02633; }
    .usage-preview-muted { color: #6c757d; }

    .material-btn-save:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #0e2e45;
        border-color: #0e2e45;
        color: #fff;
    }

    /* ============================================
       Reverse confirmation (mirrors the delete modal)
       ============================================ */
    .usage-reverse-modal-dialog { max-width: 420px; }
    .usage-reverse-modal .modal-content { border-radius: 18px; }

    .usage-reverse-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }
    .usage-reverse-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(25, 135, 84, 0.4), transparent);
    }
    .usage-reverse-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        border-radius: 16px;
        background: rgba(25, 135, 84, 0.15);
        border: 1px solid rgba(25, 135, 84, 0.3);
        color: #4ade80;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .usage-reverse-modal-body { background-color: #fff; }

    .usage-reverse-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    .usage-reverse-modal-footer .btn { white-space: nowrap; font-weight: 600; }

    .usage-reverse-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .usage-reverse-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .usage-reverse-confirm-btn {
        background-color: #198754;
        border: 1px solid #198754;
        color: #fff;
        transition: all 0.2s ease;
    }
    .usage-reverse-confirm-btn:hover {
        background-color: #146c43;
        border-color: #146c43;
        color: #fff;
    }

    /* ============================================
       Mobile ( < lg / 992px ) — see ResponsiveMobileNote.md
       ============================================ */
    @media (max-width: 991.98px) {
        .material-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            min-width: 0;
            max-width: 100%;
        }
        .material-tabs::-webkit-scrollbar { height: 4px; }
        .material-tabs::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.15); border-radius: 999px; }
        .material-tabs .nav-item { flex-shrink: 0; }
        .material-tabs .nav-link {
            font-size: 0.75rem;
            padding: 0.45rem 0.7rem;
            white-space: nowrap;
        }

        /* The ledger has one more column than the materials table. */
        .usage-log-card .table { min-width: 920px; }
        .usage-log-card .pagination-bar { min-width: 920px; }

        .usage-reverse-modal .modal-dialog { margin: 0.5rem; }
        .usage-reverse-modal-header { padding: 20px 16px 16px; }
        .usage-reverse-modal-footer { padding: 12px 16px 18px; }
    }
</style>

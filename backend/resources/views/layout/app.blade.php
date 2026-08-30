<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>FABLAB | Innovation Meets Excellence</title>
    <link rel="icon" type="image/png" href="{{ asset('FABLAB-LOGO.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --bs-primary: #0e2e45;
            --bs-primary-rgb: 14, 46, 69;
            --bs-secondary: #ffc508;
            /* Using gold as secondary for custom use */
            --bs-secondary-rgb: 255, 197, 8;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* ==================================================================
           Thin scrollbars, app-wide (admin / staff / customer)

           A baseline for every scroll area that never styled a bar of its
           own — the page itself, tables, panels — so none of them fall back
           to the chunky OS default. This sits in the head block on purpose:
           page-level <style> blocks load later and still win, including the
           ones that deliberately hide a bar or repaint it darker.

           Chrome\Edge\Safari take the ::-webkit- rules below; they ignore
           them the moment scrollbar-width/-color are set, so those go to
           browsers without ::-webkit-scrollbar support (Firefox) only.
           ================================================================== */
        @supports not selector(::-webkit-scrollbar) {

            /* Both properties inherit, so html alone reaches every scroller. */
            html {
                scrollbar-width: thin;
                scrollbar-color: rgba(14, 46, 69, 0.28) transparent;
            }

            /* The three sidebars are the only dark scroll surfaces; a dark
               thumb would vanish against them. */
            .custom-scrollbar {
                scrollbar-color: rgba(255, 255, 255, 0.22) transparent;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(14, 46, 69, 0.28);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 46, 69, 0.45);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* Primary Overrides (Dark Blue) */
        .btn-primary {
            background-color: #0e2e45;
            border-color: #0e2e45;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #081d2e;
            border-color: #081d2e;
        }

        .text-primary {
            color: #0e2e45 !important;
        }

        /* Through rgba, not a flat hex: a flat background-color would outrank
           --bs-bg-opacity and paint every `bg-primary bg-opacity-10` tint solid
           navy — which is how a badge ended up navy-on-navy. Untinted callers
           still get #0e2e45, since Bootstrap leaves --bs-bg-opacity at 1. */
        .bg-primary {
            background-color: rgba(var(--bs-primary-rgb), var(--bs-bg-opacity, 1)) !important;
        }

        /* Accent/Gold Classes */
        .text-accent {
            color: #ffc508 !important;
        }

        .bg-accent {
            background-color: #ffc508 !important;
        }

        .btn-accent {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
            font-weight: 600;
        }

        .btn-accent:hover {
            background-color: #eebb07;
            border-color: #eebb07;
            color: #0e2e45;
        }

        /* Text Gradient Gold */
        .text-gradient-gold {
            background: linear-gradient(45deg, #FFD700, #FDB931);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: #ffc508;
            /* Fallback */
        }

        /* Helper Classes */
        .h-screen {
            height: 100vh;
        }

        /* ── No-flash sidebar collapsed state ──
           Pre-applied via inline script in <head> based on localStorage. */
        html.sidebar-preload-collapsed aside:has(> .sidebar-inner) { width: 76px !important; }
        html.sidebar-preload-collapsed .sidebar-spacer { width: 76px !important; }
        html.sidebar-preload-collapsed .sidebar-inner {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            align-items: center;
        }
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-label {
            opacity: 0 !important;
            max-width: 0 !important;
            margin-left: 0 !important;
        }
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-section-title {
            height: 1px !important;
            background: rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0.25rem !important;
            padding: 0 !important;
            color: transparent !important;
        }
        html.sidebar-preload-collapsed .sidebar-inner .nav.flex-column { margin-bottom: 0.25rem !important; }
        html.sidebar-preload-collapsed .sidebar-inner .nav-link {
            justify-content: center;
            width: 42px;
            height: 42px;
            padding: 0;
            margin: 0 auto;
        }
        html.sidebar-preload-collapsed .sidebar-inner .nav-link i { margin-right: 0 !important; }
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-brand { justify-content: center; }
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-logo { margin-right: 0 !important; }
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-header-divider {
            display: block !important;
            align-self: stretch;
            width: auto !important;
        }
        /* Chevron flips to point right while preloaded (until JS swaps the icon class). */
        html.sidebar-preload-collapsed .sidebar-collapse-icon { transform: rotate(180deg); }

        /* ── Suppress sidebar collapse/expand transitions on page load ──
           Until JS adds `.sidebar-armed` to <html>, every transitionable property
           on the sidebar tree is disabled. This prevents the "close → open"
           animation that fires on every navigation when a CSS rule (e.g. padding,
           opacity) changes during the JS handover. */
        html:not(.sidebar-armed) aside:has(> .sidebar-inner),
        html:not(.sidebar-armed) aside:has(> .sidebar-inner) *,
        html:not(.sidebar-armed) .sidebar-spacer,
        html:not(.sidebar-armed) .sidebar-inner,
        html:not(.sidebar-armed) .sidebar-inner * {
            transition: none !important;
        }

        /* Strip the legacy bg-white + shadow-sm from page-level <header> wrappers
           that hold the dark navbar. The navbar paints its own dark background. */
        header:has(> .custom-navbar) {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Strip the legacy border-end + shadow-sm on the <aside> sidebar wrapper.
           The sidebar's dark fill provides its own visual boundary. */
        aside:has(> .sidebar-inner) {
            border-right: none !important;
            box-shadow: none !important;
        }

        /* ==================================================================
           Table action buttons (admin / staff / customer)

           Every Actions column reads as one toolbar: an icon, a label, and
           a light 5px corner instead of a pill or a circle. Set here rather
           than per page so a new table inherits the shape just by naming
           the class, and so the three roles never drift apart again.
           ================================================================== */
        .table-action-btn {
            border-radius: 5px !important;
        }
    </style>

    <script>
        // Read the saved sidebar state and apply a class to <html> before the page paints,
        // so the sidebar doesn't flash expanded on every navigation.
        (function () {
            try {
                var keys = ['adminSidebarCollapsed', 'staffSidebarCollapsed', 'customerSidebarCollapsed'];
                for (var i = 0; i < keys.length; i++) {
                    if (localStorage.getItem(keys[i]) === 'true') {
                        document.documentElement.classList.add('sidebar-preload-collapsed');
                        break;
                    }
                }
            } catch (e) { /* localStorage unavailable */ }
        })();

        // Arm sidebar transitions only AFTER the JS sidebar handover and a paint,
        // so refresh/navigation never replays the collapse/expand animation.
        // Toggle clicks happen well after this, so they still animate normally.
        document.addEventListener('DOMContentLoaded', function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    document.documentElement.classList.add('sidebar-armed');
                });
            });
        });
    </script>
</head>

<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Logout Modal -->
    @include('auth.modal-logout')

    <!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces the browser's native alert()/confirm() dialogs, which
         ignore the app's theme entirely. Driven from JS via
         showAlertModal() / showConfirmModal() — see the script further
         down. One element, reused by every caller.
         ================================================================== -->
    <div class="modal fade app-alert-modal" id="appAlertModal" tabindex="-1" aria-labelledby="appAlertModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered app-alert-modal-dialog">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <!-- Themed Header -->
                <div class="app-alert-modal-header">
                    <div class="app-alert-modal-icon">
                        <i class="bi bi-info-circle-fill" id="appAlertModalIcon"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-1 text-white" id="appAlertModalLabel">Notice</h5>
                    <p class="text-white-50 small mb-0 d-none" id="appAlertModalSubtitle"></p>
                </div>

                <!-- Message Body -->
                <div class="modal-body p-4 app-alert-modal-body">
                    <p class="text-dark mb-0 text-center" id="appAlertModalMessage"></p>
                </div>

                <!-- Footer with actions -->
                <div class="app-alert-modal-footer">
                    <button type="button" class="btn fw-semibold rounded-pill px-4 app-alert-cancel-btn d-none"
                        id="appAlertModalCancel" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn fw-semibold rounded-pill px-4 app-alert-confirm-btn"
                        id="appAlertModalConfirm">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ==================================================================
           Global Alert / Confirm Modal

           Same shape as the app's themed confirmation modals (dark gradient
           header, icon tile, centered body, pill buttons). The accent is a
           set of custom properties so one element can serve every variant.
           ================================================================== */
        .app-alert-modal {
            --app-alert-rgb: 13, 110, 253;
            --app-alert-icon: #8ab4fe;
            --app-alert-solid: #0d6efd;
            --app-alert-solid-hover: #0b5ed7;
            --app-alert-on-solid: #fff;
        }

        .app-alert-modal-dialog {
            max-width: 420px;
        }

        .app-alert-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }

        .app-alert-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(var(--app-alert-rgb), 0.4), transparent);
        }

        .app-alert-modal-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 16px;
            background: rgba(var(--app-alert-rgb), 0.15);
            border: 1px solid rgba(var(--app-alert-rgb), 0.3);
            color: var(--app-alert-icon);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .app-alert-modal-body {
            background-color: #fff;
        }

        .app-alert-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .app-alert-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .app-alert-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .app-alert-confirm-btn {
            background-color: var(--app-alert-solid);
            border: 1px solid var(--app-alert-solid);
            color: var(--app-alert-on-solid);
            transition: all 0.2s ease;
        }

        .app-alert-confirm-btn:hover,
        .app-alert-confirm-btn:focus {
            background-color: var(--app-alert-solid-hover);
            border-color: var(--app-alert-solid-hover);
            color: var(--app-alert-on-solid);
        }

        @media (max-width: 575.98px) {
            .app-alert-modal-header {
                padding: 20px 16px 16px;
            }

            .app-alert-modal-footer {
                padding: 12px 16px 16px;
                flex-direction: column-reverse;
            }

            .app-alert-modal-footer > .btn {
                width: 100%;
            }
        }
    </style>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <!-- Toasts will be dynamically pushed here -->
    </div>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (as per TechStack) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @stack('scripts')

    <!-- Global Toast Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastContainer = document.querySelector('.toast-container');

            window.showToast = function (message, type = 'info') {
                const toastId = 'toast-' + Date.now();
                const bgColor = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' :
                    'bg-primary');
                const textColor = 'text-white';
                const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'error' ?
                    'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

                const toastHtml = `
            <div id="${toastId}" class="toast align-items-center ${bgColor} ${textColor} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${icon} fs-5"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            `;

                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    delay: 5000
                });
                toast.show();

                // Remove after hide
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });
            };

            // Check for Session Flashed Messages
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if (session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            @if (session('status'))
                showToast("{{ session('status') }}", 'info');
            @endif

            @if ($errors->any())
                // Show first error in toast
                showToast("{{ $errors->first() }}", 'error');
            @endif
        });
    </script>

    <!-- Global Alert / Confirm Modal Script -->
    <script>
        (function () {
            const el = document.getElementById('appAlertModal');
            if (!el) return;

            const iconEl = document.getElementById('appAlertModalIcon');
            const titleEl = document.getElementById('appAlertModalLabel');
            const subtitleEl = document.getElementById('appAlertModalSubtitle');
            const messageEl = document.getElementById('appAlertModalMessage');
            const cancelBtn = document.getElementById('appAlertModalCancel');
            const confirmBtn = document.getElementById('appAlertModalConfirm');

            const VARIANTS = {
                info: { icon: 'bi-info-circle-fill', rgb: '13, 110, 253', tint: '#8ab4fe', solid: '#0d6efd', hover: '#0b5ed7', on: '#fff' },
                success: { icon: 'bi-check-circle-fill', rgb: '25, 135, 84', tint: '#75d3a5', solid: '#198754', hover: '#146c43', on: '#fff' },
                warning: { icon: 'bi-exclamation-triangle-fill', rgb: '255, 197, 8', tint: '#ffd75c', solid: '#ffc508', hover: '#eebb07', on: '#0e2e45' },
                danger: { icon: 'bi-exclamation-triangle-fill', rgb: '220, 53, 69', tint: '#ff8088', solid: '#dc3545', hover: '#b02a37', on: '#fff' }
            };

            // The dialog is asynchronous, unlike the native alert()/confirm() it
            // replaces — callers get a Promise that settles once it has closed.
            let resolver = null;
            let outcome = false;

            // These dialogs usually open on top of another modal (a validation
            // warning raised from inside a form modal), which Bootstrap doesn't
            // support out of the box: the parent keeps the higher stacking order
            // and its focus trap drags focus straight back out of this dialog.
            // Lift this one above the parent and park the parent's trap meanwhile.
            let suspendedTraps = [];

            el.addEventListener('show.bs.modal', function () {
                const openModals = document.querySelectorAll('.modal.show');

                suspendedTraps = [];
                openModals.forEach(function (parent) {
                    const trap = (bootstrap.Modal.getInstance(parent) || {})._focustrap;
                    if (trap && typeof trap.deactivate === 'function') {
                        trap.deactivate();
                        suspendedTraps.push(trap);
                    }
                });

                if (!openModals.length) {
                    el.style.zIndex = '';
                    return;
                }

                const z = 1055 + openModals.length * 20;
                el.style.zIndex = z;
                // The backdrop is appended after this event, so bump it next tick.
                setTimeout(function () {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    const top = backdrops[backdrops.length - 1];
                    if (top) top.style.zIndex = z - 5;
                }, 0);
            });

            el.addEventListener('hidden.bs.modal', function () {
                suspendedTraps.forEach(function (trap) {
                    if (typeof trap.activate === 'function') trap.activate();
                });
                suspendedTraps = [];

                // Bootstrap drops .modal-open on the body when any modal closes,
                // which would unlock page scrolling behind a still-open parent.
                if (document.querySelectorAll('.modal.show').length > 0) {
                    document.body.classList.add('modal-open');
                }

                if (resolver) {
                    const done = resolver;
                    resolver = null;
                    done(outcome);
                }
            });

            confirmBtn.addEventListener('click', function () {
                outcome = true;
                bootstrap.Modal.getOrCreateInstance(el).hide();
            });

            function open(options, isConfirm) {
                const o = typeof options === 'string' ? { message: options } : (options || {});
                const variant = VARIANTS[o.variant] || (isConfirm ? VARIANTS.warning : VARIANTS.info);

                el.style.setProperty('--app-alert-rgb', variant.rgb);
                el.style.setProperty('--app-alert-icon', variant.tint);
                el.style.setProperty('--app-alert-solid', variant.solid);
                el.style.setProperty('--app-alert-solid-hover', variant.hover);
                el.style.setProperty('--app-alert-on-solid', variant.on);

                iconEl.className = 'bi ' + (o.icon || variant.icon);
                titleEl.textContent = o.title || (isConfirm ? 'Are you sure?' : 'Notice');
                messageEl.textContent = o.message || '';

                subtitleEl.textContent = o.subtitle || '';
                subtitleEl.classList.toggle('d-none', !o.subtitle);

                confirmBtn.textContent = o.confirmText || (isConfirm ? 'Confirm' : 'OK');
                cancelBtn.textContent = o.cancelText || 'Cancel';
                cancelBtn.classList.toggle('d-none', !isConfirm);

                // A second call while one is still open settles the first as dismissed.
                if (resolver) {
                    const stale = resolver;
                    resolver = null;
                    stale(false);
                }

                outcome = false;
                bootstrap.Modal.getOrCreateInstance(el).show();

                return new Promise(function (resolve) {
                    resolver = resolve;
                });
            }

            // showAlertModal('Something went wrong.')
            // showAlertModal({ title, subtitle, message, variant, confirmText })
            window.showAlertModal = function (options) {
                return open(options, false);
            };

            // showConfirmModal({ ... }).then(ok => { if (ok) ... })
            window.showConfirmModal = function (options) {
                return open(options, true);
            };
        })();
    </script>

    <!-- Global Tooltip Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all existing tooltips
            function initTooltips(container) {
                var tooltipEls = (container || document).querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipEls.forEach(function (el) {
                    if (!bootstrap.Tooltip.getInstance(el)) {
                        new bootstrap.Tooltip(el, {
                            trigger: 'hover',
                            delay: { show: 300, hide: 100 }
                        });
                    }
                });
            }

            initTooltips();

            // Auto-initialize tooltips on dynamically added elements
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1) {
                            initTooltips(node.parentElement);
                        }
                    });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>

    <!-- A11y: Bootstrap sets aria-hidden="true" on a modal while a control
         inside it (e.g. a data-bs-dismiss Cancel button) still holds focus,
         which logs "Blocked aria-hidden ... descendant retained focus".
         Move focus out the moment the modal starts hiding. Event bubbles to
         document, so this one listener covers every modal in the app. -->
    <script>
        document.addEventListener('hide.bs.modal', function (event) {
            var modal = event.target;
            if (modal && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });
    </script>

    <style>
        /* Global Tooltip Styling */
        .tooltip {
            --bs-tooltip-bg: #0e2e45;
            --bs-tooltip-color: #fff;
            --bs-tooltip-opacity: 0.95;
            --bs-tooltip-padding-x: 0.65rem;
            --bs-tooltip-padding-y: 0.35rem;
            --bs-tooltip-font-size: 0.78rem;
            font-family: 'Poppins', sans-serif;
        }

        .tooltip-inner {
            font-weight: 500;
            letter-spacing: 0.015em;
            border-radius: 0.375rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* ==================================================================
           Global Modal Scrolling (admin / customer / staff)

           A tall modal used to grow past the viewport, which handed the
           scrolling to the .modal overlay — so the bar appeared outside the
           card, hard against the page edge, and the header scrolled away.
           Cap the card at the viewport and let .modal-body do the scrolling
           instead, so the bar sits inside the modal with the same thin
           styling the sidebars use.

           Loaded after the page-level <style> blocks, so these win ties.
           ================================================================== */
        .modal .modal-content {
            max-height: calc(100vh - 3.5rem);
            /* dvh keeps mobile browser chrome from eating the footer */
            max-height: calc(100dvh - 3.5rem);
        }

        @media (max-width: 575.98px) {

            /* Bootstrap drops --bs-modal-margin to 0.5rem below sm, and the
               page styles that override it use the same 0.5rem. */
            .modal .modal-content {
                max-height: calc(100vh - 1rem);
                max-height: calc(100dvh - 1rem);
            }
        }

        /* Headers and footers hold their size; only the body gives up space.
           These modals use themed *-modal-header / *-modal-footer divs rather
           than Bootstrap's own classes, so match on the suffix. */
        .modal .modal-content > [class*="modal-header"],
        .modal .modal-content > [class*="modal-footer"],
        .modal .modal-content > form > [class*="modal-footer"] {
            flex-shrink: 0;
        }

        /* Modals that wrap body + footer in a <form> have to pass the height
           cap through the form, or the body never learns it has to scroll. */
        .modal .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        /* ...but a <form> carrying only the footer must never be squeezed. */
        .modal .modal-content > form:not(:has(.modal-body)) {
            flex-shrink: 0;
            overflow: visible;
        }

        /* The scroll area. .modal-body-fill opts out: those bodies size their
           own content (report iframe, 3D canvas). .modal-scroll-area opts in
           for the one modal that has no .modal-body. */
        .modal .modal-body:not(.modal-body-fill),
        .modal .modal-scroll-area {
            overflow-y: auto;
            min-height: 0;
        }

        /* Several modals keep the action bar inside .modal-body (inside the
           <form>, so the submit button stays wired up). Pin it to the bottom
           instead of letting it scroll away with the fields. */
        .modal .modal-body [class*="modal-footer"] {
            position: sticky;
            bottom: 0;
            z-index: 3;
        }

        /* Thin in-modal scrollbar — same shape as the sidebar bars.
           Chrome/Edge/Safari take the ::-webkit- rules below; they ignore them
           the moment scrollbar-width/-color are set, so those go to browsers
           without ::-webkit-scrollbar support (Firefox) only. */
        @supports not selector(::-webkit-scrollbar) {

            .modal .modal-body,
            .modal .modal-scroll-area {
                scrollbar-width: thin;
                scrollbar-color: rgba(14, 46, 69, 0.22) transparent;
            }

            .modal .modal-content.bg-dark .modal-body,
            .modal .modal-content.modal-content-dark .modal-body {
                scrollbar-color: rgba(255, 255, 255, 0.22) transparent;
            }
        }

        .modal .modal-body::-webkit-scrollbar,
        .modal .modal-scroll-area::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .modal .modal-body::-webkit-scrollbar-track,
        .modal .modal-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal .modal-body::-webkit-scrollbar-thumb,
        .modal .modal-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(14, 46, 69, 0.22);
            border-radius: 999px;
        }

        .modal .modal-body::-webkit-scrollbar-thumb:hover,
        .modal .modal-scroll-area::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 46, 69, 0.38);
        }

        /* Dark-surfaced modals need the light thumb instead. */
        .modal .modal-content.bg-dark .modal-body::-webkit-scrollbar-thumb,
        .modal .modal-content.modal-content-dark .modal-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.22);
        }

        .modal .modal-content.bg-dark .modal-body::-webkit-scrollbar-thumb:hover,
        .modal .modal-content.modal-content-dark .modal-body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.38);
        }
    </style>
</body>

</html>
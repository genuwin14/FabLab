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

        .bg-primary {
            background-color: #0e2e45 !important;
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
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
        html.sidebar-preload-collapsed aside:has(> .sidebar-inner) { width: 70px !important; }
        html.sidebar-preload-collapsed .sidebar-spacer { width: 70px !important; }
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
        /* Chevron flips to point right while preloaded (until JS swaps the icon class). */
        html.sidebar-preload-collapsed .sidebar-collapse-icon { transform: rotate(180deg); }
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
    </style>
</body>

</html>
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
    </style>
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
</body>

</html>
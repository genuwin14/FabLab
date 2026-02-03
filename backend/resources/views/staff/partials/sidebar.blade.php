<div class="d-flex flex-column p-3 text-white h-100" style="background-color: #05111a;">
    <!-- Fixed Header -->
    <div class="flex-shrink-0">
        <a href="{{ route('staff.dashboard') }}" class="d-flex align-items-center mb-4 text-white text-decoration-none">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" width="40" height="40" class="me-2">
            <span class="fs-4 fw-bold tracking-tight">CSPC FAB<span class="text-warning">LAB</span></span>
        </a>
    </div>

    <!-- Scrollable Menu Content -->
    <div class="flex-grow-1 overflow-y-auto pe-1 custom-scrollbar">
        <!-- General Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">General</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('staff.dashboard') }}"
                    class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>
        </ul>

        <!-- Sales & Orders Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Sales & Orders</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('staff.orders.index') }}"
                    class="nav-link {{ request()->routeIs('staff.orders.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}">
                    <i class="bi bi-cart4 me-2"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-palette me-2"></i>
                    Customize Orders
                </a>
            </li>
        </ul>

        <!-- Inventory Control Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Inventory Control</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-box-seam me-2"></i>
                    Product
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-layers me-2"></i>
                    Textures
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Inventory logs
                </a>
            </li>
        </ul>

        <!-- Procurement Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Procurement</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-bag-check me-2"></i>
                    Purchase Supply
                </a>
            </li>
        </ul>

        <!-- Administration Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Administration</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    Reports
                </a>
            </li>
        </ul>
    </div>

    <!-- Fixed Footer (Settings) -->
    <div class="flex-shrink-0 mt-2">
        <hr class="opacity-25 mt-0 mb-2">
        <ul class="nav nav-pills flex-column">
            <li>
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .nav-link.hover-accent:hover {
        background-color: rgba(255, 197, 8, 0.1);
        color: #ffc508 !important;
    }

    .nav-link.hover-accent:hover i {
        color: #ffc508 !important;
    }

    .bg-accent {
        background-color: #ffc508 !important;
    }

    .text-primary {
        color: #0e2e45 !important;
    }

    /* Scrollbar styling for webkit browsers */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #05111a;
    }

    ::-webkit-scrollbar-thumb {
        background: #1a2c38;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #2a4153;
    }
</style>
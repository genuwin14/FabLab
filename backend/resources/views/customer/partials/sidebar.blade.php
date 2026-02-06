<div class="d-flex flex-column p-3 text-white h-100" style="background-color: #05111a;">
    <!-- Fixed Header -->
    <div class="flex-shrink-0">
        <a href="{{ route('customer.shop') }}" class="d-flex align-items-center mb-4 text-white text-decoration-none">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" width="40" height="40" class="me-2">
            <span class="fs-4 fw-bold tracking-tight">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>
    </div>

    <!-- Scrollable Menu Content -->
    <div class="flex-grow-1 overflow-y-auto pe-1 custom-scrollbar">
        <!-- Explore Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Explore</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.shop') }}"
                    class="nav-link {{ request()->routeIs('customer.shop') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}">
                    <i class="bi bi-shop me-2"></i>
                    Shop Products
                </a>
            </li>
        </ul>

        <!-- Design Center Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Design Center</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.customize.index') }}"
                    class="nav-link {{ request()->routeIs('customer.customize.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}">
                    <i class="bi bi-palette me-2"></i>
                    Customize Product
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent">
                    <i class="bi bi-vector-pen me-2"></i>
                    Personal Design
                </a>
            </li>
        </ul>

        <!-- Transactions Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold px-2">Transactions</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.orders.index') }}"
                    class="nav-link {{ request()->routeIs('customer.orders.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}">
                    <i class="bi bi-bag-check me-2"></i>
                    My Orders
                    @if(isset($inProgressCount) && $inProgressCount > 0)
                        <span
                            class="badge bg-warning text-dark rounded-pill float-end smaller">{{ $inProgressCount }}</span>
                    @endif
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
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #05111a;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1a2c38;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #2a4153;
    }
</style>
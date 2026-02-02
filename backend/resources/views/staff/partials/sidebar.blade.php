<div class="d-flex flex-column p-3 text-white h-100" style="background-color: #05111a;">
    <a href="{{ route('staff.dashboard') }}" class="d-flex align-items-center mb-2 text-white text-decoration-none">
        <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" width="40" height="40" class="me-2">
        <span class="fs-4 fw-bold tracking-tight">FAB<span class="text-warning">LAB</span></span>
    </a>
    <hr class="mt-0 mb-3 opacity-25">
    <ul class="nav nav-pills flex-column mb-auto gap-2">
        <li class="nav-item">
            <a href="{{ route('staff.dashboard') }}"
                class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                aria-current="page">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-cart4 me-2"></i>
                Orders
                <span class="badge bg-danger rounded-pill float-end smaller">3</span>
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-box-seam me-2"></i>
                Inventory
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-arrow-repeat me-2"></i>
                Restock Requests
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-receipt me-2"></i>
                POS
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-tools me-2"></i>
                Machines
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-palette me-2"></i>
                Customization
            </a>
        </li>
    </ul>

    <hr>

    <ul class="nav nav-pills flex-column mb-3">
        <li>
            <a href="#" class="nav-link text-white hover-accent">
                <i class="bi bi-gear me-2"></i>
                Profile
            </a>
        </li>
    </ul>
</div>

<style>
    .nav-link.hover-accent:hover {
        background-color: rgba(255, 197, 8, 0.1);
        color: #ffc508 !important;
    }

    .bg-accent {
        background-color: #ffc508 !important;
    }

    .text-primary {
        color: #0e2e45 !important;
    }
</style>
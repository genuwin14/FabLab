<div class="d-flex flex-column p-3 text-white h-100 sidebar-inner" style="background-color: #05111a;">
    <!-- Fixed Header -->
    <div class="flex-shrink-0 sidebar-header d-flex align-items-center">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-white text-decoration-none sidebar-brand">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" width="40" height="40" class="me-2 sidebar-logo">
            <span class="fs-4 fw-bold tracking-tight sidebar-label">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>
    </div>
    <hr class="sidebar-header-divider opacity-25 mt-0 mb-4">

    <!-- Scrollable Menu Content -->
    <div class="flex-grow-1 overflow-y-auto pe-1 custom-scrollbar">
        <!-- General Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title mt-2">General</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.dashboard') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Dashboard">
                    <i class="bi bi-columns-gap me-2"></i>
                    <span class="sidebar-label">Dashboard</span>
                </a>
            </li>
        </ul>

        <!-- Inventory Control Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Inventory Control</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.products.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Products">
                    <i class="bi bi-box-seam me-2"></i>
                    <span class="sidebar-label">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.categories.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Categories">
                    <i class="bi bi-tags me-2"></i>
                    <span class="sidebar-label">Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.raw-materials.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.raw-materials.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Raw Materials">
                    <i class="bi bi-boxes me-2"></i>
                    <span class="sidebar-label">Raw Materials</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.textures.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.textures.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Textures">
                    <i class="bi bi-layers me-2"></i>
                    <span class="sidebar-label">Textures</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.inventory.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.inventory.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Stock Monitoring">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    <span class="sidebar-label">Stock Monitoring</span>
                </a>
            </li>
        </ul>

        <!-- Sales & Orders Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Sales & Orders</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.orders.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="All Orders">
                    <i class="bi bi-cart4 me-2"></i>
                    <span class="sidebar-label">All Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent sidebar-tooltip"
                    data-sidebar-tooltip="true" title="Customized Orders">
                    <i class="bi bi-palette me-2"></i>
                    <span class="sidebar-label">Customized Orders</span>
                </a>
            </li>
        </ul>

        <!-- Procurement Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Procurement</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.suppliers.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.suppliers.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Suppliers">
                    <i class="bi bi-truck me-2"></i>
                    <span class="sidebar-label">Suppliers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.purchase.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.purchase.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Purchase Orders">
                    <i class="bi bi-bag-check me-2"></i>
                    <span class="sidebar-label">Purchase Orders</span>
                </a>
            </li>
        </ul>

        <!-- Administration Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Administration</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.users*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Users">
                    <i class="bi bi-people me-2"></i>
                    <span class="sidebar-label">Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white hover-accent sidebar-tooltip"
                    data-sidebar-tooltip="true" title="Reports">
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    <span class="sidebar-label">Reports</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Fixed Footer -->
    <div class="flex-shrink-0 mt-2">
        <hr class="opacity-25 mt-0 mb-2">
        <ul class="nav nav-pills flex-column gap-1">
            <!-- Collapse Toggle -->
            <li class="sidebar-collapse-item">
                <a href="javascript:void(0)" class="nav-link text-white hover-accent sidebar-collapse-btn sidebar-tooltip"
                    data-sidebar-tooltip="true" title="Toggle Menu">
                    <i class="bi bi-chevron-double-left me-2 sidebar-collapse-icon"></i>
                    <span class="sidebar-label">Close Menu</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link text-white hover-accent sidebar-tooltip"
                    data-sidebar-tooltip="true" title="Settings">
                    <i class="bi bi-gear me-2"></i>
                    <span class="sidebar-label">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* ── Sidebar Base ── */
    .sidebar-inner { transition: padding 0.3s ease; }
    .sidebar-header {
        height: 65px; margin: -1rem -1rem 0 -1rem; padding: 0 1rem;
        display: flex; align-items: center; transition: all 0.3s ease;
    }
    .sidebar-header-divider { margin: 0 !important; transition: all 0.3s ease; }
    .sidebar-inner.sidebar-collapsed .sidebar-header { padding: 0; justify-content: center; }
    .sidebar-inner.sidebar-collapsed .sidebar-header-divider {
        display: block !important; align-self: stretch; width: auto !important; opacity: 0.25 !important;
    }
    .sidebar-inner .sidebar-label {
        font-size: 0.85rem; white-space: nowrap; overflow: hidden;
        transition: opacity 0.2s ease, width 0.2s ease;
    }
    .sidebar-inner .nav-link {
        display: flex; align-items: center; font-size: 0.85rem;
        padding: 0.6rem 0.75rem; margin-right: 0.5rem; transition: all 0.2s ease;
    }
    .sidebar-inner .nav-link i {
        flex-shrink: 0; width: 1.2em; text-align: center;
        font-size: 1.1rem; transition: margin 0.3s ease, font-size 0.3s ease;
    }
    .sidebar-brand { transition: justify-content 0.3s ease; }
    .sidebar-logo { flex-shrink: 0; transition: margin 0.3s ease; }
    .sidebar-section-title { transition: opacity 0.2s ease, height 0.2s ease, margin 0.2s ease; overflow: hidden; }

    /* ── Collapsed State ── */
    .sidebar-inner.sidebar-collapsed { padding: 1rem 0.5rem !important; align-items: center; }
    .sidebar-inner.sidebar-collapsed .sidebar-label { opacity: 0; width: 0; display: none !important; }
    .sidebar-inner.sidebar-collapsed .sidebar-section-title {
        height: 1px; background: rgba(255, 255, 255, 0.1); margin: 1.25rem 0.25rem !important;
        padding: 0 !important; color: transparent; overflow: hidden;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-section-title:first-of-type { display: none !important; }
    .sidebar-inner.sidebar-collapsed .nav:first-of-type { margin-top: 0.75rem !important; }
    .sidebar-inner.sidebar-collapsed .nav-link {
        justify-content: center; width: 42px; height: 42px; padding: 0; margin: 0 auto; border-radius: 0.5rem;
    }
    .sidebar-inner.sidebar-collapsed .nav-link i { margin-right: 0 !important; }
    .sidebar-inner.sidebar-collapsed .sidebar-brand { justify-content: center; }
    .sidebar-inner.sidebar-collapsed .sidebar-logo { margin-right: 0 !important; }
    .sidebar-inner.sidebar-collapsed .custom-scrollbar { padding-right: 0 !important; }

    /* ── Hover & Active Styles ── */
    .nav-link.hover-accent:hover { background-color: rgba(255, 197, 8, 0.1); color: #ffc508 !important; }
    .nav-link.hover-accent:hover i { color: #ffc508 !important; }
    .bg-accent { background-color: #ffc508 !important; }
    .text-primary { color: #0e2e45 !important; }

    /* ── Scrollbar ── */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
</style>

<script>
if (!window.adminSidebarInit) {
    window.adminSidebarInit = true;
    document.addEventListener('DOMContentLoaded', function() {
        const COLLAPSED_W = '70px';
        const EXPANDED_W = '280px';

        function toggleTooltips(inner, collapsed) {
            inner.querySelectorAll('.sidebar-tooltip').forEach(el => {
                const instance = bootstrap.Tooltip.getInstance(el);
                if (collapsed) {
                    if (!instance) new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'right', delay: {show:200, hide:0} });
                } else if (instance) {
                    instance.dispose();
                }
            });
        }

        document.querySelectorAll('.sidebar-collapse-btn').forEach(btn => {
            const inner = btn.closest('.sidebar-inner');
            const aside = inner.closest('aside');
            if (!aside) {
                btn.setAttribute('data-bs-dismiss', 'offcanvas');
                return;
            }

            const spacer = aside.parentElement.querySelector('.sidebar-spacer');
            const icon = btn.querySelector('.sidebar-collapse-icon');

            function apply(collapsed, animate) {
                if (!animate) {
                    aside.style.transition = spacer.style.transition = inner.style.transition = 'none';
                }
                const w = collapsed ? COLLAPSED_W : EXPANDED_W;
                aside.style.width = spacer.style.width = w;
                
                if (collapsed) {
                    inner.classList.add('sidebar-collapsed');
                    icon.classList.replace('bi-chevron-double-left', 'bi-chevron-double-right');
                } else {
                    inner.classList.remove('sidebar-collapsed');
                    icon.classList.replace('bi-chevron-double-right', 'bi-chevron-double-left');
                }
                
                toggleTooltips(inner, collapsed);
                localStorage.setItem('adminSidebarCollapsed', collapsed);

                if (!animate) {
                    requestAnimationFrame(() => {
                        aside.style.transition = spacer.style.transition = 'width 0.3s ease';
                        inner.style.transition = '';
                    });
                }
            }

            if (localStorage.getItem('adminSidebarCollapsed') === 'true') apply(true, false);
            
            btn.addEventListener('click', e => {
                e.preventDefault();
                apply(!inner.classList.contains('sidebar-collapsed'), true);
            });
        });
    });
}
</script>
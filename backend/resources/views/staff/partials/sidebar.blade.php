<div class="d-flex flex-column p-3 pb-1 text-white h-100 sidebar-inner" style="background-color: #05111a;">
    <!-- Fixed Header -->
    <div class="flex-shrink-0 sidebar-header d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="d-flex align-items-center text-white text-decoration-none sidebar-brand">
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
                <a href="{{ route('staff.dashboard') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.dashboard') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Dashboard">
                    <i class="bi bi-columns-gap me-2"></i>
                    <span class="sidebar-label">Dashboard</span>
                </a>
            </li>
        </ul>

        <!-- Sales & Orders Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Sales & Orders</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('staff.orders.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.orders.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Orders">
                    <i class="bi bi-cart4 me-2"></i>
                    <span class="sidebar-label">Orders</span>
                </a>
            </li>
        </ul>

        <!-- Inventory Control Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Inventory Control</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('staff.products.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.products.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Product">
                    <i class="bi bi-box-seam me-2"></i>
                    <span class="sidebar-label">Product</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.raw-materials.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.raw-materials.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Raw Materials">
                    <i class="bi bi-boxes me-2"></i>
                    <span class="sidebar-label">Raw Materials</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.textures.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.textures.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Textures">
                    <i class="bi bi-layers me-2"></i>
                    <span class="sidebar-label">Textures</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.inventory.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.inventory.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Inventory logs">
                    <i class="bi bi-clipboard-data me-2"></i>
                    <span class="sidebar-label">Inventory logs</span>
                </a>
            </li>
        </ul>

        <!-- Procurement Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Procurement</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('staff.purchase.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('staff.purchase.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Purchase Supply">
                    <i class="bi bi-bag-check me-2"></i>
                    <span class="sidebar-label">Purchase Supply</span>
                </a>
            </li>
        </ul>

        <!-- Administration Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Administration</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
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
        <ul class="nav nav-pills flex-column gap-1 mb-0">
            <li class="sidebar-collapse-item">
                <a href="javascript:void(0)" class="nav-link text-white hover-accent sidebar-collapse-btn sidebar-tooltip"
                    data-sidebar-tooltip="true" title="Toggle Menu">
                    <i class="bi bi-chevron-double-left me-2 sidebar-collapse-icon"></i>
                    <span class="sidebar-label">Close Menu</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* ── Sidebar Base ── */
    .sidebar-inner {
        transition: padding 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .sidebar-header {
        height: 65px; margin: -1rem -1rem 0 -1rem; padding: 0 1rem;
        display: flex; align-items: center;
        transition: padding 0.35s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.35s ease;
    }
    .sidebar-header-divider {
        margin: 0 0 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
        opacity: 1 !important;
        transition: opacity 0.3s ease;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-header { padding: 0; justify-content: center; }
    .sidebar-inner.sidebar-collapsed .sidebar-header-divider {
        display: block !important; align-self: stretch; width: auto !important;
    }

    /* Labels fade out only — max-width snaps so the icon's centered position never drifts */
    .sidebar-inner .sidebar-label {
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        max-width: 200px;
        min-width: 0;
        transition: opacity 0.25s ease;
    }

    .sidebar-inner .nav-link {
        display: flex; align-items: center; font-size: 0.85rem;
        padding: 0.6rem 0.75rem; margin-right: 0.5rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .sidebar-inner .nav-link i {
        flex-shrink: 0; width: 1.2em; text-align: center; font-size: 1.1rem;
        transition: color 0.2s ease;
    }
    .sidebar-brand { transition: justify-content 0.3s ease; }
    .sidebar-logo { flex-shrink: 0; transition: margin 0.3s ease; }

    /* Section titles never wrap to 2 lines */
    .sidebar-section-title {
        font-size: 0.68rem;
        letter-spacing: 0.06em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 0.5rem !important;
        margin-bottom: 0.25rem !important;
        transition: opacity 0.2s ease, height 0.35s ease, margin 0.35s ease, padding 0.35s ease, color 0.2s ease;
    }
    /* Tighter spacing between section nav lists */
    .sidebar-inner .nav.flex-column { margin-bottom: 0.5rem !important; }

    /* ── Collapsed State ── */
    /* Match the no-flash preload CSS in layout/app.blade.php so JS handover
       doesn't animate padding from 0.5rem back to the default 1rem. */
    .sidebar-inner.sidebar-collapsed {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
        align-items: center;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-label {
        opacity: 0;
        max-width: 0;
        margin-left: 0 !important;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-section-title {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0.5rem 0.25rem !important;
        padding: 0 !important;
        color: transparent;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-section-title:first-of-type { display: none !important; }
    .sidebar-inner.sidebar-collapsed .nav.flex-column { margin-bottom: 0.25rem !important; }
    /* Center the icon and brand logo within the narrow sidebar */
    .sidebar-inner.sidebar-collapsed .nav-link {
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        margin: 0 auto;
    }
    .sidebar-inner.sidebar-collapsed .nav-link i { margin-right: 0 !important; }
    .sidebar-inner.sidebar-collapsed .sidebar-brand { justify-content: center; }
    .sidebar-inner.sidebar-collapsed .sidebar-logo { margin-right: 0 !important; }

    /* ── Hover ── */
    .nav-link.hover-accent:hover {
        background-color: rgba(255, 255, 255, 0.06);
        color: #ffc508 !important;
    }
    .nav-link.hover-accent:hover i { color: #ffc508 !important; }

    .bg-accent { background-color: #ffc508 !important; }
    .text-primary { color: #0e2e45 !important; }

    /* ── Scrollbar ── */
    /* Prevent horizontal scrollbar from appearing in collapsed state.
       (overflow-y: auto implicitly sets overflow-x: auto per CSS spec, so we clamp it explicitly.) */
    .sidebar-inner .custom-scrollbar { overflow-x: hidden; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
</style>

<script>
if (!window.staffSidebarInit) {
    window.staffSidebarInit = true;
    document.addEventListener('DOMContentLoaded', function() {
        const COLLAPSED_W = '76px';
        const EXPANDED_W = '280px';

        function toggleTooltips(inner, collapsed) {
            inner.querySelectorAll('.sidebar-tooltip').forEach(el => {
                const instance = bootstrap.Tooltip.getInstance(el);
                if (collapsed) {
                    if (!instance) new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'right', delay: {show:200, hide:0}, container: 'body' });
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

            aside.style.transition = spacer.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';

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
                localStorage.setItem('staffSidebarCollapsed', collapsed);

                if (!animate) {
                    requestAnimationFrame(() => {
                        aside.style.transition = spacer.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                        inner.style.transition = '';
                    });
                }
            }

            if (localStorage.getItem('staffSidebarCollapsed') === 'true') apply(true, false);

            // Once JS has taken over, drop the no-flash preload class so future toggles animate.
            document.documentElement.classList.remove('sidebar-preload-collapsed');

            btn.addEventListener('click', e => {
                e.preventDefault();
                apply(!inner.classList.contains('sidebar-collapsed'), true);
            });
        });
    });
}
</script>
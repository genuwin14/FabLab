<div class="d-flex flex-column p-3 pb-1 text-white h-100 sidebar-inner" style="background-color: #05111a;">
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
                <a href="{{ route('admin.colors.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.colors.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Colors">
                    <i class="bi bi-palette me-2"></i>
                    <span class="sidebar-label">Colors</span>
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
            <li class="nav-item">
                <a href="{{ route('admin.equipment.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.equipment.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Equipment">
                    <i class="bi bi-tools me-2"></i>
                    <span class="sidebar-label">Machinery & Equipment</span>
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
                <a href="{{ route('admin.sales.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.sales.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Sales">
                    <i class="bi bi-cash-coin me-2"></i>
                    <span class="sidebar-label">Sales</span>
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
                <a href="{{ route('admin.customization-pricing.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('admin.customization-pricing.*') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Customization Pricing">
                    <i class="bi bi-cash-stack me-2"></i>
                    <span class="sidebar-label">Customization Pricing</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#reportsSubmenu" data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}"
                    class="nav-link sidebar-tooltip sidebar-has-submenu {{ request()->routeIs('admin.reports.*') ? 'text-accent' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Reports">
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    <span class="sidebar-label">Reports</span>
                    <i class="bi bi-chevron-down sidebar-caret sidebar-label ms-auto small"></i>
                </a>
                <div class="collapse sidebar-submenu {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reportsSubmenu">
                    <ul class="nav nav-pills flex-column gap-1 mt-1">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.materials') }}"
                                class="nav-link sidebar-tooltip sidebar-submenu-link {{ request()->routeIs('admin.reports.materials') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                                data-sidebar-tooltip="true" title="Materials">
                                <i class="bi bi-clipboard-data me-2"></i>
                                <span class="sidebar-label">Materials</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.equipment') }}"
                                class="nav-link sidebar-tooltip sidebar-submenu-link {{ request()->routeIs('admin.reports.equipment') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                                data-sidebar-tooltip="true" title="Machinery & Equipment">
                                <i class="bi bi-tools me-2"></i>
                                <span class="sidebar-label">Machinery & Equipment</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <!-- Fixed Footer -->
    <div class="flex-shrink-0 mt-2">
        <hr class="opacity-25 mt-0 mb-2">
        <ul class="nav nav-pills flex-column gap-1 mb-0">
            <!-- Collapse Toggle -->
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
    .text-accent { color: #ffc508 !important; }
    .text-accent i { color: #ffc508 !important; }

    /* Active nav item: soft gold wash + gold text instead of a solid gold block */
    .sidebar-inner .nav-link.active {
        background-color: rgba(255, 197, 8, 0.16) !important;
        color: #ffc508 !important;
    }
    .sidebar-inner .nav-link.active i { color: #ffc508 !important; }

    /* ── Submenu (collapsible nav group) ── */
    .sidebar-has-submenu .sidebar-caret { transition: transform 0.25s ease; }
    .sidebar-has-submenu[aria-expanded="true"] .sidebar-caret { transform: rotate(180deg); }
    .sidebar-submenu .nav { margin-bottom: 0 !important; }
    .sidebar-submenu-link {
        padding-left: 2.1rem !important;
        font-size: 0.82rem !important;
    }
    .sidebar-submenu-link i { font-size: 1rem !important; }

    /* ── Collapsed sidebar: submenu becomes a hover fly-out next to the icon ── */
    .sidebar-inner.sidebar-collapsed .sidebar-caret { display: none !important; }
    .sidebar-inner.sidebar-collapsed .nav-item { position: relative; }
    .sidebar-inner.sidebar-collapsed .sidebar-submenu {
        display: block !important;          /* override Bootstrap .collapse */
        height: auto !important;
        /* position:fixed escapes the sidebar's overflow clipping; JS sets the coords */
        position: fixed;
        top: var(--flyout-top, 70px);
        left: var(--flyout-left, 84px);
        min-width: 215px;
        background-color: #05111a;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0.65rem;
        padding: 0.4rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.45);
        opacity: 0;
        visibility: hidden;
        transform: translateX(-6px);
        transition: opacity 0.18s ease, transform 0.18s ease, visibility 0s linear 0.18s;
        z-index: 1050;
    }
    /* Invisible bridge across the gap so hover doesn't drop */
    .sidebar-inner.sidebar-collapsed .sidebar-submenu::before {
        content: "";
        position: absolute;
        left: -0.7rem;
        top: 0;
        width: 0.7rem;
        height: 100%;
    }
    .sidebar-inner.sidebar-collapsed .nav-item:hover > .sidebar-submenu {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-submenu .nav {
        margin-bottom: 0 !important;
        gap: 0.15rem !important;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-submenu .nav-link {
        justify-content: flex-start !important;
        width: auto !important;
        height: auto !important;
        padding: 0.5rem 0.7rem !important;
        margin: 0 !important;
    }
    .sidebar-inner.sidebar-collapsed .sidebar-submenu .nav-link i { margin-right: 0.5rem !important; }
    .sidebar-inner.sidebar-collapsed .sidebar-submenu .sidebar-label {
        opacity: 1 !important;
        max-width: 200px !important;
        margin-left: 0 !important;
    }

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
if (!window.adminSidebarInit) {
    window.adminSidebarInit = true;
    document.addEventListener('DOMContentLoaded', function() {
        const COLLAPSED_W = '76px';
        const EXPANDED_W = '280px';

        function toggleTooltips(inner, collapsed) {
            // Items tied to a fly-out submenu (the group toggle and its links) don't need a
            // tooltip — the fly-out itself shows the labels.
            inner.querySelectorAll('.sidebar-tooltip:not(.sidebar-has-submenu):not(.sidebar-submenu-link)').forEach(el => {
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
                        aside.style.transition = spacer.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                        inner.style.transition = '';
                    });
                }
            }

            if (localStorage.getItem('adminSidebarCollapsed') === 'true') apply(true, false);

            // Once JS has taken over, drop the no-flash preload class so future toggles animate.
            document.documentElement.classList.remove('sidebar-preload-collapsed');

            btn.addEventListener('click', e => {
                e.preventDefault();
                apply(!inner.classList.contains('sidebar-collapsed'), true);
            });
        });

        // ── Fly-out positioning for collapsible submenu groups (e.g. Reports) ──
        // On the expanded sidebar the group opens and closes only on click, which
        // Bootstrap's data-bs-toggle="collapse" handles on its own. Collapsed to
        // icons there is no width to expand into, so CSS reveals the submenu as a
        // hover fly-out and the only job left here is placing it — position:fixed
        // needs viewport coordinates JS has to measure.
        document.querySelectorAll('.sidebar-has-submenu').forEach(toggle => {
            const li = toggle.closest('.nav-item');
            const target = document.querySelector(toggle.getAttribute('href'));
            const inner = toggle.closest('.sidebar-inner');
            const scroller = toggle.closest('.custom-scrollbar');
            if (!li || !target) return;

            li.addEventListener('mouseenter', () => {
                if (!inner || !inner.classList.contains('sidebar-collapsed')) return;
                const r = toggle.getBoundingClientRect();
                target.style.setProperty('--flyout-top', Math.max(8, r.top) + 'px');
                target.style.setProperty('--flyout-left', (r.right + 10) + 'px');
            });

            // These groups sit at the bottom of the menu, so an opened submenu can
            // land below the fold. Scroll the menu down by however far it overhangs
            // and no further, leaving the rest of the sidebar where the user left it.
            function revealSubmenu() {
                if (!scroller || (inner && inner.classList.contains('sidebar-collapsed'))) return;
                const overhang = target.getBoundingClientRect().bottom - scroller.getBoundingClientRect().bottom;
                if (overhang > 0) scroller.scrollBy({ top: overhang + 8, behavior: 'smooth' });
            }

            // Fires after the expand transition, when the submenu's height is final.
            target.addEventListener('shown.bs.collapse', revealSubmenu);

            // Already open on arrival (a Reports page renders it expanded).
            if (target.classList.contains('show')) requestAnimationFrame(revealSubmenu);
        });
    });
}
</script>
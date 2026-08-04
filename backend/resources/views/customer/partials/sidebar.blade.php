<div class="d-flex flex-column p-3 pb-1 text-white h-100 sidebar-inner" style="background-color: #05111a;">
    <!-- Fixed Header -->
    <div class="flex-shrink-0 sidebar-header d-flex align-items-center">
        <a href="{{ route('customer.shop') }}" class="d-flex align-items-center text-white text-decoration-none sidebar-brand">
            <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FABLAB" width="40" height="40" class="me-2 sidebar-logo">
            <span class="fs-4 fw-bold tracking-tight sidebar-label">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>
    </div>
    <hr class="sidebar-header-divider opacity-25 mt-0 mb-4">

    <!-- Scrollable Menu Content -->
    <div class="flex-grow-1 overflow-y-auto pe-1 custom-scrollbar">
        <!-- Explore Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Explore</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.shop') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('customer.shop') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Shop Products">
                    <i class="bi bi-shop me-2"></i>
                    <span class="sidebar-label">Shop Products</span>
                </a>
            </li>
        </ul>

        <!-- Design Center Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Design Center</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.customize.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('customer.customize.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Customize Product">
                    <i class="bi bi-palette me-2"></i>
                    <span class="sidebar-label">Customize Product</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('customer.customize.my-designs') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('customer.customize.my-designs') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="Personal Design">
                    <i class="bi bi-vector-pen me-2"></i>
                    <span class="sidebar-label">Personal Design</span>
                </a>
            </li>
        </ul>

        <!-- Transactions Section -->
        <div class="mb-2 text-uppercase text-white-50 small fw-bold sidebar-section-title">Transactions</div>
        <ul class="nav nav-pills flex-column mb-3 gap-1">
            <li class="nav-item">
                <a href="{{ route('customer.orders.index') }}"
                    class="nav-link sidebar-tooltip {{ request()->routeIs('customer.orders.index') ? 'active bg-accent text-primary fw-bold' : 'text-white hover-accent' }}"
                    data-sidebar-tooltip="true" title="My Orders">
                    <i class="bi bi-bag-check me-2"></i>
                    <span class="sidebar-label">My Orders</span>
                    @if(isset($inProgressCount) && $inProgressCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill smaller sidebar-badge ms-auto">{{ $inProgressCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <!-- Order Status / CTA Card -->
        <div class="sidebar-cta mt-1 mb-3">
            @if($latestActiveOrder)
                @php
                    $statusLabel = match($latestActiveOrder->status) {
                        'pending' => 'Awaiting confirmation',
                        'processing' => 'In production',
                        default => ucfirst($latestActiveOrder->status),
                    };
                    $items = $latestActiveOrder->orderItems;
                    $firstItem = $items->first();
                    $totalQty = (int) $items->sum('quantity');
                    $extraTypes = max(0, $items->count() - 1);
                    $thumb = null;
                    $primaryName = '#' . $latestActiveOrder->order_number;
                    if ($firstItem) {
                        $thumb = ($firstItem->customDesign && $firstItem->customDesign->snapshot)
                            ? $firstItem->customDesign->snapshot
                            : ($firstItem->product->image_url ?? null);
                        $primaryName = optional($firstItem->product)->name ?? $primaryName;
                    }
                @endphp
                <a href="{{ route('customer.orders.index') }}" class="sidebar-cta-card sidebar-cta-active"
                   title="Order #{{ $latestActiveOrder->order_number }}">
                    <div class="d-flex align-items-center mb-2">
                        <span class="sidebar-cta-pulse me-2"></span>
                        <span class="sidebar-cta-label">{{ $statusLabel }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 sidebar-cta-summary">
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="" class="sidebar-cta-thumb">
                        @else
                            <div class="sidebar-cta-thumb sidebar-cta-thumb-fallback">
                                <i class="bi bi-bag-check"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <div class="sidebar-cta-item-name">
                                {{ $primaryName }}@if($extraTypes > 0)<span class="sidebar-cta-more"> +{{ $extraTypes }}</span>@endif
                            </div>
                            <div class="sidebar-cta-item-meta">
                                {{ $totalQty }} {{ $totalQty === 1 ? 'item' : 'items' }} · ₱{{ number_format((float) $latestActiveOrder->total_amount, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="sidebar-cta-action mt-2">
                        <span>View order</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </div>
                </a>
            @else
                <a href="{{ route('customer.customize.index') }}" class="sidebar-cta-card sidebar-cta-empty">
                    <div class="sidebar-cta-icon mb-1"><i class="bi bi-stars"></i></div>
                    <div class="sidebar-cta-title">Bring your idea to life</div>
                    <div class="sidebar-cta-action mt-2">
                        <span>Start designing</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </div>
                </a>
            @endif
        </div>
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
        height: 65px;
        margin: -1rem -1rem 0 -1rem;
        padding: 0 1rem;
        display: flex;
        align-items: center;
        transition: padding 0.35s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.35s ease;
    }

    .sidebar-header-divider {
        margin: 0 0 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
        opacity: 1 !important;
        transition: opacity 0.3s ease;
    }

    .sidebar-inner.sidebar-collapsed .sidebar-header {
        padding: 0;
        justify-content: center;
    }

    .sidebar-inner.sidebar-collapsed .sidebar-header-divider {
        display: block !important;
        align-self: stretch;
        width: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
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
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        padding: 0.6rem 0.75rem;
        margin-right: 0.5rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .sidebar-inner .nav-link i {
        flex-shrink: 0;
        width: 1.2em;
        text-align: center;
        font-size: 1.1rem;
        transition: color 0.2s ease;
    }

    .sidebar-brand {
        transition: justify-content 0.3s ease;
    }

    .sidebar-logo {
        flex-shrink: 0;
        transition: margin 0.3s ease;
    }

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

    /* Badge sits flush right when expanded; in collapsed state it overlays
       the icon as a notification dot in the top-right corner. */
    .sidebar-inner .sidebar-badge {
        transition: opacity 0.25s ease;
    }

    .sidebar-inner.sidebar-collapsed .nav-link {
        position: relative;
    }

    .sidebar-inner.sidebar-collapsed .sidebar-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        margin: 0 !important;
        font-size: 0.6rem;
        padding: 0.15em 0.4em;
        line-height: 1;
        min-width: 1rem;
        text-align: center;
        box-shadow: 0 0 0 2px #05111a;
    }

    .sidebar-inner.sidebar-collapsed .sidebar-section-title {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0.5rem 0.25rem !important;
        padding: 0 !important;
        color: transparent;
    }

    .sidebar-inner.sidebar-collapsed .sidebar-section-title:first-of-type {
        display: none !important;
    }

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

    .nav-link.hover-accent:hover i {
        color: #ffc508 !important;
    }

    .bg-accent {
        background-color: #ffc508 !important;
    }

    .text-primary {
        color: #0e2e45 !important;
    }

    /* Active nav item: soft gold wash + gold text instead of a solid gold block */
    .sidebar-inner .nav-link.active {
        background-color: rgba(255, 197, 8, 0.16) !important;
        color: #ffc508 !important;
    }
    .sidebar-inner .nav-link.active i { color: #ffc508 !important; }

    /* ── Scrollbar ── */
    /* Prevent horizontal scrollbar from appearing in collapsed state.
       (overflow-y: auto implicitly sets overflow-x: auto per CSS spec, so we clamp it explicitly.) */
    .sidebar-inner .custom-scrollbar { overflow-x: hidden; }
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* ── Sidebar CTA Card ── */
    .sidebar-cta-card {
        display: block;
        padding: 0.75rem;
        border-radius: 0.625rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(135deg, rgba(255, 197, 8, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
        color: #fff;
        text-decoration: none;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease;
    }

    .sidebar-cta-card:hover {
        border-color: rgba(255, 197, 8, 0.45);
        background: linear-gradient(135deg, rgba(255, 197, 8, 0.16) 0%, rgba(255, 255, 255, 0.04) 100%);
        color: #fff;
        transform: translateY(-1px);
    }

    .sidebar-cta-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ffc508;
        display: inline-block;
        flex-shrink: 0;
        animation: sidebar-cta-pulse 2s infinite;
    }

    @keyframes sidebar-cta-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(255, 197, 8, 0.55); }
        70%  { box-shadow: 0 0 0 8px rgba(255, 197, 8, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 197, 8, 0); }
    }

    .sidebar-cta-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #ffc508;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-cta-thumb {
        width: 36px;
        height: 36px;
        border-radius: 0.375rem;
        object-fit: cover;
        background-color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    .sidebar-cta-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 197, 8, 0.12);
        border-color: rgba(255, 197, 8, 0.25);
        color: #ffc508;
        font-size: 1rem;
    }

    .sidebar-cta-item-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: #fff;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-cta-more {
        font-size: 0.7rem;
        font-weight: 600;
        color: #ffc508;
    }

    .sidebar-cta-item-meta {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.55);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-cta-icon {
        font-size: 1.25rem;
        color: #ffc508;
        line-height: 1;
    }

    .sidebar-cta-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #fff;
        line-height: 1.3;
    }

    .sidebar-cta-action {
        display: flex;
        align-items: center;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255, 255, 255, 0.55);
        transition: color 0.2s ease;
    }

    .sidebar-cta-action i {
        font-size: 1rem;
        margin-left: 0.15rem;
        transition: transform 0.2s ease;
    }

    .sidebar-cta-card:hover .sidebar-cta-action {
        color: #ffc508;
    }

    .sidebar-cta-card:hover .sidebar-cta-action i {
        transform: translateX(2px);
    }

    /* Hide CTA card when sidebar is collapsed (76px is too narrow) */
    .sidebar-inner.sidebar-collapsed .sidebar-cta {
        display: none;
    }
</style>

<script>
    if (!window.sidebarScriptInit) {
        window.sidebarScriptInit = true;
        
        document.addEventListener('DOMContentLoaded', function () {
            var COLLAPSED_W = '76px';
            var EXPANDED_W  = '280px';

            function toggleTooltips(inner, collapsed) {
                var tooltipEls = inner.querySelectorAll('.sidebar-tooltip');
                tooltipEls.forEach(function (el) {
                    var instance = bootstrap.Tooltip.getInstance(el);
                    if (collapsed) {
                        if (!instance) {
                            new bootstrap.Tooltip(el, {
                                trigger: 'hover',
                                placement: 'right',
                                delay: { show: 200, hide: 0 },
                                container: 'body'
                            });
                        }
                    } else {
                        if (instance) {
                            instance.dispose();
                        }
                    }
                });
            }

            document.querySelectorAll('.sidebar-collapse-btn').forEach(function (btn) {
                var inner = btn.closest('.sidebar-inner');
                var aside = inner.closest('aside');

                if (!aside) {
                    btn.setAttribute('data-bs-dismiss', 'offcanvas');
                    return;
                }

                var spacer = aside.parentElement.querySelector('.sidebar-spacer');
                var icon   = btn.querySelector('.sidebar-collapse-icon');

                aside.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                if (spacer) spacer.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';

                aside.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                if (spacer) spacer.style.transition = 'width 0.35s cubic-bezier(0.4, 0, 0.2, 1)';

                function apply(collapsed, animate) {
                    if (!animate) {
                        aside.style.transition = 'none';
                        if (spacer) spacer.style.transition = 'none';
                        inner.style.transition = 'none';
                    }

                    var width = collapsed ? COLLAPSED_W : EXPANDED_W;
                    aside.style.width = width;
                    if (spacer) spacer.style.width = width;

                    if (collapsed) {
                        inner.classList.add('sidebar-collapsed');
                        icon.classList.replace('bi-chevron-double-left', 'bi-chevron-double-right');
                    } else {
                        inner.classList.remove('sidebar-collapsed');
                        icon.classList.replace('bi-chevron-double-right', 'bi-chevron-double-left');
                    }

                    toggleTooltips(inner, collapsed);
                    localStorage.setItem('customerSidebarCollapsed', collapsed);

                    if (!animate) {
                        requestAnimationFrame(function () {
                            aside.style.transition = 'width 0.3s ease';
                            if (spacer) spacer.style.transition = 'width 0.3s ease';
                            inner.style.transition = '';
                        });
                    }
                }

                if (localStorage.getItem('customerSidebarCollapsed') === 'true') {
                    apply(true, false);
                }

                // Once JS has taken over, drop the no-flash preload class so future toggles animate.
                document.documentElement.classList.remove('sidebar-preload-collapsed');

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var isCollapsed = inner.classList.contains('sidebar-collapsed');
                    apply(!isCollapsed, true);
                });
            });
        });
    }
</script>
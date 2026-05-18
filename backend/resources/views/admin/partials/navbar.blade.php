<nav class="navbar navbar-expand-lg py-2 custom-navbar">
    <div class="container-fluid px-3 px-md-4">
        <!-- Mobile Sidebar Toggle -->
        <button class="navbar-action-btn d-md-none me-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Page Title -->
        <div class="navbar-title-wrap me-auto">
            <div class="d-flex align-items-center gap-2 navbar-title-inner">
                <span class="page-eyebrow d-none d-md-inline">Admin</span>
                <span class="page-eyebrow-divider d-none d-md-inline">/</span>
                <h6 class="page-title fw-bold mb-0 text-white text-truncate">
                    @if(request()->routeIs('admin.dashboard'))
                        Dashboard
                    @elseif(request()->routeIs('admin.products*'))
                        Product Management
                    @elseif(request()->routeIs('admin.textures*'))
                        Texture Management
                    @elseif(request()->routeIs('admin.raw-materials*'))
                        Raw Materials
                    @elseif(request()->routeIs('admin.categories*'))
                        Category Management
                    @elseif(request()->routeIs('admin.orders*'))
                        Order Management
                    @elseif(request()->routeIs('admin.sales*'))
                        Sales
                    @elseif(request()->routeIs('admin.suppliers*'))
                        Supplier Management
                    @elseif(request()->routeIs('admin.purchase*'))
                        Purchase Orders
                    @elseif(request()->routeIs('admin.inventory*'))
                        Stock Monitoring
                    @elseif(request()->routeIs('admin.equipment*'))
                        Machinery & Equipment
                    @elseif(request()->routeIs('admin.reports.materials'))
                        Reports <span class="page-title-sub">/ Materials</span>
                    @elseif(request()->routeIs('admin.reports.equipment'))
                        Reports <span class="page-title-sub">/ Machinery & Equipment</span>
                    @elseif(request()->routeIs('admin.reports*'))
                        Reports
                    @elseif(request()->routeIs('admin.users*'))
                        User Management
                    @elseif(request()->routeIs('notifications.index'))
                        Notifications
                    @elseif(request()->routeIs('admin.settings*'))
                        Settings
                    @else
                        Admin Panel
                    @endif
                </h6>
            </div>
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- Clock (Desktop Only) -->
            <div class="d-none d-lg-flex align-items-center clock-wrapper">
                <i class="bi bi-clock me-2 text-white-50 small"></i>
                <span id="live-clock" class="text-white-50 small fw-medium"></span>
            </div>

            <!-- Notifications Dropdown -->
            @include('partials.notification-bell')

            <!-- User Dropdown -->
            <div class="dropdown ms-1">
                <button type="button"
                    class="d-flex align-items-center user-dropdown-btn"
                    id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->photo ? (Str::startsWith(Auth::user()->photo, 'http') ? Auth::user()->photo : asset('storage/' . Auth::user()->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->fullname) . '&background=0e2e45&color=fff' }}"
                        alt="{{ Auth::user()->fullname }}" class="user-dropdown-avatar rounded-circle object-fit-cover">
                    <div class="d-none d-md-block ms-2 me-2 text-start">
                        <div class="text-white fw-semibold small text-truncate" style="max-width: 140px;">{{ Auth::user()->fullname }}</div>
                        <div class="text-white-50" style="font-size: 0.65rem; line-height: 1;">Administrator</div>
                    </div>
                    <i class="bi bi-chevron-down text-white-50 small d-none d-md-inline me-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu shadow-lg border-0 mt-2"
                    aria-labelledby="dropdownUser1">
                    <li class="user-card-header">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ Auth::user()->photo ? (Str::startsWith(Auth::user()->photo, 'http') ? Auth::user()->photo : asset('storage/' . Auth::user()->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->fullname) . '&background=ffc508&color=0e2e45' }}"
                                alt="" width="44" height="44" class="rounded-circle object-fit-cover flex-shrink-0">
                            <div class="overflow-hidden">
                                <div class="text-white fw-bold small text-truncate">{{ Auth::user()->fullname }}</div>
                                <div class="text-white-50 text-truncate" style="font-size: 0.72rem;">{{ Auth::user()->email }}</div>
                                <span class="role-badge mt-1">
                                    <i class="bi bi-shield-check me-1"></i>Admin
                                </span>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown-section-label">Account</li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal"
                            data-bs-target="#profileModal">
                            <i class="bi bi-person-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                            @php $ddUnread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0; @endphp
                            @if($ddUnread > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $ddUnread > 99 ? '99+' : $ddUnread }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button"
                            class="dropdown-item dropdown-item-danger d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
    function updateClock() {
        const clockEl = document.getElementById('live-clock');
        if (!clockEl) return;
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        clockEl.textContent = `${dateStr} · ${timeStr}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>

<style>
    .custom-navbar {
        background-color: #05111a;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        height: 65px;
        z-index: 1042 !important;
    }

    .page-eyebrow {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 197, 8, 0.85);
    }
    .page-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

    /* Long titles ellipsize instead of shoving the navbar controls
       off-screen. min-width:0 lets these flex items shrink below their
       content width so .text-truncate's ellipsis actually engages. */
    .navbar-title-wrap { min-width: 0; overflow: hidden; }
    .navbar-title-inner { min-width: 0; }
    .page-title {
        font-size: 0.95rem;
        letter-spacing: 0.01em;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Fixed-height bar stays a single row at every width. */
    .custom-navbar > .container-fluid { flex-wrap: nowrap; }

    @media (max-width: 575.98px) {
        .page-title { font-size: 0.85rem; }
        .page-title .page-title-sub { display: none; }
    }

    .clock-wrapper {
        padding-right: 0.85rem;
        margin-right: 0.35rem;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Action buttons (icons in navbar) */
    .navbar-action-btn {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.85);
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .navbar-action-btn:hover,
    .navbar-action-btn[aria-expanded="true"] {
        background: rgba(255, 197, 8, 0.08);
        border-color: rgba(255, 197, 8, 0.3);
        color: #ffc508;
    }

    .action-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 0.6rem;
        font-weight: 700;
        border-radius: 999px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        line-height: 16px;
        text-align: center;
        color: #fff;
        border: 2px solid #05111a;
    }
    .action-badge-dot {
        width: 9px;
        height: 9px;
        min-width: 9px;
        padding: 0;
    }

    /* User dropdown trigger */
    .user-dropdown-btn {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        padding: 3px 8px 3px 3px;
        cursor: pointer;
        transition: background 0.2s ease, border-color 0.2s ease;
        text-decoration: none;
    }
    .user-dropdown-btn:hover,
    .user-dropdown-btn[aria-expanded="true"] {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 197, 8, 0.3);
    }
    .user-dropdown-avatar {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }

    /* ── Dropdown Menus (shared) ── */
    .notification-dropdown,
    .user-dropdown-menu {
        background-color: #05111a;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 14px;
        overflow: hidden;
        padding: 0;
        min-width: 280px;
    }
    .notification-dropdown { min-width: 340px; }

    /* Mobile: a right-anchored 340px menu overflows narrow phones and
       triggers page-wide horizontal scroll. Drop the fixed min-width
       and cap to the viewport so the menu always stays on screen. */
    @media (max-width: 575.98px) {
        .notification-dropdown,
        .user-dropdown-menu {
            min-width: 0;
            width: calc(100vw - 1.25rem);
            max-width: 360px;
        }
        .notification-list { max-height: 60vh; }
    }

    .dropdown-header-bar {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.95);
        font-weight: 600;
        font-size: 0.82rem;
    }
    .dropdown-header-bar a:hover { color: #ffc508 !important; }

    .dropdown-footer-link {
        display: block;
        text-align: center;
        padding: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.8rem;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .dropdown-footer-link:hover {
        background: rgba(255, 197, 8, 0.06);
        color: #ffc508;
    }

    .notification-empty-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.04);
        color: rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    /* User card header inside dropdown */
    .user-card-header {
        padding: 14px 16px;
        background: linear-gradient(135deg, rgba(14, 46, 69, 0.6), rgba(255, 197, 8, 0.04));
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .role-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(255, 197, 8, 0.12);
        color: #ffc508;
        border: 1px solid rgba(255, 197, 8, 0.2);
        line-height: 1.3;
    }

    .dropdown-section-label {
        padding: 10px 16px 4px;
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    /* Dropdown items inside menus */
    .user-dropdown-menu .dropdown-item,
    .notification-dropdown .dropdown-item {
        color: rgba(255, 255, 255, 0.85);
        padding: 9px 16px;
        font-size: 0.83rem;
        background: transparent;
        border: 0;
        transition: background 0.2s ease, color 0.2s ease, padding-left 0.2s ease;
    }
    .user-dropdown-menu .dropdown-item i,
    .notification-dropdown .dropdown-item i {
        font-size: 0.95rem;
        width: 16px;
        text-align: center;
        color: rgba(255, 255, 255, 0.6);
        transition: color 0.2s ease;
    }
    .user-dropdown-menu .dropdown-item:hover,
    .user-dropdown-menu .dropdown-item:focus,
    .notification-dropdown .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ffc508;
    }
    .user-dropdown-menu .dropdown-item:hover i,
    .notification-dropdown .dropdown-item:hover i { color: #ffc508; }

    .dropdown-item-danger { color: #ff8088 !important; }
    .dropdown-item-danger i { color: #ff8088 !important; }
    .dropdown-item-danger:hover {
        background: rgba(220, 53, 69, 0.1) !important;
        color: #ffb0b6 !important;
    }
    .dropdown-item-danger:hover i { color: #ffb0b6 !important; }

    .user-dropdown-menu .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.08);
        margin: 6px 0;
    }

    /* Sub-segment in the page title (e.g. "Reports / Materials") */
    .page-title .page-title-sub {
        color: rgba(255, 255, 255, 0.45);
        font-weight: 400;
    }
</style>

@include('admin.profile.modal-profile')

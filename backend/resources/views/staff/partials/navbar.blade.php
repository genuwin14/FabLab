<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-2">
    <div class="container-fluid">
        <!-- Sidebar Toggle (Mobile Only) -->
        <button class="btn btn-link text-dark d-md-none me-2 p-0" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#staffSidebarOffcanvas" aria-controls="staffSidebarOffcanvas">
            <i class="bi bi-list fs-2"></i>
        </button>

        <!-- Page Title (Responsive) -->
        <div class="me-auto overflow-hidden text-nowrap">
            <h5 class="fw-bold mb-0 text-dark">
                @if(request()->routeIs('staff.dashboard')) Staff Dashboard
                @else Staff Panel @endif
            </h5>
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <!-- Clock (Desktop Only) -->
            <div class="d-none d-lg-block border-end pe-3 me-1">
                <span id="live-clock" class="text-secondary small fw-medium"></span>
            </div>

            <!-- Notifications -->
            <a class="nav-link position-relative text-dark p-1" href="#">
                <i class="bi bi-bell fs-5"></i>
                <span
                    class="position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </a>

            <!-- User Dropdown -->
            <div class="dropdown">
                <a href="#"
                    class="d-flex align-items-center text-dark text-decoration-none border rounded-pill p-1 p-md-2 user-dropdown-btn bg-white"
                    id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->photo ? (Str::startsWith(Auth::user()->photo, 'http') ? Auth::user()->photo : asset('storage/' . Auth::user()->photo)) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->fullname) . '&background=0e2e45&color=fff' }}"
                        alt="" width="30" height="30" class="rounded-circle object-fit-cover">
                    <span
                        class="d-none d-md-inline ms-2 me-1 fw-bold small text-nowrap">{{ Auth::user()->fullname }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-2 mt-2 custom-dropdown-menu"
                    aria-labelledby="dropdownUser1" style="min-width: 180px;">
                    <li>
                        <h6
                            class="dropdown-header text-white-50 d-md-none border-bottom border-white border-opacity-10 mb-2 pb-2">
                            {{ Auth::user()->fullname }}
                        </h6>
                    </li>
                    <li>
                        <a class="dropdown-item text-white hover-accent small py-2" href="#" data-bs-toggle="modal"
                            data-bs-target="#profileModal">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider border-white border-opacity-10 my-1">
                    </li>
                    <li>
                        <button type="button"
                            class="dropdown-item text-danger hover-accent small py-2 w-100 text-start border-0 bg-transparent"
                            data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right me-2"></i> Sign out
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
        const optionsDate = { month: 'long', day: 'numeric', year: 'numeric' };
        const optionsTime = { hour: 'numeric', minute: '2-digit', hour12: true };

        const dateStr = now.toLocaleDateString('en-US', optionsDate);
        const timeStr = now.toLocaleTimeString('en-US', optionsTime);

        clockEl.textContent = `${dateStr} | ${timeStr}`;
    }

    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
</script>

<style>
    .user-dropdown-btn {
        transition: all 0.2s ease;
        border-color: #dee2e6 !important;
    }

    .user-dropdown-btn:hover,
    .user-dropdown-btn[aria-expanded="true"] {
        background-color: #f8f9fa;
        border-color: #ffc508 !important;
        /* Gold border */
        box-shadow: 0 0 0 2px rgba(255, 197, 8, 0.1);
    }

    .custom-dropdown-menu {
        background-color: rgba(14, 46, 69, 0.98);
        backdrop-filter: blur(10px);
    }

    .dropdown-item.hover-accent:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #ffc508 !important;
    }

    .navbar {
        height: 65px;
        z-index: 1042 !important;
    }
</style>

@include('staff.profile.modal-profile')
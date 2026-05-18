@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid" style="max-width: 880px;">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark mb-1">Settings</h4>
                        <p class="text-muted small mb-0">Manage how your admin account behaves across FABLAB.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">
                        @csrf
                        @method('PUT')

                        <div class="card border-0 shadow-sm settings-card">
                            <div class="card-header bg-white border-0 pt-3 pt-md-4 pb-2 px-3 px-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="settings-section-icon"><i class="bi bi-bell-fill"></i></span>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Notifications</h6>
                                        <small class="text-muted">Control how you receive in-app notifications.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body px-3 px-md-4 pb-3 pb-md-4">
                                <div class="setting-row d-flex justify-content-between align-items-center gap-3 py-3 border-top">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">Enable notifications</div>
                                        <small class="text-muted">Receive order, inventory, procurement, and account alerts in the navbar bell and notifications page.</small>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input type="hidden" name="notifications_enabled" value="0">
                                        <input class="form-check-input settings-switch" type="checkbox" role="switch"
                                            id="adminNotificationsEnabled" name="notifications_enabled" value="1"
                                            {{ $user->notifications_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label visually-hidden" for="adminNotificationsEnabled">Enable notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-check2-circle me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <style>
        .settings-card { border-radius: 14px; }

        .settings-section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: #0e2e45;
            color: #ffc508;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .setting-row:first-of-type { border-top: 0 !important; }

        .settings-switch {
            width: 2.6em !important;
            height: 1.4em;
            cursor: pointer;
            border-color: rgba(14, 46, 69, 0.25);
        }
        .settings-switch:checked {
            background-color: #0e2e45;
            border-color: #0e2e45;
        }
        .settings-switch:focus {
            border-color: rgba(14, 46, 69, 0.4);
            box-shadow: 0 0 0 0.2rem rgba(255, 197, 8, 0.25);
        }

        /* ── Mobile ( < sm ) — ResponsiveMobileNote.md §2 ── */
        @media (max-width: 575.98px) {
            .container-fluid h4 { font-size: 1.15rem; }
            .setting-row { gap: 0.75rem !important; }
            .settings-form .d-flex.justify-content-end { justify-content: stretch !important; }
            .settings-form .d-flex.justify-content-end .btn { width: 100%; }
        }
    </style>
@endsection

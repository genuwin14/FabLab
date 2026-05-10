@extends('layout.app')

@section('content')
    @php
        $roleAvatars = [
            'admin'    => ['bg' => '#0e2e45', 'color' => '#ffc508'],
            'staff'    => ['bg' => '#0d6efd', 'color' => '#ffffff'],
            'customer' => ['bg' => '#198754', 'color' => '#ffffff'],
        ];
        $roleBadges = [
            'admin'    => ['bg' => 'rgba(14,46,69,0.12)',   'color' => '#0e2e45', 'icon' => 'bi-shield-lock',   'label' => 'Administrator'],
            'staff'    => ['bg' => 'rgba(13,110,253,0.12)', 'color' => '#0d6efd', 'icon' => 'bi-person-badge',  'label' => 'Staff'],
            'customer' => ['bg' => 'rgba(25,135,84,0.12)',  'color' => '#198754', 'icon' => 'bi-person',        'label' => 'Customer'],
        ];
        $roleActionLabels = [
            'admin'    => 'Administrator',
            'staff'    => 'Staff Member',
            'customer' => 'Customer',
        ];
    @endphp

    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
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
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Filters & Search -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="userFilterForm" method="GET" action="{{ route('admin.users.index') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by name, email, or contact number...">
                                </div>
                                <a href="{{ route('admin.users.index') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                    title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                                <button type="submit"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0">
                                    <i class="bi bi-search small"></i>
                                    <span class="small fw-bold">Search</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Name</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Role</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Email</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Contact</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Joined</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Password</th>
                                            <th
                                                class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($users as $user)
                                            @php
                                                $avatar = $roleAvatars[$user->role] ?? $roleAvatars['customer'];
                                                $badge  = $roleBadges[$user->role] ?? $roleBadges['customer'];
                                                $actionRole = $roleActionLabels[$user->role] ?? ucfirst($user->role);
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                            style="width: 40px; height: 40px; background-color: {{ $avatar['bg'] }}; color: {{ $avatar['color'] }};">
                                                            {{ strtoupper(substr($user->fullname, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">
                                                                {{ $user->fullname }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                <i class="bi bi-geo-alt-fill me-1"
                                                                    style="font-size: 0.7rem;"></i>{{ Str::limit($user->address ?? 'No address', 30) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                        style="background-color: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                                        <i class="bi {{ $badge['icon'] }}" style="font-size: 0.7rem;"></i>{{ $badge['label'] }}
                                                    </span>
                                                </td>
                                                <td class="text-muted small">{{ $user->email }}</td>
                                                <td class="text-muted font-monospace small">
                                                    {{ $user->contact_number ?: '—' }}
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $user->created_at->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    @if($user->password)
                                                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                            style="background-color: rgba(25,135,84,0.12); color: #198754;">
                                                            <i class="bi bi-shield-check" style="font-size: 0.7rem;"></i>Verified
                                                        </span>
                                                    @else
                                                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                            style="background-color: rgba(108,117,125,0.15); color: #5a6268;">
                                                            <i class="bi bi-shield-exclamation" style="font-size: 0.7rem;"></i>Not Set
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    @if($user->status === 'active')
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"
                                                            data-bs-toggle="modal" data-bs-target="#statusModal"
                                                            data-user-id="{{ $user->id }}"
                                                            data-user-name="{{ $user->fullname }}"
                                                            data-role="{{ $actionRole }}" data-status="disabled"
                                                            data-route="{{ route('admin.users.updateStatus', $user->id) }}">
                                                            <i class="bi bi-slash-circle me-1"></i>Disable
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold"
                                                            data-bs-toggle="modal" data-bs-target="#statusModal"
                                                            data-user-id="{{ $user->id }}"
                                                            data-user-name="{{ $user->fullname }}"
                                                            data-role="{{ $actionRole }}" data-status="active"
                                                            data-route="{{ route('admin.users.updateStatus', $user->id) }}">
                                                            <i class="bi bi-check-circle me-1"></i>Enable
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-people display-6 d-block mb-3 opacity-50"></i>
                                                    No users found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Include Status Modal -->
    @include('admin.users.components.modal-status')

    <style>
        /* ============================================
           User Status Modal Theme (mirrors product delete)
           ============================================ */
        .user-status-modal-dialog { max-width: 420px; }
        .user-status-modal .modal-content { border-radius: 18px; }

        .user-status-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .user-status-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-line, rgba(255, 197, 8, 0.3)), transparent);
        }
        .user-status-modal-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .user-status-modal-icon.icon-disable {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8088;
        }
        .user-status-modal-icon.icon-enable {
            background: rgba(25, 135, 84, 0.15);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #4dd58a;
        }

        .user-status-modal-body { background-color: #fff; }

        .user-status-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .user-status-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .user-status-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .user-status-confirm-btn-disable {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .user-status-confirm-btn-disable:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }
        .user-status-confirm-btn-enable {
            background-color: #198754;
            border: 1px solid #198754;
            color: #fff;
            transition: all 0.2s ease;
        }
        .user-status-confirm-btn-enable:hover {
            background-color: #146c43;
            border-color: #146c43;
            color: #fff;
        }
    </style>
@endsection

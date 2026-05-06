@extends('layout.app')

@section('content')
    @php
        $roleAvatars = [
            'admin'    => ['bg' => '#0e2e45', 'color' => '#ffc508'],
            'staff'    => ['bg' => '#0d6efd', 'color' => '#ffffff'],
            'customer' => ['bg' => '#198754', 'color' => '#ffffff'],
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

                    <!-- User Tabs and Tables -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                            <ul class="nav nav-tabs nav-tabs-bordered border-bottom-0 card-header-tabs gap-3" id="usersTab"
                                role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-tab-pane"
                                        type="button" role="tab" aria-controls="admin-tab-pane" aria-selected="true">
                                        <i class="bi bi-shield-lock"></i>Administrators
                                        <span class="badge ms-2 rounded-pill">{{ count($admins) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-tab-pane"
                                        type="button" role="tab" aria-controls="staff-tab-pane" aria-selected="false">
                                        <i class="bi bi-person-badge"></i>Staff
                                        <span class="badge ms-2 rounded-pill">{{ count($staffs) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer-tab-pane"
                                        type="button" role="tab" aria-controls="customer-tab-pane" aria-selected="false">
                                        <i class="bi bi-people"></i>Customers
                                        <span class="badge ms-2 rounded-pill">{{ count($customers) }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content" id="usersTabContent">

                                {{-- ============== ADMIN TAB ============== --}}
                                <div class="tab-pane fade show active" id="admin-tab-pane" role="tabpanel"
                                    aria-labelledby="admin-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Name</th>
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
                                                @forelse($admins as $admin)
                                                    <tr>
                                                        <td class="ps-4 py-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                                    style="width: 40px; height: 40px; background-color: {{ $roleAvatars['admin']['bg'] }}; color: {{ $roleAvatars['admin']['color'] }};">
                                                                    {{ strtoupper(substr($admin->fullname, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="fw-bold text-dark mb-0">
                                                                        {{ $admin->fullname }}
                                                                    </h6>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-geo-alt-fill me-1"
                                                                            style="font-size: 0.7rem;"></i>{{ Str::limit($admin->address ?? 'No address', 30) }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-muted small">{{ $admin->email }}</td>
                                                        <td class="text-muted font-monospace small">
                                                            {{ $admin->contact_number ?: '—' }}
                                                        </td>
                                                        <td class="text-muted small">
                                                            {{ $admin->created_at->format('M d, Y') }}
                                                        </td>
                                                        <td>
                                                            @if($admin->password)
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
                                                            @if($admin->status === 'active')
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $admin->id }}"
                                                                    data-user-name="{{ $admin->fullname }}"
                                                                    data-role="Administrator" data-status="disabled"
                                                                    data-route="{{ route('admin.users.updateStatus', $admin->id) }}">
                                                                    <i class="bi bi-slash-circle me-1"></i>Disable
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $admin->id }}"
                                                                    data-user-name="{{ $admin->fullname }}"
                                                                    data-role="Administrator" data-status="active"
                                                                    data-route="{{ route('admin.users.updateStatus', $admin->id) }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Enable
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5 text-muted">
                                                            <i class="bi bi-shield-lock display-6 d-block mb-3 opacity-50"></i>
                                                            No administrators found.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- ============== STAFF TAB ============== --}}
                                <div class="tab-pane fade" id="staff-tab-pane" role="tabpanel"
                                    aria-labelledby="staff-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Name</th>
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
                                                @forelse($staffs as $staff)
                                                    <tr>
                                                        <td class="ps-4 py-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                                    style="width: 40px; height: 40px; background-color: {{ $roleAvatars['staff']['bg'] }}; color: {{ $roleAvatars['staff']['color'] }};">
                                                                    {{ strtoupper(substr($staff->fullname, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="fw-bold text-dark mb-0">
                                                                        {{ $staff->fullname }}
                                                                    </h6>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-geo-alt-fill me-1"
                                                                            style="font-size: 0.7rem;"></i>{{ Str::limit($staff->address ?? 'No address', 30) }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-muted small">{{ $staff->email }}</td>
                                                        <td class="text-muted font-monospace small">
                                                            {{ $staff->contact_number ?: '—' }}
                                                        </td>
                                                        <td class="text-muted small">
                                                            {{ $staff->created_at->format('M d, Y') }}
                                                        </td>
                                                        <td>
                                                            @if($staff->password)
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
                                                            @if($staff->status === 'active')
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $staff->id }}"
                                                                    data-user-name="{{ $staff->fullname }}"
                                                                    data-role="Staff Member" data-status="disabled"
                                                                    data-route="{{ route('admin.users.updateStatus', $staff->id) }}">
                                                                    <i class="bi bi-slash-circle me-1"></i>Disable
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $staff->id }}"
                                                                    data-user-name="{{ $staff->fullname }}"
                                                                    data-role="Staff Member" data-status="active"
                                                                    data-route="{{ route('admin.users.updateStatus', $staff->id) }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Enable
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5 text-muted">
                                                            <i class="bi bi-person-badge display-6 d-block mb-3 opacity-50"></i>
                                                            No staff found.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- ============== CUSTOMER TAB ============== --}}
                                <div class="tab-pane fade" id="customer-tab-pane" role="tabpanel"
                                    aria-labelledby="customer-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Customer</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Academic Info</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Gender</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Contact Details</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Password</th>
                                                    <th
                                                        class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($customers as $customer)
                                                    <tr>
                                                        <td class="ps-4 py-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                                    style="width: 40px; height: 40px; background-color: {{ $roleAvatars['customer']['bg'] }}; color: {{ $roleAvatars['customer']['color'] }};">
                                                                    {{ strtoupper(substr($customer->fullname, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="fw-bold text-dark mb-0">
                                                                        {{ $customer->fullname }}
                                                                    </h6>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-geo-alt-fill me-1"
                                                                            style="font-size: 0.7rem;"></i>{{ Str::limit($customer->address ?? 'No address', 30) }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="small">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold text-dark">
                                                                    {{ $customer->degree ?: '—' }}
                                                                </span>
                                                                <span class="text-muted">
                                                                    {{ $customer->year ?? '' }}
                                                                    {{ $customer->section ? '- ' . $customer->section : '' }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-muted small">{{ $customer->gender ?: '—' }}</td>
                                                        <td class="small">
                                                            <div class="d-flex flex-column gap-1">
                                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                                    <i class="bi bi-telephone" style="font-size: 0.75rem;"></i>
                                                                    <span class="font-monospace">
                                                                        {{ $customer->contact_number ?: '—' }}
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                                    <i class="bi bi-envelope" style="font-size: 0.75rem;"></i>
                                                                    <span>{{ $customer->email }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($customer->password)
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
                                                            @if($customer->status === 'active')
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $customer->id }}"
                                                                    data-user-name="{{ $customer->fullname }}"
                                                                    data-role="Customer" data-status="disabled"
                                                                    data-route="{{ route('admin.users.updateStatus', $customer->id) }}">
                                                                    <i class="bi bi-slash-circle me-1"></i>Disable
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold"
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                                                    data-user-id="{{ $customer->id }}"
                                                                    data-user-name="{{ $customer->fullname }}"
                                                                    data-role="Customer" data-status="active"
                                                                    data-route="{{ route('admin.users.updateStatus', $customer->id) }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Enable
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5 text-muted">
                                                            <i class="bi bi-people display-6 d-block mb-3 opacity-50"></i>
                                                            No customers found.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

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
        /* Tab styling */
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            border-bottom: 3px solid transparent !important;
        }
        .nav-tabs .nav-link:hover {
            color: var(--bs-primary);
            background-color: transparent;
            border-color: rgba(var(--bs-primary-rgb), 0.3) !important;
        }
        .nav-tabs .nav-link.active {
            color: var(--bs-primary) !important;
            font-weight: 700 !important;
            background-color: transparent !important;
            border-bottom: 3px solid var(--bs-primary) !important;
        }
        .nav-tabs .nav-link .badge {
            background-color: #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease-in-out;
        }
        .nav-tabs .nav-link:hover .badge {
            color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.1);
        }
        .nav-tabs .nav-link.active .badge {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
            color: var(--bs-primary) !important;
        }

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

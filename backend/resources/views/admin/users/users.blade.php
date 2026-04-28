@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
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

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">User Management</h4>
                            <p class="text-muted small mb-0">Manage system administrators, staff, and registered customers.</p>
                        </div>
                    </div>

                    <!-- User Tabs and Tables -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 rounded-top-4">
                            <ul class="nav nav-tabs nav-tabs-bordered border-bottom-0 card-header-tabs gap-3" id="usersTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-tab-pane" type="button"
                                        role="tab" aria-controls="admin-tab-pane" aria-selected="true">
                                        <i class="bi bi-shield-lock"></i> Administrators
                                        <span class="badge ms-2 rounded-pill">{{ count($admins) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-tab-pane" type="button"
                                        role="tab" aria-controls="staff-tab-pane" aria-selected="false">
                                        <i class="bi bi-person-badge"></i> Staff
                                        <span class="badge ms-2 rounded-pill">{{ count($staffs) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2 px-3 pb-3 border-0"
                                        id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer-tab-pane" type="button"
                                        role="tab" aria-controls="customer-tab-pane" aria-selected="false">
                                        <i class="bi bi-people"></i> Customers
                                        <span class="badge ms-2 rounded-pill">{{ count($customers) }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content" id="usersTabContent">
                                
                                <!-- Admin Tab -->
                                <div class="tab-pane fade show active" id="admin-tab-pane" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="ps-4 text-muted small text-uppercase border-0">Name</th>
                                                    <th class="text-muted small text-uppercase border-0">Email</th>
                                                    <th class="text-muted small text-uppercase border-0">Contact</th>
                                                    <th class="text-muted small text-uppercase border-0">Joined</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0">Password Status</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($admins as $admin)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="bg-primary bg-opacity-10 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                                                {{ substr($admin->fullname, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-0">{{ $admin->fullname }}</h6>
                                                                <small class="text-muted"><i class="bi bi-geo-alt-fill me-1 small"></i>{{ Str::limit($admin->address ?? 'No address', 30) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted">{{ $admin->email }}</td>
                                                    <td class="text-muted font-monospace small">{{ $admin->contact_number }}</td>
                                                    <td class="text-muted small">{{ $admin->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end pe-4">
                                                        @if($admin->password)
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Verified</span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Not Set</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($admin->status === 'active')
                                                            <button type="button" 
                                                                class="btn btn-outline-danger btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $admin->id }}"
                                                                data-user-name="{{ $admin->fullname }}"
                                                                data-role="Administrator"
                                                                data-status="disabled"
                                                                data-route="{{ route('admin.users.updateStatus', $admin->id) }}">
                                                                Disable
                                                            </button>
                                                        @else
                                                            <button type="button" 
                                                                class="btn btn-outline-success btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $admin->id }}"
                                                                data-user-name="{{ $admin->fullname }}"
                                                                data-role="Administrator"
                                                                data-status="active"
                                                                data-route="{{ route('admin.users.updateStatus', $admin->id) }}">
                                                                Enable
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No administrators found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Staff Tab -->
                                <div class="tab-pane fade" id="staff-tab-pane" role="tabpanel" aria-labelledby="staff-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="ps-4 text-muted small text-uppercase border-0">Name</th>
                                                    <th class="text-muted small text-uppercase border-0">Email</th>
                                                    <th class="text-muted small text-uppercase border-0">Contact</th>
                                                    <th class="text-muted small text-uppercase border-0">Joined</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0">Password Status</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($staffs as $staff)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                                                {{ substr($staff->fullname, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-0">{{ $staff->fullname }}</h6>
                                                                <small class="text-muted"><i class="bi bi-geo-alt-fill me-1 small"></i>{{ Str::limit($staff->address ?? 'No address', 30) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted">{{ $staff->email }}</td>
                                                    <td class="text-muted font-monospace small">{{ $staff->contact_number }}</td>
                                                    <td class="text-muted small">{{ $staff->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end pe-4">
                                                        @if($staff->password)
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Verified</span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Not Set</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($staff->status === 'active')
                                                            <button type="button" 
                                                                class="btn btn-outline-danger btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $staff->id }}"
                                                                data-user-name="{{ $staff->fullname }}"
                                                                data-role="Staff Member"
                                                                data-status="disabled"
                                                                data-route="{{ route('admin.users.updateStatus', $staff->id) }}">
                                                                Disable
                                                            </button>
                                                        @else
                                                            <button type="button" 
                                                                class="btn btn-outline-success btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $staff->id }}"
                                                                data-user-name="{{ $staff->fullname }}"
                                                                data-role="Staff Member"
                                                                data-status="active"
                                                                data-route="{{ route('admin.users.updateStatus', $staff->id) }}">
                                                                Enable
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No staff found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Customer Tab -->
                                <div class="tab-pane fade" id="customer-tab-pane" role="tabpanel" aria-labelledby="customer-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="ps-4 text-muted small text-uppercase border-0">Customer</th>
                                                    <th class="text-muted small text-uppercase border-0">Academic Info</th>
                                                    <th class="text-muted small text-uppercase border-0">Gender</th>
                                                    <th class="text-muted small text-uppercase border-0">Contact Details</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0">Password Status</th>
                                                    <th class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($customers as $customer)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                                                {{ substr($customer->fullname, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-0">{{ $customer->fullname }}</h6>
                                                                <small class="text-muted"><i class="bi bi-geo-alt-fill me-1 small"></i>{{ Str::limit($customer->address ?? 'No address', 30) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted small">
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold">{{ $customer->degree }}</span>
                                                            <span>{{ $customer->year }} - {{ $customer->section }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted">{{ $customer->gender }}</td>
                                                    <td class="text-muted small">
                                                        <div class="d-flex flex-column">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-telephone text-muted" style="font-size: 0.8rem;"></i>
                                                                <span class="font-monospace">{{ $customer->contact_number }}</span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-envelope text-muted" style="font-size: 0.8rem;"></i>
                                                                <span>{{ $customer->email }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($customer->password)
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Verified</span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Not Set</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($customer->status === 'active')
                                                            <button type="button" 
                                                                class="btn btn-outline-danger btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $customer->id }}"
                                                                data-user-name="{{ $customer->fullname }}"
                                                                data-role="Customer"
                                                                data-status="disabled"
                                                                data-route="{{ route('admin.users.updateStatus', $customer->id) }}">
                                                                Disable
                                                            </button>
                                                        @else
                                                            <button type="button" 
                                                                class="btn btn-outline-success btn-sm rounded-pill px-3" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#statusModal"
                                                                data-user-id="{{ $customer->id }}"
                                                                data-user-name="{{ $customer->fullname }}"
                                                                data-role="Customer"
                                                                data-status="active"
                                                                data-route="{{ route('admin.users.updateStatus', $customer->id) }}">
                                                                Enable
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No customers found.</td>
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
        /* Custom Tab Styles */
        .nav-tabs .nav-link {
            color: #6c757d; /* text-secondary */
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            border-bottom: 3px solid transparent !important; /* Reserve space for border */
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
            background-color: #e9ecef; /* gray-200 */
            color: #6c757d; /* secondary text */
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
    </style>
@endsection
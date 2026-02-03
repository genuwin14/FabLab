@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm" style="width: 280px; z-index: 1040;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <div class="container-fluid">
                    <!-- Welcome Banner -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="p-5 rounded-4 text-white position-relative overflow-hidden"
                                style="background: linear-gradient(135deg, #0e2e45 0%, #1a4b6e 100%);">
                                <div class="position-relative z-1">
                                    <h2 class="fw-bold display-6">Hello, Staff {{ Auth::user()->fullname }}!</h2>
                                    <p class="lead opacity-75">There are 3 pending orders and 1 stock request to review.</p>
                                    <div class="d-flex gap-2 mt-3">
                                        <a href="{{ route('staff.orders.index') }}"
                                            class="btn btn-warning fw-bold text-dark px-4"><i
                                                class="bi bi-cart4 me-2"></i>Go to Orders</a>
                                        <button class="btn btn-outline-light fw-bold px-4"><i
                                                class="bi bi-upc-scan me-2"></i>Open POS</button>
                                    </div>
                                </div>
                                <!-- Decor -->
                                <div class="position-absolute top-0 end-0 opacity-10">
                                    <i class="bi bi-clipboard-check"
                                        style="font-size: 15rem; transform: rotate(-15deg) translate(20px, -20px);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task & Stats Cards -->
                    <div class="row g-4 mb-4">
                        <!-- Pending Orders -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                        <i class="bi bi-cart4 fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">To Pack</h6>
                                        <h3 class="fw-bold mb-0">3</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Low Stock -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                                        <i class="bi bi-box-seam fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Low Stock</h6>
                                        <h3 class="fw-bold mb-0">2</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Completed Today -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                        <i class="bi bi-check-circle fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Completed (Today)</h6>
                                        <h3 class="fw-bold mb-0">12</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Sales Today (Maybe hidden for some roles in v2) -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                        <i class="bi bi-currency-dollar fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Sales (Today)</h6>
                                        <h3 class="fw-bold mb-0">$450.00</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Todo List / Tasks -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div
                                    class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Priority Tasks</h5>
                                    <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold small"><i
                                            class="bi bi-plus me-1"></i> Add Task</button>
                                </div>
                                <div class="card-body p-4">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0 py-3 border-bottom d-flex align-items-center gap-3">
                                            <input class="form-check-input flex-shrink-0 my-0 border-2" type="checkbox"
                                                value="" id="task1">
                                            <div class="flex-grow-1">
                                                <label class="form-check-label fw-bold d-block" for="task1">Pack Order
                                                    #ORD-005 for Pickup</label>
                                                <small class="text-muted">Due by 2:00 PM today</small>
                                            </div>
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Urgent</span>
                                        </li>
                                        <li class="list-group-item px-0 py-3 border-bottom d-flex align-items-center gap-3">
                                            <input class="form-check-input flex-shrink-0 my-0 border-2" type="checkbox"
                                                value="" id="task2">
                                            <div class="flex-grow-1">
                                                <label class="form-check-label fw-bold d-block" for="task2">Check inventory
                                                    for 3D Printer Filament</label>
                                                <small class="text-muted">Requested by Admin</small>
                                            </div>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill">Info</span>
                                        </li>
                                        <li class="list-group-item px-0 py-3 d-flex align-items-center gap-3">
                                            <input class="form-check-input flex-shrink-0 my-0 border-2" type="checkbox"
                                                value="" id="task3">
                                            <div class="flex-grow-1">
                                                <label class="form-check-label fw-bold d-block" for="task3">Clean and
                                                    calibrate Laser Cutter</label>
                                                <small class="text-muted">Maintenance Schedule</small>
                                            </div>
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Maintenance</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications / Recent Activity -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-transparent border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="timeline">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2"
                                                    style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-cart-check"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-dark fw-bold small">New Order Received</p>
                                                <p class="text-muted small mb-0">Order #ORD-102 placed by Customer A.</p>
                                                <small class="text-muted" style="font-size: 0.75rem;">10 mins ago</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2"
                                                    style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-check-lg"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-dark fw-bold small">Order Completed</p>
                                                <p class="text-muted small mb-0">Order #ORD-099 marked as ready.</p>
                                                <small class="text-muted" style="font-size: 0.75rem;">1 hour ago</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2"
                                                    style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-dark fw-bold small">Stock Alert</p>
                                                <p class="text-muted small mb-0">Black PLA Filament is below threshold.</p>
                                                <small class="text-muted" style="font-size: 0.75rem;">2 hours ago</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"
        style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-circle text-warning display-4"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark">Sign Out?</h5>
                    <p class="text-muted small mb-4">Are you sure you want to log out of the Staff Panel?</p>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4 rounded-pill fw-bold small"
                            data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold small">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
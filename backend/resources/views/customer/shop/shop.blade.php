@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm" style="width: 280px; z-index: 1040;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
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
                                    <h2 class="fw-bold display-6">Welcome back, {{ Auth::user()->fullname }}!</h2>
                                    <p class="lead opacity-75">Track your orders and explore new products.</p>
                                    <button class="btn btn-accent fw-bold mt-2">Create New Order</button>
                                </div>
                                <!-- Decor -->
                                <div class="position-absolute top-0 end-0 opacity-10">
                                    <i class="bi bi-box-seam"
                                        style="font-size: 15rem; transform: rotate(-15deg) translate(20px, -20px);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                        <i class="bi bi-cart-check fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Active Orders</h6>
                                        <h3 class="fw-bold mb-0">3</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                        <i class="bi bi-clipboard-check fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Completed</h6>
                                        <h3 class="fw-bold mb-0">12</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                        <i class="bi bi-clock-history fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Action</h6>
                                        <h3 class="fw-bold mb-0">1</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div
                            class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Recent Orders</h5>
                            <a href="#" class="text-decoration-none text-primary fw-bold small">View All</a>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="border-0 rounded-start">Order ID</th>
                                            <th scope="col" class="border-0">Product</th>
                                            <th scope="col" class="border-0">Date</th>
                                            <th scope="col" class="border-0">Status</th>
                                            <th scope="col" class="border-0 text-end rounded-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">#ORD-001</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-light rounded p-1">
                                                        <i class="bi bi-cpu text-secondary"></i>
                                                    </div>
                                                    <span>Arduino Uno R3</span>
                                                </div>
                                            </td>
                                            <td class="text-muted">Feb 01, 2026</td>
                                            <td><span
                                                    class="badge bg-warning text-dark bg-opacity-25 text-opacity-75 px-3 py-2 rounded-pill">Processing</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light rounded-circle"><i
                                                        class="bi bi-three-dots-vertical"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">#ORD-002</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-light rounded p-1">
                                                        <i class="bi bi-motherboard text-secondary"></i>
                                                    </div>
                                                    <span>Raspberry Pi 4</span>
                                                </div>
                                            </td>
                                            <td class="text-muted">Jan 28, 2026</td>
                                            <td><span
                                                    class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Completed</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light rounded-circle"><i
                                                        class="bi bi-three-dots-vertical"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                    <p class="text-muted small mb-4">Are you sure you want to log out of your FABLAB account?</p>

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
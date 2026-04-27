@extends('layout.app')

@section('content')
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <div class="container-fluid">
                    
                    <!-- Welcome Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold text-primary mb-1">Dashboard Overview</h3>
                            <p class="text-muted small mb-0">Good evening, {{ Auth::user()->fullname }}. Here's what's happening today.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-white rounded-pill px-3 shadow-sm border small fw-bold text-muted hover-accent">
                                <i class="bi bi-calendar3 me-2"></i>Last 30 Days
                            </button>
                            <button class="btn btn-primary rounded-pill px-3 shadow-sm small fw-bold">
                                <i class="bi bi-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary">
                                            <i class="bi bi-currency-dollar fs-4"></i>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                            <i class="bi bi-arrow-up-short"></i>12%
                                        </span>
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Revenue</h6>
                                    <h3 class="fw-bold mb-0">₱{{ number_format($totalRevenue, 2) }}</h3>
                                    <div class="stat-pattern"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success">
                                            <i class="bi bi-people fs-4"></i>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                            <i class="bi bi-arrow-up-short"></i>5%
                                        </span>
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Users</h6>
                                    <h3 class="fw-bold mb-0">{{ number_format($activeUsersCount) }}</h3>
                                    <div class="stat-pattern bg-success"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning">
                                            <i class="bi bi-box-seam fs-4"></i>
                                        </div>
                                        @if($lowStockCount > 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                Critical
                                            </span>
                                        @endif
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Low Stock Items</h6>
                                    <h3 class="fw-bold mb-0">{{ $lowStockCount }}</h3>
                                    <div class="stat-pattern bg-warning"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info">
                                            <i class="bi bi-cart-check fs-4"></i>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small">
                                            Pending
                                        </span>
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Active Orders</h6>
                                    <h3 class="fw-bold mb-0">{{ $pendingOrdersCount }}</h3>
                                    <div class="stat-pattern bg-info"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Sales Revenue Trend</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-pill px-3 border" type="button" data-bs-toggle="dropdown">
                                            Yearly <i class="bi bi-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div id="salesChart" style="min-height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Inventory by Category</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div id="categoryChart" style="min-height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row: Recent Orders & Top Products -->
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Recent Transactions</h5>
                                    <a href="#" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">View All</a>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="ps-3 border-0 small text-uppercase text-muted">Order ID</th>
                                                    <th class="border-0 small text-uppercase text-muted">Customer</th>
                                                    <th class="border-0 small text-uppercase text-muted">Amount</th>
                                                    <th class="border-0 small text-uppercase text-muted">Status</th>
                                                    <th class="border-0 small text-uppercase text-muted text-end pe-3">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentOrders as $order)
                                                    <tr>
                                                        <td class="ps-3 py-3 font-monospace fw-bold text-primary">{{ $order->order_number }}</td>
                                                        <td>
                                                            <div class="fw-bold text-dark small">{{ $order->user->fullname }}</div>
                                                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $order->created_at->diffForHumans() }}</div>
                                                        </td>
                                                        <td class="fw-bold text-dark small">₱{{ number_format($order->total_amount, 2) }}</td>
                                                        <td>
                                                            @php
                                                                $statusClass = 'bg-secondary';
                                                                if($order->status === 'pending') $statusClass = 'bg-warning text-warning';
                                                                if($order->status === 'completed') $statusClass = 'bg-success text-success';
                                                                if($order->status === 'cancelled') $statusClass = 'bg-danger text-danger';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }} bg-opacity-10 rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.65rem;">
                                                                {{ ucfirst($order->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            <a href="#" class="btn btn-light btn-sm rounded-circle text-primary border">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">No recent orders.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Top Products</h5>
                                </div>
                                <div class="card-body p-4">
                                    @forelse($topProducts as $product)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded-3 shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate small">{{ $product->name }}</h6>
                                                <p class="text-muted mb-0 small">{{ $product->category->name }}</p>
                                            </div>
                                            <div class="text-end flex-shrink-0 ms-2">
                                                <div class="fw-bold text-primary small">{{ $product->total_sold }} sold</div>
                                                <div class="text-muted small" style="font-size: 0.7rem;">₱{{ number_format($product->price, 2) }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center py-4">No top products yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <style>
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
        }
        .stat-pattern {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background-color: var(--bs-primary);
            opacity: 0.03;
            border-radius: 50%;
            z-index: 0;
        }
        .hover-accent:hover {
            background-color: #f8f9fa !important;
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sales Chart
            const salesOptions = {
                series: [{
                    name: 'Revenue',
                    data: @json($salesTrend->pluck('total'))
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, sans-serif'
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3, colors: ['#0d6efd'] },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100]
                    }
                },
                xaxis: {
                    categories: @json($salesTrend->pluck('month')),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) { return '₱' + val.toLocaleString(); }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (val) { return '₱' + val.toLocaleString(); }
                    }
                },
                colors: ['#0d6efd']
            };

            const salesChart = new ApexCharts(document.querySelector("#salesChart"), salesOptions);
            salesChart.render();

            // Category Chart (Bar Chart - No Pie!)
            const categoryOptions = {
                series: [{
                    name: 'Products',
                    data: @json($categoryDistribution->pluck('count'))
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '45%',
                        distributed: true,
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                xaxis: {
                    categories: @json($categoryDistribution->pluck('name')),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                },
                colors: ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6610f2', '#fd7e14']
            };

            const categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryOptions);
            categoryChart.render();
        });
    </script>
@endsection
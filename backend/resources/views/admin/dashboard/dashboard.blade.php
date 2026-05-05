@extends('layout.app')

@section('content')
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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

                    <!-- Primary KPI Stats Grid -->
                    <div class="row g-4 mb-4">
                        <!-- Total Revenue -->
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card h-100">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-primary-soft p-3 text-primary">
                                            <i class="bi bi-currency-dollar fs-4"></i>
                                        </div>
                                        @if($revenueGrowth >= 0)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                                <i class="bi bi-arrow-up-short"></i>{{ $revenueGrowth }}%
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                <i class="bi bi-arrow-down-short"></i>{{ abs($revenueGrowth) }}%
                                            </span>
                                        @endif
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Revenue</h6>
                                    <h3 class="fw-bold mb-0">₱{{ number_format($totalRevenue, 2) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">vs last month</p>
                                    <div class="stat-pattern"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Revenue -->
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card h-100">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success">
                                            <i class="bi bi-calendar-check fs-4"></i>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                            Today
                                        </span>
                                    </div>
                                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Today's Revenue</h6>
                                    <h3 class="fw-bold mb-0">₱{{ number_format($todayRevenue, 2) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">{{ now()->format('M j, Y') }}</p>
                                    <div class="stat-pattern bg-success"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Orders -->
                        <div class="col-md-6 col-xl-3">
                            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card h-100">
                                    <div class="card-body p-4 position-relative">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning">
                                                <i class="bi bi-clock-history fs-4"></i>
                                            </div>
                                            @if($orderGrowth >= 0)
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                                    <i class="bi bi-arrow-up-short"></i>{{ $orderGrowth }}%
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                    <i class="bi bi-arrow-down-short"></i>{{ abs($orderGrowth) }}%
                                                </span>
                                            @endif
                                        </div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Orders</h6>
                                        <h3 class="fw-bold mb-0 text-dark">{{ $pendingOrdersCount }}</h3>
                                        <p class="text-muted small mb-0 mt-1">awaiting review</p>
                                        <div class="stat-pattern bg-warning"></div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Critical Stock -->
                        <div class="col-md-6 col-xl-3">
                            <a href="{{ route('admin.inventory.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden stat-card h-100">
                                    <div class="card-body p-4 position-relative">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="rounded-3 bg-danger bg-opacity-10 p-3 text-danger">
                                                <i class="bi bi-exclamation-triangle fs-4"></i>
                                            </div>
                                            @if($lowStockCount > 0)
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                    Critical
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                                    Healthy
                                                </span>
                                            @endif
                                        </div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Stock Alerts</h6>
                                        <h3 class="fw-bold mb-0 text-dark">{{ $lowStockCount }}</h3>
                                        <p class="text-muted small mb-0 mt-1">items at or below threshold</p>
                                        <div class="stat-pattern bg-danger"></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Secondary KPI Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 mini-stat">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="rounded-3 bg-primary-soft p-2 text-primary me-3">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Products</div>
                                        <div class="fw-bold fs-5 mb-0">{{ number_format($totalProducts) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 mini-stat">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3">
                                        <i class="bi bi-people fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Customers</div>
                                        <div class="fw-bold fs-5 mb-0">
                                            {{ number_format($totalCustomers) }}
                                            @if($newCustomersThisMonth > 0)
                                                <span class="text-success small fw-normal">+{{ $newCustomersThisMonth }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 mini-stat">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="rounded-3 bg-warning bg-opacity-10 p-2 text-warning me-3">
                                        <i class="bi bi-truck fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Suppliers</div>
                                        <div class="fw-bold fs-5 mb-0">{{ number_format($totalSuppliers) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 mini-stat">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success me-3">
                                        <i class="bi bi-bag-check fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Open POs</div>
                                        <div class="fw-bold fs-5 mb-0">{{ number_format($pendingPOCount) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Stock Trend</h5>
                                        <p class="text-muted small mb-0">Top moving products — last 30 days</p>
                                    </div>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 small fw-bold">
                                        <i class="bi bi-activity me-1"></i>30D
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <div id="stockChart" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Top Products</h5>
                                    <p class="text-muted small mb-0">Best performers by units sold</p>
                                </div>
                                <div class="card-body p-4">
                                    @forelse($topProducts as $index => $product)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3 position-relative">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded-3 shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 42px; height: 42px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                                <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;">
                                                    #{{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate small">{{ $product->name }}</h6>
                                                <p class="text-muted mb-0 small" style="font-size: 0.7rem;">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                            </div>
                                            <div class="text-end flex-shrink-0 ms-2">
                                                <div class="fw-bold text-primary small">{{ $product->total_sold ?? 0 }}</div>
                                                <div class="text-muted small" style="font-size: 0.7rem;">sold</div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center py-4 small">No top products yet.</p>
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
        /* Soft primary tint — works around the layout's bg-primary !important override
           that disables Bootstrap's bg-opacity-* utility. */
        .bg-primary-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        /* ── Cards / hover effects ── */
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: default;
        }
        a > .stat-card { cursor: pointer; }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }
        .stat-pattern {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background-color: var(--bs-primary);
            opacity: 0.04;
            border-radius: 50%;
            z-index: 0;
        }
        .mini-stat {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .mini-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05) !important;
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
            // Stock Trend (stocks-style multi-line chart) — top-moving products' inventory levels
            const stockSeries = @json($stockSeries);
            const stockLabels = @json($stockLabels);

            const stockOptions = {
                series: stockSeries.length ? stockSeries : [{ name: 'No data', data: [] }],
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, sans-serif',
                    animations: { enabled: true, speed: 600 }
                },
                stroke: { curve: 'smooth', width: 2.5 },
                dataLabels: { enabled: false },
                colors: ['#0d6efd', '#198754', '#ffc107', '#6610f2', '#dc3545'],
                xaxis: {
                    categories: stockLabels,
                    tickAmount: 6,
                    labels: { style: { fontSize: '11px', colors: '#6c757d' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '11px', colors: '#6c757d' },
                        formatter: v => Number(v).toLocaleString()
                    }
                },
                grid: { borderColor: '#f1f1f1', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '12px',
                    markers: { width: 10, height: 10, radius: 12 },
                    itemMargin: { horizontal: 8, vertical: 4 }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    x: { show: true },
                    y: { formatter: v => Number(v).toLocaleString() + ' units' }
                },
                markers: { size: 0, hover: { size: 5 } },
                noData: { text: 'No recent stock activity', style: { color: '#6c757d', fontSize: '13px' } }
            };
            new ApexCharts(document.querySelector("#stockChart"), stockOptions).render();
        });
    </script>
@endsection

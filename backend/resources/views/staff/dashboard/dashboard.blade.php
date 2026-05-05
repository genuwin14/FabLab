@extends('layout.app')

@section('content')
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100" style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class='d-none d-md-block sidebar-spacer flex-shrink-0' style='width: 280px;'></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    @php
                        $hour = (int) now()->format('H');
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    @endphp

                    <!-- Welcome Banner -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="p-4 p-md-5 rounded-4 text-white position-relative overflow-hidden"
                                style="background: linear-gradient(135deg, #0e2e45 0%, #1a4b6e 100%);">
                                <div class="position-relative z-1">
                                    <p class="mb-1 opacity-75 small">{{ $greeting }}, {{ now()->format('l, F j') }}</p>
                                    <h2 class="fw-bold mb-2">Hello, {{ Auth::user()->fullname }}!</h2>
                                    <p class="opacity-75 mb-3">
                                        @if($pendingCount + $totalStockAlerts === 0)
                                            All caught up — no pending orders or stock alerts right now.
                                        @else
                                            You have
                                            @if($pendingCount > 0)
                                                <span class="fw-bold text-warning">{{ $pendingCount }} pending order{{ $pendingCount === 1 ? '' : 's' }}</span>
                                            @endif
                                            @if($pendingCount > 0 && $totalStockAlerts > 0) and @endif
                                            @if($totalStockAlerts > 0)
                                                <span class="fw-bold text-danger">{{ $totalStockAlerts }} stock alert{{ $totalStockAlerts === 1 ? '' : 's' }}</span>
                                            @endif
                                            to review.
                                        @endif
                                    </p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('staff.orders.index') }}"
                                            class="btn btn-warning fw-bold text-dark px-4 rounded-pill">
                                            <i class="bi bi-cart4 me-2"></i>Review Orders
                                        </a>
                                        <a href="{{ route('staff.inventory.index') }}"
                                            class="btn btn-outline-light fw-bold px-4 rounded-pill">
                                            <i class="bi bi-clipboard-data me-2"></i>Stock Logs
                                        </a>
                                    </div>
                                </div>
                                <!-- Decor -->
                                <div class="position-absolute top-0 end-0 opacity-10 d-none d-md-block">
                                    <i class="bi bi-clipboard-check"
                                        style="font-size: 15rem; transform: rotate(-15deg) translate(20px, -20px);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Pipeline KPIs -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 col-xl-3">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                                    <div class="card-body p-4 d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning">
                                            <i class="bi bi-clock-history fs-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small fw-bold text-uppercase mb-1">To Review</h6>
                                            <h3 class="fw-bold mb-0 text-dark">{{ $pendingCount }}</h3>
                                            <small class="text-muted">pending orders</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                                    <div class="card-body p-4 d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary-soft p-3 text-primary">
                                            <i class="bi bi-gear-wide-connected fs-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small fw-bold text-uppercase mb-1">Processing</h6>
                                            <h3 class="fw-bold mb-0 text-dark">{{ $processingCount }}</h3>
                                            <small class="text-muted">in progress</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                                    <div class="card-body p-4 d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info">
                                            <i class="bi bi-bag-check fs-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted small fw-bold text-uppercase mb-1">Ready for Pickup</h6>
                                            <h3 class="fw-bold mb-0 text-dark">{{ $readyCount }}</h3>
                                            <small class="text-muted">awaiting customer</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                                <div class="card-body p-4 d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success">
                                        <i class="bi bi-check-circle fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Completed Today</h6>
                                        <h3 class="fw-bold mb-0 text-dark">{{ $completedTodayCount }}</h3>
                                        <small class="text-muted">{{ now()->format('M j') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Alert Mini Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('staff.products.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="rounded-3 {{ $lowStockProducts > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} p-2 me-3">
                                            <i class="bi bi-box-seam fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Products Low</div>
                                            <div class="fw-bold fs-5 mb-0 text-dark">{{ $lowStockProducts }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('staff.raw-materials.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="rounded-3 {{ $lowStockMaterials > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} p-2 me-3">
                                            <i class="bi bi-boxes fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Materials Low</div>
                                            <div class="fw-bold fs-5 mb-0 text-dark">{{ $lowStockMaterials }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('staff.textures.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="rounded-3 {{ $lowStockTextures > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} p-2 me-3">
                                            <i class="bi bi-layers fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Textures Low</div>
                                            <div class="fw-bold fs-5 mb-0 text-dark">{{ $lowStockTextures }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('staff.purchase.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info me-3">
                                            <i class="bi bi-truck fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Incoming POs</div>
                                            <div class="fw-bold fs-5 mb-0 text-dark">{{ $incomingPOCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Order Activity</h5>
                                        <p class="text-muted small mb-0">New orders received over the last 7 days</p>
                                    </div>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 small fw-bold">
                                        <i class="bi bi-calendar-week me-1"></i>7 Days
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <div id="ordersChart" style="min-height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Active Pipeline</h5>
                                    <p class="text-muted small mb-0">Orders in progress</p>
                                </div>
                                <div class="card-body p-4">
                                    <div id="pipelineChart" style="min-height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Queue & Critical Stock -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Order Queue</h5>
                                        <p class="text-muted small mb-0">Orders awaiting your action — oldest first</p>
                                    </div>
                                    <a href="{{ route('staff.orders.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">View All</a>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="ps-3 border-0 small text-uppercase text-muted">Order</th>
                                                    <th class="border-0 small text-uppercase text-muted">Customer</th>
                                                    <th class="border-0 small text-uppercase text-muted">Status</th>
                                                    <th class="border-0 small text-uppercase text-muted text-end pe-3">Waiting</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($orderQueue as $order)
                                                    <tr>
                                                        <td class="ps-3 py-3">
                                                            <div class="font-monospace fw-bold text-primary small">{{ $order->order_number }}</div>
                                                            <div class="text-muted" style="font-size: 0.7rem;">₱{{ number_format($order->total_amount, 2) }}</div>
                                                        </td>
                                                        <td class="fw-bold text-dark small">{{ $order->user->fullname ?? 'N/A' }}</td>
                                                        <td>
                                                            @php
                                                                $statusClass = 'bg-secondary text-secondary';
                                                                if($order->status === 'pending') $statusClass = 'bg-warning text-warning';
                                                                if($order->status === 'approved') $statusClass = 'bg-info text-info';
                                                                if($order->status === 'processing') $statusClass = 'bg-primary text-primary';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }} bg-opacity-10 rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.65rem;">
                                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            @php $hours = $order->created_at->diffInHours(now()); @endphp
                                                            <span class="small fw-bold {{ $hours > 24 ? 'text-danger' : ($hours > 8 ? 'text-warning' : 'text-muted') }}">
                                                                {{ $order->created_at->diffForHumans(null, true) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-5">
                                                            <i class="bi bi-check-circle text-success fs-1"></i>
                                                            <p class="text-muted small mb-0 mt-2">No orders waiting. You're all caught up!</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Critical Stock</h5>
                                        <p class="text-muted small mb-0">Products below threshold</p>
                                    </div>
                                    <a href="{{ route('staff.products.index') }}" class="btn btn-light btn-sm rounded-circle border text-muted">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                                <div class="card-body p-4">
                                    @forelse($criticalStockProducts as $product)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded-3 shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 42px; height: 42px;">
                                                        <i class="bi bi-box"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate small">{{ $product->name }}</h6>
                                                <p class="text-muted mb-0 small" style="font-size: 0.7rem;">SKU: {{ $product->sku }}</p>
                                            </div>
                                            <div class="text-end flex-shrink-0 ms-2">
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                    {{ $product->stock }} / {{ $product->low_stock_threshold }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <i class="bi bi-check-circle text-success fs-1"></i>
                                            <p class="text-muted small mb-0 mt-2">All products in healthy stock.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Incoming Purchase Orders -->
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Incoming Deliveries</h5>
                                        <p class="text-muted small mb-0">Purchase orders awaiting receipt</p>
                                    </div>
                                    <a href="{{ route('staff.purchase.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">All Purchase Orders</a>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        @forelse($incomingPurchaseOrders as $po)
                                            <div class="col-md-6 col-xl-4">
                                                <a href="{{ route('staff.purchase.show', $po->purchase_order_id) }}" class="text-decoration-none">
                                                    <div class="p-3 rounded-3 border hover-bg-light transition-all h-100">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <div class="font-monospace fw-bold text-primary small">{{ $po->po_number }}</div>
                                                                <div class="text-muted small">{{ $po->supplier->name ?? 'N/A' }}</div>
                                                            </div>
                                                            @php
                                                                $poColor = $po->status === 'confirmed' ? 'primary' : 'info';
                                                            @endphp
                                                            <span class="badge bg-{{ $poColor }} bg-opacity-10 text-{{ $poColor }} rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.6rem;">
                                                                {{ ucfirst($po->status) }}
                                                            </span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-end">
                                                            <div>
                                                                <div class="text-muted small" style="font-size: 0.7rem;">Expected</div>
                                                                <div class="fw-bold text-dark small">
                                                                    @if($po->expected_delivery_date)
                                                                        {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M j, Y') }}
                                                                    @else
                                                                        <span class="text-muted">Not set</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="text-muted small" style="font-size: 0.7rem;">Total</div>
                                                                <div class="fw-bold text-dark small">₱{{ number_format($po->total_cost ?? 0, 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-4">
                                                <i class="bi bi-inbox text-muted fs-1"></i>
                                                <p class="text-muted small mb-0 mt-2">No incoming deliveries.</p>
                                            </div>
                                        @endforelse
                                    </div>
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
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
        }
        .mini-stat {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .mini-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.05) !important;
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
            // Daily Orders Chart
            const ordersOptions = {
                series: [{
                    name: 'Orders',
                    data: @json($dailyOrders->pluck('count'))
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '50%',
                        distributed: false
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($dailyOrders->pluck('label')),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: val => Math.round(val)
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                },
                colors: ['#0e2e45'],
                tooltip: {
                    custom: function({ series, seriesIndex, dataPointIndex }) {
                        const dates = @json($dailyOrders->pluck('date'));
                        return '<div class="px-3 py-2"><div class="small text-muted">' + dates[dataPointIndex] + '</div><div class="fw-bold">' + series[seriesIndex][dataPointIndex] + ' orders</div></div>';
                    }
                }
            };
            new ApexCharts(document.querySelector("#ordersChart"), ordersOptions).render();

            // Active Pipeline Donut
            const pipelineData = @json($orderStatusBreakdown);
            const statusColorMap = {
                'pending': '#ffc107',
                'approved': '#0dcaf0',
                'processing': '#0d6efd',
                'ready_for_pickup': '#6610f2'
            };
            const pipelineLabels = pipelineData.map(s => s.status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
            const pipelineSeries = pipelineData.map(s => Number(s.count));
            const pipelineColors = pipelineData.map(s => statusColorMap[s.status] || '#6c757d');

            const pipelineOptions = {
                series: pipelineSeries.length ? pipelineSeries : [1],
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: pipelineLabels.length ? pipelineLabels : ['No Active Orders'],
                colors: pipelineColors.length ? pipelineColors : ['#e9ecef'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    markers: { width: 10, height: 10, radius: 12 }
                },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { fontSize: '12px', color: '#6c757d' },
                                value: { fontSize: '20px', fontWeight: 700, color: '#0e2e45' },
                                total: {
                                    show: true,
                                    label: 'Active',
                                    fontSize: '12px',
                                    color: '#6c757d',
                                    formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                },
                stroke: { width: 0 }
            };
            new ApexCharts(document.querySelector("#pipelineChart"), pipelineOptions).render();
        });
    </script>
@endsection

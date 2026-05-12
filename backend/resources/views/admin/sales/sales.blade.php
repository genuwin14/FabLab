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

                    @php
                        $rangeOptions = [
                            '7days'    => 'Last 7 days',
                            '30days'   => 'Last 30 days',
                            '90days'   => 'Last 90 days',
                            '12months' => 'Last 12 months',
                            'all'      => 'All time',
                        ];
                        $statusMeta = [
                            'pending'          => ['label' => 'Pending',          'color' => '#997404'],
                            'approved'         => ['label' => 'Approved',         'color' => '#0d6efd'],
                            'processing'       => ['label' => 'Processing',       'color' => '#087990'],
                            'ready_for_pickup' => ['label' => 'Ready for Pickup', 'color' => '#b95900'],
                            'completed'        => ['label' => 'Completed',        'color' => '#198754'],
                            'cancelled'        => ['label' => 'Cancelled',        'color' => '#dc3545'],
                        ];
                    @endphp

                    <!-- Header + Range Filter -->
                    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Sales</h4>
                            <p class="text-muted small mb-0">
                                Completed sales from
                                <span class="fw-semibold text-dark">{{ $rangeStart->format('M j, Y') }}</span>
                                to
                                <span class="fw-semibold text-dark">{{ $rangeEnd->format('M j, Y') }}</span>
                            </p>
                        </div>
                        <form method="GET" action="{{ route('admin.sales.index') }}"
                            class="d-flex flex-wrap align-items-center gap-2">
                            <select name="range" class="form-select form-select-sm rounded-2 w-auto"
                                onchange="this.form.querySelector('[name=date_from]').value=''; this.form.querySelector('[name=date_to]').value=''; this.form.submit();">
                                @foreach($rangeOptions as $key => $label)
                                    <option value="{{ $key }}" {{ $range === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                                @if($range === 'custom')
                                    <option value="custom" selected>Custom range</option>
                                @endif
                            </select>
                            <span class="text-muted small">or</span>
                            <input type="date" name="date_from" value="{{ $dateFrom }}"
                                class="form-control form-control-sm rounded-2 w-auto">
                            <span class="text-muted small">&ndash;</span>
                            <input type="date" name="date_to" value="{{ $dateTo }}"
                                class="form-control form-control-sm rounded-2 w-auto">
                            <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3 fw-bold">
                                <i class="bi bi-funnel me-1"></i>Apply
                            </button>
                            @if($range === 'custom')
                                <a href="{{ route('admin.sales.index') }}" class="btn btn-light btn-sm rounded-2"
                                    data-bs-toggle="tooltip" title="Reset">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- KPI Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 h-100 sales-kpi" style="--kpi-color: #198754;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="sales-kpi-icon"><i class="bi bi-cash-stack"></i></div>
                                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Revenue</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0">₱{{ number_format($totalRevenue, 2) }}</h4>
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.7rem;">All-time: ₱{{ number_format($allTimeRevenue, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 h-100 sales-kpi" style="--kpi-color: #0d6efd;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="sales-kpi-icon"><i class="bi bi-bag-check"></i></div>
                                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Completed Orders</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0">{{ number_format($orderCount) }}</h4>
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.7rem;">in selected period</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 h-100 sales-kpi" style="--kpi-color: #6610f2;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="sales-kpi-icon"><i class="bi bi-receipt"></i></div>
                                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Avg. Order Value</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0">₱{{ number_format($avgOrderValue, 2) }}</h4>
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.7rem;">revenue ÷ orders</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 h-100 sales-kpi" style="--kpi-color: #b95900;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="sales-kpi-icon"><i class="bi bi-box-seam"></i></div>
                                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Items Sold</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0">{{ number_format($itemsSold) }}</h4>
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.7rem;">units across completed orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Revenue Chart -->
                        <div class="col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-dark mb-0">Revenue Over Time</h6>
                                        <span class="badge rounded-pill" style="background-color: rgba(25,135,84,0.12); color: #198754; font-size: 0.65rem;">
                                            {{ $groupByMonth ? 'Monthly' : 'Daily' }}
                                        </span>
                                    </div>
                                    <div id="salesRevenueChart"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Breakdown -->
                        <div class="col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark mb-3">Orders by Status</h6>
                                    @forelse($statusMeta as $key => $meta)
                                        @php $row = $statusBreakdown[$key] ?? null; @endphp
                                        <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <span class="d-inline-flex align-items-center gap-2 small fw-semibold text-dark">
                                                <span class="rounded-circle" style="width: 9px; height: 9px; background-color: {{ $meta['color'] }};"></span>
                                                {{ $meta['label'] }}
                                            </span>
                                            <span class="text-end">
                                                <span class="fw-bold text-dark">{{ number_format($row->total ?? 0) }}</span>
                                                <span class="text-muted small d-block" style="font-size: 0.7rem;">₱{{ number_format($row->amount ?? 0, 2) }}</span>
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">No orders in this period.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Best-selling Products -->
                        <div class="col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-0">
                                    <div class="p-4 pb-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0">Best-Selling Products</h6>
                                        <span class="text-muted small">by revenue</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-2 text-primary small text-uppercase fw-bold border-0">Product</th>
                                                    <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">Qty Sold</th>
                                                    <th class="text-end pe-4 py-2 text-primary small text-uppercase fw-bold border-0">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($topProducts as $p)
                                                    <tr>
                                                        <td class="ps-4 py-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded border bg-white p-1 me-2" style="width: 38px; height: 38px;">
                                                                    <img src="{{ $p->image ?: asset('FABLAB-LOGO.png') }}" class="w-100 h-100 object-fit-cover rounded" alt="">
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold text-dark small">{{ $p->name }}</div>
                                                                    <div class="text-muted font-monospace" style="font-size: 0.7rem;">SKU: {{ $p->sku ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center fw-bold text-dark">{{ number_format($p->qty) }}</td>
                                                        <td class="text-end pe-4 fw-bold text-primary">₱{{ number_format($p->revenue, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center py-4 text-muted small">No sales in this period.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Completed Sales -->
                        <div class="col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-0">
                                    <div class="p-4 pb-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0">Recent Completed Sales</h6>
                                        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="small text-decoration-none">View all</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-2 text-primary small text-uppercase fw-bold border-0">Order</th>
                                                    <th class="py-2 text-primary small text-uppercase fw-bold border-0">Customer</th>
                                                    <th class="py-2 text-primary small text-uppercase fw-bold border-0">Date</th>
                                                    <th class="text-end pe-4 py-2 text-primary small text-uppercase fw-bold border-0">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @forelse($recentSales as $sale)
                                                    <tr>
                                                        <td class="ps-4 py-2 fw-bold text-dark small">
                                                            #{{ $sale->order_number }}
                                                            <span class="text-muted fw-normal d-block" style="font-size: 0.7rem;">{{ $sale->order_items_count }} item(s)</span>
                                                        </td>
                                                        <td class="small text-dark">{{ $sale->user->fullname ?? 'Guest' }}</td>
                                                        <td class="text-muted small">{{ $sale->created_at->format('M d, Y') }}</td>
                                                        <td class="text-end pe-4 fw-bold text-primary">₱{{ number_format($sale->total_amount, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-muted small">No completed sales in this period.</td>
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

    @include('auth.modal-logout')

    <style>
        .sales-kpi {
            border-left: 3px solid var(--kpi-color) !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .sales-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        }
        .sales-kpi-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            background-color: color-mix(in srgb, var(--kpi-color) 14%, transparent);
            color: var(--kpi-color);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = @json($chartLabels);
            const revenue = @json($revenueSeries);
            const orders = @json($orderSeries);

            const options = {
                series: [
                    { name: 'Revenue', type: 'area', data: revenue },
                    { name: 'Completed Orders', type: 'line', data: orders },
                ],
                chart: {
                    height: 340,
                    type: 'line',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, sans-serif',
                    animations: { enabled: true, speed: 500 },
                },
                stroke: { curve: 'smooth', width: [2.5, 2] },
                fill: {
                    type: ['gradient', 'solid'],
                    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.03, stops: [0, 90, 100] },
                },
                colors: ['#198754', '#ffc107'],
                dataLabels: { enabled: false },
                xaxis: {
                    categories: labels,
                    tickAmount: Math.min(labels.length, 12),
                    labels: { style: { fontSize: '11px', colors: '#6c757d' }, rotate: -30, rotateAlways: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: [
                    {
                        seriesName: 'Revenue',
                        labels: { style: { fontSize: '11px', colors: '#6c757d' }, formatter: v => '₱' + Number(v).toLocaleString() },
                        title: { text: 'Revenue (₱)', style: { fontSize: '11px', color: '#6c757d', fontWeight: 600 } },
                    },
                    {
                        seriesName: 'Completed Orders',
                        opposite: true,
                        labels: { style: { fontSize: '11px', colors: '#6c757d' }, formatter: v => Math.round(v) },
                        title: { text: 'Orders', style: { fontSize: '11px', color: '#6c757d', fontWeight: 600 } },
                    },
                ],
                grid: { borderColor: '#f1f1f1', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', markers: { width: 10, height: 10, radius: 12 }, itemMargin: { horizontal: 10 } },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val, opts) {
                            if (opts && opts.seriesIndex === 0) return '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                            return Number(val).toLocaleString() + ' order(s)';
                        },
                    },
                },
                markers: { size: 0, hover: { size: 5 } },
                noData: { text: 'No sales data for this period', style: { color: '#6c757d', fontSize: '13px' } },
            };

            new ApexCharts(document.querySelector('#salesRevenueChart'), options).render();
        });
    </script>
@endsection

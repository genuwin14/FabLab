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
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Combined Stat Row -->
                    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-8 g-2 g-md-3 mb-4">
                        <div class="col">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-clock-history fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">To Review</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $pendingCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-primary-soft text-primary d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-gear-wide-connected fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Processing</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $processingCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.orders.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-info bg-opacity-10 text-info d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-bag-check fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Ready</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $readyCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                <div class="card-body p-2 d-flex align-items-center gap-2">
                                    <div class="rounded-3 bg-success bg-opacity-10 text-success d-inline-flex p-2 flex-shrink-0">
                                        <i class="bi bi-check-circle fs-6"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Completed</div>
                                        <div class="fw-bold fs-6 mb-0 text-dark">{{ $completedTodayCount }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.products.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 {{ $lowStockProducts > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-box-seam fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Products Low</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $lowStockProducts }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.raw-materials.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 {{ $lowStockMaterials > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-boxes fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Materials Low</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $lowStockMaterials }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.textures.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 {{ $lowStockTextures > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-layers fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Textures Low</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $lowStockTextures }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('staff.purchase.index') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 rounded-4 mini-stat">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-info bg-opacity-10 text-info d-inline-flex p-2 flex-shrink-0">
                                            <i class="bi bi-truck fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 0.6rem;">Incoming POs</div>
                                            <div class="fw-bold fs-6 mb-0 text-dark">{{ $incomingPOCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-3 g-md-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-white border-0 p-3 p-md-4 pb-0 d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="fw-bold mb-0">Order Activity</h5>
                                        <p class="text-muted small mb-0">New orders received over the last 7 days</p>
                                    </div>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 small fw-bold flex-shrink-0">
                                        <i class="bi bi-calendar-week me-1"></i>7 Days
                                    </span>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div id="ordersChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-white border-0 p-3 p-md-4 pb-0">
                                    <h5 class="fw-bold mb-0">Active Pipeline</h5>
                                    <p class="text-muted small mb-0">Orders in progress</p>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div id="pipelineChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Queue & Critical Stock -->
                    <div class="row g-3 g-md-4 mb-4">
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-white border-0 p-3 p-md-4 pb-0 d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="fw-bold mb-0">Order Queue</h5>
                                        <p class="text-muted small mb-0">Orders awaiting your action — oldest first</p>
                                    </div>
                                    <a href="{{ route('staff.orders.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary flex-shrink-0">View All</a>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 order-queue-table">
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
                                                                // Written out rather than built from bg-* + text-*
                                                                // utilities: Bootstrap's .text-primary and .bg-primary
                                                                // are the same navy, so that pairing hid the label on
                                                                // its own background. These are the orders table's
                                                                // colours, so a status reads the same on both screens.
                                                                [$statusBg, $statusColor] = match ($order->status) {
                                                                    'approved' => ['rgba(13, 110, 253, 0.12)', '#0d6efd'],
                                                                    'processing' => ['rgba(13, 202, 240, 0.15)', '#087990'],
                                                                    default => ['rgba(255, 193, 7, 0.18)', '#997404'],
                                                                };
                                                            @endphp
                                                            <span class="badge rounded-pill px-2 py-1 text-uppercase"
                                                                style="font-size: 0.65rem; background-color: {{ $statusBg }}; color: {{ $statusColor }};">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-white border-0 p-3 p-md-4 pb-0 d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="fw-bold mb-0">Critical Stock</h5>
                                        <p class="text-muted small mb-0">Products below threshold</p>
                                    </div>
                                    <a href="{{ route('staff.products.index') }}" class="btn btn-light btn-sm rounded-circle border text-muted flex-shrink-0">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                                <div class="card-body p-3 p-md-4 critical-stock-list">
                                    @forelse($criticalStockProducts as $product)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3">
                                                @if($product->image_url)
                                                    <img src="{{ $product->image_url }}" class="rounded-3 shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
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

        /* ── Chart heights ──
           (No inline min-height so the media query can win — see
           ResponsiveMobileNote.md §7.) */
        #ordersChart { min-height: 300px; }
        #pipelineChart { min-height: 300px; }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md
           ============================================ */
        @media (max-width: 991.98px) {
            #ordersChart { min-height: 240px; }
            #pipelineChart { min-height: 260px; }

            /* Compact, scroll-capped Critical Stock list so it doesn't push
               the rest of the dashboard far down the page on a phone. */
            .critical-stock-list {
                max-height: 340px;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .critical-stock-list > .d-flex {
                margin-bottom: 0.25rem !important;
                padding-top: 0.4rem !important;
                padding-bottom: 0.4rem !important;
            }
            .critical-stock-list::-webkit-scrollbar { width: 4px; }
            .critical-stock-list::-webkit-scrollbar-track { background: transparent; }
            .critical-stock-list::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, 0.15);
                border-radius: 10px;
            }

            /* Order Queue table: keep columns from squishing — swipe. */
            .order-queue-table {
                min-width: 520px;
            }
            .order-queue-table th,
            .order-queue-table td {
                white-space: nowrap;
            }

            /* Smaller card type so the mini-stat tiles and card headers stay
               proportionate when squeezed (2-up phone, 4-up tablet). */
            .mini-stat .text-uppercase { font-size: 0.58rem !important; }
            .mini-stat .fs-6 { font-size: 0.95rem !important; }
            .mini-stat .rounded-3 { padding: 0.4rem !important; }

            .card-header h5 { font-size: 1rem !important; }
            .card-header p { font-size: 0.72rem !important; }
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
                    type: 'line',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: 5,
                    strokeWidth: 2,
                    hover: { size: 7 }
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
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 240 },
                        stroke: { width: 2 },
                        markers: { size: 3 },
                        xaxis: { labels: { style: { fontSize: '10px' } } },
                        yaxis: { labels: { style: { fontSize: '10px' } } }
                    }
                }]
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
                stroke: { width: 0 },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 260 },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '11px'
                        }
                    }
                }]
            };
            new ApexCharts(document.querySelector("#pipelineChart"), pipelineOptions).render();
        });
    </script>
@endsection

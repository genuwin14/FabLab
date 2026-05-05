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
                        $hour = (int) now()->format('H');
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    @endphp

                    <!-- Welcome Header -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h3 class="fw-bold text-primary mb-1">Dashboard Overview</h3>
                            <p class="text-muted small mb-0">{{ $greeting }}, {{ Auth::user()->fullname }}. Here's what's happening today, {{ now()->format('F j, Y') }}.</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($lowStockCount > 0)
                                <a href="{{ route('admin.inventory.index') }}" class="btn btn-white rounded-pill px-3 shadow-sm border small fw-bold text-danger hover-accent">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $lowStockCount }} Stock Alerts
                                </a>
                            @endif
                            @if($pendingOrdersCount > 0)
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-white rounded-pill px-3 shadow-sm border small fw-bold text-warning hover-accent">
                                    <i class="bi bi-clock-history me-2"></i>{{ $pendingOrdersCount }} Pending
                                </a>
                            @endif
                            <button class="btn btn-primary rounded-pill px-3 shadow-sm small fw-bold">
                                <i class="bi bi-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>

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
                            <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100 mini-stat">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Sales Revenue Trend</h5>
                                        <p class="text-muted small mb-0">Last 6 months performance</p>
                                    </div>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 small fw-bold">
                                        <i class="bi bi-graph-up me-1"></i>Monthly
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <div id="salesChart" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Order Status</h5>
                                    <p class="text-muted small mb-0">Distribution across statuses</p>
                                </div>
                                <div class="card-body p-4">
                                    <div id="orderStatusChart" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Health -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold text-primary mb-0">Inventory Health</h5>
                                <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">
                                    Stock Monitoring <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        @php
                            $healthCards = [
                                ['key' => 'products', 'label' => 'Products', 'icon' => 'bi-box-seam', 'color' => 'primary', 'route' => route('admin.products.index')],
                                ['key' => 'materials', 'label' => 'Raw Materials', 'icon' => 'bi-boxes', 'color' => 'info', 'route' => route('admin.raw-materials.index')],
                                ['key' => 'textures', 'label' => 'Textures', 'icon' => 'bi-layers', 'color' => 'warning', 'route' => route('admin.textures.index')],
                            ];
                        @endphp
                        @foreach($healthCards as $card)
                            @php $h = $inventoryHealth[$card['key']]; @endphp
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100 inv-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-3 {{ $card['color'] === 'primary' ? 'bg-primary-soft' : 'bg-' . $card['color'] . ' bg-opacity-10' }} p-2 text-{{ $card['color'] }} me-3">
                                                    <i class="bi {{ $card['icon'] }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0">{{ $card['label'] }}</h6>
                                                    <p class="text-muted small mb-0">{{ number_format($h['total']) }} total items</p>
                                                </div>
                                            </div>
                                            <a href="{{ $card['route'] }}" class="btn btn-light btn-sm rounded-circle border text-muted">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="p-3 rounded-3 bg-light">
                                                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem;">Stock Value</div>
                                                    <div class="fw-bold text-dark">₱{{ number_format($h['value'], 0) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-3 rounded-3 {{ $h['low_stock'] > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' }}">
                                                    <div class="{{ $h['low_stock'] > 0 ? 'text-danger' : 'text-success' }} small text-uppercase fw-bold" style="font-size: 0.65rem;">Low Stock</div>
                                                    <div class="fw-bold {{ $h['low_stock'] > 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $h['low_stock'] }} {{ $h['low_stock'] > 0 ? 'alerts' : 'OK' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($h['total'] > 0)
                                            @php $healthPct = max(0, 100 - round(($h['low_stock'] / $h['total']) * 100)); @endphp
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Health</span>
                                                    <span class="fw-bold text-{{ $healthPct >= 80 ? 'success' : ($healthPct >= 50 ? 'warning' : 'danger') }}">{{ $healthPct }}%</span>
                                                </div>
                                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                                    <div class="progress-bar bg-{{ $healthPct >= 80 ? 'success' : ($healthPct >= 50 ? 'warning' : 'danger') }}"
                                                         role="progressbar" style="width: {{ $healthPct }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Recent Transactions & Critical Alerts -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Recent Transactions</h5>
                                        <p class="text-muted small mb-0">Latest customer orders</p>
                                    </div>
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">View All</a>
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
                                                            <div class="fw-bold text-dark small">{{ $order->user->fullname ?? 'N/A' }}</div>
                                                            <div class="text-muted small" style="font-size: 0.7rem;">{{ $order->created_at->diffForHumans() }}</div>
                                                        </td>
                                                        <td class="fw-bold text-dark small">₱{{ number_format($order->total_amount, 2) }}</td>
                                                        <td>
                                                            @php
                                                                $statusClass = 'bg-secondary text-secondary';
                                                                if($order->status === 'pending') $statusClass = 'bg-warning text-warning';
                                                                if($order->status === 'approved') $statusClass = 'bg-info text-info';
                                                                if($order->status === 'processing') $statusClass = 'bg-primary text-primary';
                                                                if($order->status === 'ready_for_pickup') $statusClass = 'bg-info text-info';
                                                                if($order->status === 'completed') $statusClass = 'bg-success text-success';
                                                                if($order->status === 'cancelled') $statusClass = 'bg-danger text-danger';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }} bg-opacity-10 rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.65rem;">
                                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm rounded-circle text-primary border">
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
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Critical Stock</h5>
                                        <p class="text-muted small mb-0">Products needing restock</p>
                                    </div>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm rounded-circle border text-muted">
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

                    <!-- Top Products & Recent Purchase Orders -->
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Top Products</h5>
                                        <p class="text-muted small mb-0">Best performers by units sold</p>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    @forelse($topProducts as $index => $product)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3 position-relative">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded-3 shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                                <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;">
                                                    #{{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate small">{{ $product->name }}</h6>
                                                <p class="text-muted mb-0 small">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                            </div>
                                            <div class="text-end flex-shrink-0 ms-2">
                                                <div class="fw-bold text-primary small">{{ $product->total_sold ?? 0 }} sold</div>
                                                <div class="text-muted small" style="font-size: 0.7rem;">₱{{ number_format($product->price, 2) }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center py-4">No top products yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-0">Recent Purchase Orders</h5>
                                        <p class="text-muted small mb-0">Incoming inventory shipments</p>
                                    </div>
                                    <a href="{{ route('admin.purchase.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border small text-primary">View All</a>
                                </div>
                                <div class="card-body p-4">
                                    @forelse($recentPurchaseOrders as $po)
                                        <div class="d-flex align-items-center mb-3 p-2 rounded-3 hover-bg-light transition-all">
                                            <div class="flex-shrink-0 me-3">
                                                @php
                                                    $poColor = 'secondary';
                                                    if($po->status === 'draft') $poColor = 'secondary';
                                                    if($po->status === 'sent') $poColor = 'info';
                                                    if($po->status === 'confirmed') $poColor = 'primary';
                                                    if($po->status === 'delivered') $poColor = 'success';
                                                    if($po->status === 'cancelled') $poColor = 'danger';
                                                @endphp
                                                <div class="rounded-3 bg-{{ $poColor }} bg-opacity-10 d-flex align-items-center justify-content-center text-{{ $poColor }}" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-bag-check fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate small font-monospace">{{ $po->po_number }}</h6>
                                                <p class="text-muted mb-0 small">
                                                    {{ $po->supplier->name ?? 'N/A' }}
                                                    @if($po->expected_delivery_date)
                                                        · ETA {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M j') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-end flex-shrink-0 ms-2">
                                                <div class="fw-bold text-dark small">₱{{ number_format($po->total_cost ?? 0, 2) }}</div>
                                                <span class="badge bg-{{ $poColor }} bg-opacity-10 text-{{ $poColor }} rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.6rem;">
                                                    {{ ucfirst($po->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <i class="bi bi-inbox text-muted fs-1"></i>
                                            <p class="text-muted small mb-0 mt-2">No recent purchase orders.</p>
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
            cursor: default;
        }
        a > .stat-card { cursor: pointer; }
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
            opacity: 0.04;
            border-radius: 50%;
            z-index: 0;
        }
        .mini-stat {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .mini-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.05) !important;
        }
        .inv-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .inv-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
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
                    height: 320,
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
                        formatter: function (val) { return '₱' + Number(val).toLocaleString(); }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (val) { return '₱' + Number(val).toLocaleString(); }
                    }
                },
                colors: ['#0d6efd']
            };
            new ApexCharts(document.querySelector("#salesChart"), salesOptions).render();

            // Order Status Donut Chart
            const orderStatusData = @json($orderStatusBreakdown);
            const statusColorMap = {
                'pending': '#ffc107',
                'approved': '#0dcaf0',
                'processing': '#0d6efd',
                'ready_for_pickup': '#6610f2',
                'completed': '#198754',
                'cancelled': '#dc3545'
            };
            const orderStatusLabels = orderStatusData.map(s => s.status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
            const orderStatusSeries = orderStatusData.map(s => Number(s.count));
            const orderStatusColors = orderStatusData.map(s => statusColorMap[s.status] || '#6c757d');

            const orderStatusOptions = {
                series: orderStatusSeries.length ? orderStatusSeries : [1],
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: orderStatusLabels.length ? orderStatusLabels : ['No Orders'],
                colors: orderStatusColors.length ? orderStatusColors : ['#e9ecef'],
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
                                    label: 'Total',
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
            new ApexCharts(document.querySelector("#orderStatusChart"), orderStatusOptions).render();
        });
    </script>
@endsection

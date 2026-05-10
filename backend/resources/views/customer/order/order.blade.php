@extends('layout.app')

@section('content')
    <style>
        /* ============================================
           Order History — Grid / Table View Toggle
           ============================================ */
        .order-view-toggle {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .order-view-btn {
            border: 0;
            background: transparent;
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .order-view-btn:hover {
            background-color: #f1f4f8;
            color: #0e2e45;
        }
        .order-view-btn.active {
            background-color: #0e2e45;
            color: #ffc508;
        }
        .order-view-btn.active:hover {
            background-color: #0e2e45;
            color: #ffc508;
        }

        /* ============================================
           Order History — Mini Stat Cards
           ============================================ */
        .order-stat-mini {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-left: 3px solid var(--c, #0e2e45);
            border-radius: 10px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            min-width: 112px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .order-stat-mini:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        }
        .order-stat-mini-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(14, 46, 69, 0.08);
            color: var(--c, #0e2e45);
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* ============================================
           Order Details Drawer (admin theme)
           ============================================ */
        .customer-order-details-drawer { background-color: #fff; }

        .customer-order-details-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 22px 22px 18px;
            position: relative;
        }
        .customer-order-details-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }

        .customer-order-details-eyebrow {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }

        .customer-order-details-close {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .customer-order-details-close:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .customer-order-details-body { background-color: #fff; }

        .customer-order-details-summary {
            background-color: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 12px;
            padding: 14px 16px;
        }

        .customer-order-details-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .customer-order-details-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            background-color: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .customer-order-details-item:hover {
            border-color: rgba(14, 46, 69, 0.18);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
    </style>

    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100" style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class='d-none d-md-block sidebar-spacer flex-shrink-0' style='width: 280px;'></div>

        <!-- Spacer for fixed sidebar -->

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">
                    @php
                        $orderStats = [
                            ['label' => 'Total',     'count' => $orders->count(),                                  'icon' => 'bi-receipt',           'color' => '#0e2e45'],
                            ['label' => 'Pending',   'count' => $orders->where('status', 'pending')->count(),      'icon' => 'bi-hourglass-split',   'color' => '#997404'],
                            ['label' => 'For Pickup','count' => $orders->where('status', 'ready_for_pickup')->count(), 'icon' => 'bi-bag-check',     'color' => '#b95900'],
                            ['label' => 'Completed', 'count' => $orders->where('status', 'completed')->count(),    'icon' => 'bi-check-circle-fill', 'color' => '#198754'],
                            ['label' => 'Cancelled', 'count' => $orders->where('status', 'cancelled')->count(),    'icon' => 'bi-x-circle-fill',     'color' => '#dc3545'],
                        ];
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">Order History</h3>
                            <p class="text-muted small mb-0">Review your past orders and their statuses.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($orderStats as $stat)
                                <div class="order-stat-mini" style="--c: {{ $stat['color'] }};">
                                    <div class="order-stat-mini-icon">
                                        <i class="bi {{ $stat['icon'] }}"></i>
                                    </div>
                                    <div class="lh-1">
                                        <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                            {{ $stat['count'] }}
                                        </div>
                                        <div class="text-muted text-uppercase fw-bold mt-1"
                                            style="font-size: 0.6rem; letter-spacing: 0.04em;">
                                            {{ $stat['label'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($orders->count() > 0)
                        <!-- Filters & Search -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex flex-nowrap align-items-center gap-2">
                                    <div class="btn-group order-view-toggle flex-shrink-0" role="group"
                                        aria-label="View mode">
                                        <button type="button" class="btn order-view-btn" data-view="grid"
                                            data-bs-toggle="tooltip" title="Grid view">
                                            <i class="bi bi-grid-3x3-gap-fill"></i>
                                        </button>
                                        <button type="button" class="btn order-view-btn active" data-view="table"
                                            data-bs-toggle="tooltip" title="Table view">
                                            <i class="bi bi-list-ul"></i>
                                        </button>
                                    </div>
                                    <div class="input-group flex-grow-1" style="min-width: 0;">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" id="orderSearchInput"
                                            class="form-control border-start-0 rounded-end-2 ps-0"
                                            placeholder="Search by Order ID...">
                                    </div>
                                    <select id="orderStatusFilter"
                                        class="form-select rounded-2 flex-shrink-0 w-auto">
                                        <option value="">All Statuses</option>
                                        @foreach(['pending', 'approved', 'processing', 'ready_for_pickup', 'completed', 'cancelled'] as $s)
                                            <option value="{{ $s }}">
                                                {{ ucwords(str_replace('_', ' ', $s)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select id="orderDateFilter"
                                        class="form-select rounded-2 flex-shrink-0 w-auto">
                                        <option value="">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                    </select>
                                    <button type="button" id="orderFilterReset"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                        title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="orderNoResults" class="text-center py-5 d-none">
                            <div class="mb-3">
                                <i class="bi bi-search text-muted display-4 opacity-25"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No matching orders</h5>
                            <p class="text-muted small mb-0">Try a different search term or status.</p>
                        </div>

                        <div class="row g-4 d-none" id="orderGrid">
                            @foreach($orders as $order)
                                <div class="col-12 col-md-6 col-lg-4 order-card-wrapper"
                                    data-order-number="{{ strtolower($order->order_number) }}"
                                    data-status="{{ $order->status }}"
                                    data-created-at="{{ $order->created_at->toDateString() }}">
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                        <div class="card-header bg-white border-bottom p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted small text-uppercase fw-bold">Order
                                                    #{{ $order->order_number }}</span>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-warning text-dark',
                                                        'processing' => 'bg-info text-white',
                                                        'ready_for_pickup' => 'bg-primary text-white',
                                                        'completed' => 'bg-success text-white',
                                                        'cancelled' => 'bg-danger text-white',
                                                    ];
                                                    $statusLabel = ucwords(str_replace('_', ' ', $order->status));
                                                    $badgeClass = $statusColors[$order->status] ?? 'bg-secondary text-white';
                                                @endphp
                                                <span
                                                    class="badge {{ $badgeClass }} rounded-pill px-2 py-1">{{ $statusLabel }}</span>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('M d, Y') }}
                                            </div>
                                        </div>

                                        <div class="card-body p-3 d-flex flex-column">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small">Total Amount</span>
                                                    <span
                                                        class="fw-bold text-primary fs-5">₱{{ number_format($order->total_amount, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Items</span>
                                                    <span class="fw-medium">{{ $order->orderItems->sum('quantity') }} items</span>
                                                </div>
                                            </div>

                                            @if($order->status == 'pending')
                                                <div class="alert alert-warning d-flex align-items-start mb-3 p-2 small border-0 bg-warning bg-opacity-10 text-dark rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-hourglass-split me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Waiting for Approval</span><br>
                                                        Please wait for admin approval before paying.
                                                    </div>
                                                </div>
                                            @endif

                                            @if($order->status == 'approved')
                                                <div class="alert alert-info d-flex align-items-start mb-3 p-2 small border-0 bg-info bg-opacity-10 text-primary rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-envelope-check-fill me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Order Approved - Action Required</span><br>
                                                        A transaction slip has been sent to your email. Please present it to the cashier
                                                        to process your payment.
                                                    </div>
                                                </div>
                                            @endif

                                            @if($order->status == 'processing')
                                                <div class="alert alert-info d-flex align-items-start mb-3 p-2 small border-0 bg-info bg-opacity-10 text-info rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-gear-wide-connected me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Processing Order</span><br>
                                                        Your order is being processed by our staff. Please wait for updates.
                                                    </div>
                                                </div>
                                            @endif

                                            @if($order->status == 'ready_for_pickup')
                                                <div class="alert alert-success d-flex align-items-start mb-3 p-2 small border-0 bg-success bg-opacity-10 text-success rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-geo-alt-fill me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Ready for Pickup!</span><br>
                                                        Please proceed to Fablab office to get your product.<br>
                                                        And bring the payment receipt for checking.
                                                    </div>
                                                </div>
                                            @endif

                                            @if($order->status == 'completed')
                                                <div class="alert alert-success d-flex align-items-start mb-3 p-2 small border-0 bg-success bg-opacity-10 text-success rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-patch-check-fill me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Order Completed</span><br>
                                                        Thank you for choosing Fablab! We hope to serve you again soon.
                                                    </div>
                                                </div>
                                            @endif

                                            @if($order->status == 'cancelled' && $order->reason)
                                                <div class="alert alert-danger d-flex align-items-start mb-3 p-2 small border-0 bg-danger bg-opacity-10 text-danger rounded-3"
                                                    role="alert">
                                                    <i class="bi bi-x-circle-fill me-2 mt-1"></i>
                                                    <div class="lh-sm">
                                                        <span class="fw-bold">Order Cancelled</span><br>
                                                        Reason: {{ $order->reason }}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mt-auto">
                                                <div class="d-flex gap-2">
                                                    <button
                                                        class="btn btn-light text-primary fw-bold btn-sm rounded-pill flex-grow-1"
                                                        type="button" data-bs-toggle="offcanvas"
                                                        data-bs-target="#orderDetails-{{ $order->order_id }}"
                                                        aria-controls="orderDetails-{{ $order->order_id }}">
                                                        Details <i class="bi bi-arrow-right-short ms-1"></i>
                                                    </button>

                                                    @if($order->status == 'pending')
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm rounded-pill flex-grow-1"
                                                            data-bs-toggle="modal" data-bs-target="#cancelOrderModal"
                                                            data-order-id="{{ $order->order_id }}"
                                                            data-order-number="{{ $order->order_number }}"
                                                            data-url="{{ route('customer.orders.cancel', $order->order_id) }}">
                                                            Cancel
                                                        </button>
                                                    @elseif($order->status == 'processing')
                                                        <span
                                                            class="badge bg-light text-muted border rounded-pill d-flex align-items-center justify-content-center flex-grow-1">
                                                            <i class="bi bi-lock-fill me-1"></i> Approved
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Table View -->
                        <div id="orderTable" class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr class="bg-primary bg-opacity-10">
                                                <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">Order ID</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Date</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Items</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Total</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                                <th class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            @foreach($orders as $order)
                                                @php
                                                    [$rowStatusBg, $rowStatusColor, $rowStatusIcon] = match ($order->status) {
                                                        'completed' => ['rgba(25, 135, 84, 0.12)', '#198754', 'bi-check-circle-fill'],
                                                        'approved' => ['rgba(13, 110, 253, 0.12)', '#0d6efd', 'bi-clipboard-check'],
                                                        'processing' => ['rgba(13, 202, 240, 0.15)', '#087990', 'bi-arrow-repeat'],
                                                        'ready_for_pickup' => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-bag-check'],
                                                        'cancelled' => ['rgba(220, 53, 69, 0.12)', '#dc3545', 'bi-x-circle-fill'],
                                                        default => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-hourglass-split'],
                                                    };
                                                @endphp
                                                <tr class="order-row-wrapper"
                                                    data-order-number="{{ strtolower($order->order_number) }}"
                                                    data-status="{{ $order->status }}"
                                                    data-created-at="{{ $order->created_at->toDateString() }}">
                                                    <td class="ps-4 py-3 fw-bold text-dark">#{{ $order->order_number }}</td>
                                                    <td class="text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td class="fw-bold text-dark">{{ $order->orderItems->sum('quantity') }}</td>
                                                    <td class="fw-bold text-primary">₱{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>
                                                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                            style="background-color: {{ $rowStatusBg }}; color: {{ $rowStatusColor }};">
                                                            <i class="bi {{ $rowStatusIcon }}" style="font-size: 0.75rem;"></i>
                                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-inline-flex gap-2">
                                                            <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold border" type="button"
                                                                data-bs-toggle="offcanvas"
                                                                data-bs-target="#orderDetails-{{ $order->order_id }}"
                                                                aria-controls="orderDetails-{{ $order->order_id }}">
                                                                <i class="bi bi-eye me-1 text-primary"></i>Details
                                                            </button>
                                                            @if($order->status == 'pending')
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                                    data-bs-toggle="modal" data-bs-target="#cancelOrderModal"
                                                                    data-order-id="{{ $order->order_id }}"
                                                                    data-order-number="{{ $order->order_number }}"
                                                                    data-url="{{ route('customer.orders.cancel', $order->order_id) }}">
                                                                    <i class="bi bi-x-lg me-1"></i>Cancel
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-bag-x text-muted display-1 opacity-25"></i>
                            </div>
                            <h4 class="fw-bold text-dark">No orders found</h4>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="{{ route('customer.shop') }}"
                                class="btn btn-primary rounded-pill px-5 fw-bold mt-3 shadow-sm">
                                Start Shopping
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </main>
    </div>
    </div>

    {{-- Order Details Right Drawer (one per order) --}}
    @if($orders->count() > 0)
        @each('customer.order.components.details-drawer', $orders, 'order')
    @endif

    @include('customer.order.components.cancel-modal')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Order search, status & date filter
                var searchInput = document.getElementById('orderSearchInput');
                var statusFilter = document.getElementById('orderStatusFilter');
                var dateFilter = document.getElementById('orderDateFilter');
                var resetBtn = document.getElementById('orderFilterReset');
                var noResults = document.getElementById('orderNoResults');
                var grid = document.getElementById('orderGrid');

                function matchesDateRange(dateStr, range) {
                    if (!range || !dateStr) return true;
                    var d = new Date(dateStr + 'T00:00:00');
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (range === 'today') {
                        return d.getTime() === today.getTime();
                    }
                    if (range === 'week') {
                        var day = today.getDay(); // 0 = Sun
                        var weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - day);
                        return d >= weekStart && d <= today;
                    }
                    if (range === 'month') {
                        return d.getFullYear() === today.getFullYear()
                            && d.getMonth() === today.getMonth();
                    }
                    return true;
                }

                function applyOrderFilters() {
                    if (!grid) return;
                    var term = (searchInput.value || '').trim().toLowerCase();
                    var status = statusFilter.value || '';
                    var range = dateFilter.value || '';
                    var items = document.querySelectorAll('.order-card-wrapper, .order-row-wrapper');
                    var visibleCards = 0;

                    items.forEach(function (item) {
                        var matchesTerm = !term || (item.dataset.orderNumber || '').indexOf(term) !== -1;
                        var matchesStatus = !status || item.dataset.status === status;
                        var matchesDate = matchesDateRange(item.dataset.createdAt, range);
                        if (matchesTerm && matchesStatus && matchesDate) {
                            item.classList.remove('d-none');
                            if (item.classList.contains('order-card-wrapper')) visibleCards++;
                        } else {
                            item.classList.add('d-none');
                        }
                    });

                    var filtersActive = term !== '' || status !== '' || range !== '';
                    noResults.classList.toggle('d-none', visibleCards !== 0 || !filtersActive);
                }

                if (searchInput) searchInput.addEventListener('input', applyOrderFilters);
                if (statusFilter) statusFilter.addEventListener('change', applyOrderFilters);
                if (dateFilter) dateFilter.addEventListener('change', applyOrderFilters);
                if (resetBtn) resetBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    statusFilter.value = '';
                    dateFilter.value = '';
                    applyOrderFilters();
                });

                // Grid / Table view toggle
                var viewToggleBtns = document.querySelectorAll('.order-view-btn');
                var orderTable = document.getElementById('orderTable');
                var STORAGE_KEY = 'customerOrderView';

                function setOrderView(mode) {
                    if (!grid || !orderTable) return;
                    var isGrid = mode !== 'table';
                    grid.classList.toggle('d-none', !isGrid);
                    orderTable.classList.toggle('d-none', isGrid);
                    viewToggleBtns.forEach(function (btn) {
                        btn.classList.toggle('active', btn.dataset.view === (isGrid ? 'grid' : 'table'));
                    });
                    try { localStorage.setItem(STORAGE_KEY, isGrid ? 'grid' : 'table'); } catch (e) {}
                }

                viewToggleBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        setOrderView(btn.dataset.view);
                    });
                });

                try {
                    var saved = localStorage.getItem(STORAGE_KEY);
                    setOrderView(saved === 'grid' ? 'grid' : 'table');
                } catch (e) {
                    setOrderView('table');
                }

                // Bootstrap tooltip init for any data-bs-toggle="tooltip"
                if (window.bootstrap && bootstrap.Tooltip) {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                        new bootstrap.Tooltip(el);
                    });
                }

                var cancelModal = document.getElementById('cancelOrderModal');
                if (cancelModal) {
                    cancelModal.addEventListener('show.bs.modal', function (event) {
                        // Button that triggered the modal
                        var button = event.relatedTarget;
                        // Extract info from data-bs-* attributes
                        var orderNumber = button.getAttribute('data-order-number');
                        var cancelUrl = button.getAttribute('data-url');

                        // Update the modal's content.
                        var modalOrderNumberSpan = cancelModal.querySelector('#cancelOrderNumber');
                        var modalForm = cancelModal.querySelector('#cancelOrderForm');

                        modalOrderNumberSpan.textContent = '#' + orderNumber;
                        modalForm.action = cancelUrl;
                    });
                }
            });
        </script>
    @endpush
@endsection
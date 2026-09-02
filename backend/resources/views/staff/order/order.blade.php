@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

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
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto; overflow-x: hidden;">
                <div class="container-fluid">

                    <!-- Status Analytics -->
                    @php
                        $statusMeta = [
                            'pending'          => ['label' => 'Pending',          'icon' => 'bi-hourglass-split',   'bg' => 'rgba(255, 193, 7, 0.15)',  'color' => '#997404'],
                            'approved'         => ['label' => 'Approved',         'icon' => 'bi-clipboard-check',   'bg' => 'rgba(13, 110, 253, 0.12)', 'color' => '#0d6efd'],
                            'processing'       => ['label' => 'Processing',       'icon' => 'bi-arrow-repeat',      'bg' => 'rgba(13, 202, 240, 0.15)', 'color' => '#087990'],
                            'awaiting_pr'      => ['label' => 'Awaiting PR',       'icon' => 'bi-file-earmark-text', 'bg' => 'rgba(108, 117, 125, 0.15)','color' => '#5c636a'],
                            'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => 'bi-bag-check',         'bg' => 'rgba(255, 153, 0, 0.15)',  'color' => '#b95900'],
                            'for_delivery'     => ['label' => 'For Delivery',     'icon' => 'bi-truck',             'bg' => 'rgba(111, 66, 193, 0.14)', 'color' => '#6f42c1'],
                            'completed'        => ['label' => 'Completed',        'icon' => 'bi-check-circle-fill', 'bg' => 'rgba(25, 135, 84, 0.12)',  'color' => '#198754'],
                            'cancelled'        => ['label' => 'Cancelled',        'icon' => 'bi-x-circle-fill',     'bg' => 'rgba(220, 53, 69, 0.12)',  'color' => '#dc3545'],
                        ];
                    @endphp
                    {{-- One row of eight: a count per status, read-only. Filtering
                         is the dropdown's job, so these only report. --}}
                    <div class="order-stat-grid mb-4">
                        @foreach($statusMeta as $key => $meta)
                            @php
                                $isActive = $status === $key;
                                $count = $statusCounts[$key] ?? 0;
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4 order-stat-card {{ $isActive ? 'order-stat-card-active' : '' }}"
                                style="--stat-color: {{ $meta['color'] }};">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 d-none d-xxl-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 38px; height: 38px; background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }};">
                                            <i class="bi {{ $meta['icon'] }}"></i>
                                        </div>
                                        <div class="d-flex flex-column lh-1 flex-grow-1 min-w-0">
                                            <span class="text-uppercase fw-bold text-muted order-stat-label">
                                                {{ $meta['label'] }}
                                            </span>
                                            <h5 class="fw-bold text-dark mb-0 mt-1 order-stat-count">{{ $count }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="orderFilterForm" method="GET" action="{{ route('staff.orders.index') }}"
                                class="row g-2 align-items-center">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <!-- Search: inline on desktop; icon-triggered dropdown on mobile. -->
                                <div class="col-auto col-lg dropdown search-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none search-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Search">
                                        <i class="bi bi-search text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu search-menu border-0 shadow p-2 p-lg-0">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                                                class="form-control border-start-0 rounded-end-2 ps-0"
                                                placeholder="Search by Order ID, Ref No, or Customer...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status + Date filters: inline on desktop; behind a filter icon on mobile. -->
                                <div class="col-auto dropdown filter-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none filter-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Filters">
                                        <i class="bi bi-funnel text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
                                        <div class="d-flex flex-column flex-lg-row gap-2">
                                            <select name="status" autocomplete="off" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('orderFilterForm').submit()">
                                                <option value="">All Statuses</option>
                                                @foreach(['pending', 'awaiting_pr', 'approved', 'processing', 'ready_for_pickup', 'for_delivery', 'completed', 'cancelled'] as $s)
                                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                                                        {{ \App\Models\Order::statusLabel($s) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <select name="date" autocomplete="off" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('orderFilterForm').submit()">
                                                <option value="">All Time</option>
                                                <option value="today" {{ $date === 'today' ? 'selected' : '' }}>Today</option>
                                                <option value="week" {{ $date === 'week' ? 'selected' : '' }}>This Week</option>
                                                <option value="month" {{ $date === 'month' ? 'selected' : '' }}>This Month</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('staff.orders.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                        title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        title="Export">
                                        <i class="bi bi-download small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Export</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden data-table-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Order ID</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Ref No
                                            </th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Customer
                                            </th>
                                            {{-- No Date column: the order number carries its date (ORDR-YYYYMMDD-####). --}}
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Items</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Total</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                            <th
                                                class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($orders as $order)
                                            @php
                                                $customCount = $order->orderItems->whereNotNull('custom_design_id')->count();
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3 fw-bold text-dark">
                                                    #{{ $order->order_number }}
                                                    @if($customCount > 0)
                                                        <span class="ms-1 text-primary" title="Contains Customized Items">
                                                            <i class="bi bi-palette-fill" style="font-size: 0.75rem;"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($order->payment_reference)
                                                        <span class="font-monospace small text-muted">
                                                            {{ $order->payment_reference }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center"
                                                            style="width: 36px; height: 36px; background-color: #0e2e45; color: #ffc508; font-size: 0.78rem; letter-spacing: 0.02em;">
                                                            {{ strtoupper(substr($order->user->fullname ?? 'G', 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark small mb-0">
                                                                {{ $order->user->fullname ?? 'Guest' }}
                                                            </div>
                                                            <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-dark">{{ $order->orderItems->count() }}</td>
                                                <td class="fw-bold text-primary">
                                                    ₱{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td>
                                                    @php
                                                        [$statusBg, $statusColor, $statusIcon] = match ($order->status) {
                                                            'completed' => ['rgba(25, 135, 84, 0.12)', '#198754', 'bi-check-circle-fill'],
                                                            'approved' => ['rgba(13, 110, 253, 0.12)', '#0d6efd', 'bi-clipboard-check'],
                                                            'processing' => ['rgba(13, 202, 240, 0.15)', '#087990', 'bi-arrow-repeat'],
                                                            'awaiting_pr' => ['rgba(108, 117, 125, 0.15)', '#5c636a', 'bi-file-earmark-text'],
                                                            'ready_for_pickup' => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-bag-check'],
                                                            'for_delivery' => ['rgba(111, 66, 193, 0.14)', '#6f42c1', 'bi-truck'],
                                                            'cancelled' => ['rgba(220, 53, 69, 0.12)', '#dc3545', 'bi-x-circle-fill'],
                                                            default => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-hourglass-split'],
                                                        };
                                                    @endphp
                                                    <span
                                                        class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                        style="background-color: {{ $statusBg }}; color: {{ $statusColor }};">
                                                        <i class="bi {{ $statusIcon }}" style="font-size: 0.75rem;"></i>
                                                        {{ \App\Models\Order::statusLabel($order->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    @php
                                                        // A PR order is driven by the admin's paperwork; the only
                                                        // step staff take is handing it over after delivery.
                                                        $isPr = $order->isPurchaseRequest();

                                                        $nextStatus = $isPr
                                                            ? ($order->status === 'for_delivery' ? 'completed' : null)
                                                            : match ($order->status) {
                                                                'approved' => 'processing',
                                                                'processing' => 'ready_for_pickup',
                                                                'ready_for_pickup' => 'completed',
                                                                default => null,
                                                            };

                                                        $btnLabel = match ($nextStatus) {
                                                            'processing' => 'Process',
                                                            'ready_for_pickup' => 'Ready',
                                                            'completed' => 'Complete',
                                                            default => null,
                                                        };

                                                        $btnClass = match ($nextStatus) {
                                                            'processing' => 'btn-info text-white',
                                                            'ready_for_pickup' => 'btn-primary',
                                                            'completed' => 'btn-success text-white',
                                                            default => '',
                                                        };
                                                    @endphp

                                                    @if($order->status === 'pending')
                                                        <span class="badge bg-light text-muted border table-action-btn px-3 py-2 me-1 fw-semibold small">
                                                            <i class="bi bi-hourglass-split me-1"></i>Awaiting admin
                                                        </span>
                                                    @elseif($order->status === 'awaiting_pr')
                                                        <span class="badge bg-light text-muted border table-action-btn px-3 py-2 me-1 fw-semibold small">
                                                            <i class="bi bi-file-earmark-text me-1"></i>Awaiting PR number
                                                        </span>
                                                    @elseif($isPr && in_array($order->status, ['approved', 'processing']))
                                                        <span class="badge bg-light text-muted border table-action-btn px-3 py-2 me-1 fw-semibold small">
                                                            <i class="bi bi-hourglass-split me-1"></i>Awaiting {{ $order->status === 'approved' ? 'NOA' : 'PO' }}
                                                        </span>
                                                    @elseif($nextStatus)
                                                        <button class="btn {{ $btnClass }} btn-sm table-action-btn px-3 fw-bold shadow-sm btn-update-status me-1"
                                                            data-id="{{ $order->order_id }}" data-status="{{ $order->status }}"
                                                            data-next-status="{{ $nextStatus }}">
                                                            <i class="bi bi-arrow-right-circle me-1"></i>{{ $btnLabel }}
                                                        </button>
                                                    @endif

                                                    <button class="btn btn-light btn-sm table-action-btn px-3 fw-bold shadow-sm border btn-view-order"
                                                        data-order='@json($order)'
                                                        data-items='@json($order->orderItems)'>
                                                        <i class="bi bi-eye me-1 text-primary"></i>View
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox display-6 d-block mb-3 opacity-50"></i>
                                                    No orders found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <!-- Pagination sits INSIDE .table-responsive, so it
                                     rides the table's single horizontal scrollbar. -->
                                <div
                                    class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                        <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                            onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                            @foreach([10, 25, 50, 100] as $size)
                                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                                                    {{ $size }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-muted small text-nowrap">
                                            Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of
                                            {{ $orders->total() }} entries
                                        </span>
                                    </div>
                                    <nav class="flex-shrink-0">
                                        {{ $orders->links() }}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('staff.order.components.order-detail-modal')
    @include('staff.order.components.update-status-modal')

    <style>
        /* ============================================
           Order Status Stat Cards
           ============================================ */
        /* Eight statuses across one row. Bootstrap's 12-column grid can't
           divide by 8, so these lay out on their own grid. */
        .order-stat-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 576px) {
            .order-stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (min-width: 1200px) {
            .order-stat-grid { grid-template-columns: repeat(8, minmax(0, 1fr)); }
        }

        /* Read-only now — filtering moved to the dropdown, so there's no hover
           lift and no pointer. The ring stays: it shows which status the
           dropdown is currently filtering by. */
        .order-stat-card {
            border: 1px solid transparent;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .order-stat-card-active {
            border-color: var(--stat-color) !important;
            box-shadow: 0 0 0 2px var(--stat-color) inset, 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06) !important;
        }
        .order-stat-card-active .order-stat-count { color: var(--stat-color) !important; }
        /* Laravel's paginator spreads its summary and page links apart with
           justify-content-between, stranding a gap inside our own bar. Sit
           them side by side instead. */
        .pagination-bar nav > div[class*="d-sm-flex"] {
            flex: 0 0 auto !important;
            justify-content: flex-end !important;
            gap: 0.75rem;
        }
        .pagination-bar nav p { margin-bottom: 0; }
        .pagination-bar .pagination { margin-bottom: 0; }

        .order-stat-label {
            font-size: 0.62rem;
            letter-spacing: 0.03em;
            line-height: 1.15;
        }

        /* ============================================
           Order Modal Theme
           ============================================ */
        .order-modal .modal-content { border-radius: 18px; }

        .order-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .order-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .order-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .order-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .order-close-btn {
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
        }
        .order-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .order-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .order-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .order-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }

        .order-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .order-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .order-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .order-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .order-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md
           ============================================ */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu,
            .filter-dd .dropdown-menu.filter-menu {
                position: static !important;
                display: block !important;
                float: none;
                width: 100%;
                margin: 0;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                background: transparent;
            }
            .filter-dd .filter-menu .form-select { width: auto !important; }
        }
        @media (max-width: 991.98px) {
            .search-dd .dropdown-menu.search-menu {
                width: min(82vw, 360px);
            }
            .filter-dd .dropdown-menu.filter-menu {
                width: min(82vw, 320px);
            }

            /* Edge-to-edge scrollable table; pagination shares its scroll. */
            .data-table-card {
                margin-left: -0.75rem;
                margin-right: -0.75rem;
                border-radius: 0 !important;
            }
            .data-table-card .table {
                min-width: 920px;
            }
            .data-table-card .table th,
            .data-table-card .table td {
                white-space: nowrap;
            }
            .data-table-card .table th:nth-child(3),
            .data-table-card .table td:nth-child(3) {
                min-width: 200px;
            }
            .pagination-bar {
                flex-wrap: nowrap;
                min-width: 920px;
            }
            .pagination-bar .pagination {
                --bs-pagination-padding-x: 0.5rem;
                --bs-pagination-padding-y: 0.25rem;
                --bs-pagination-font-size: 0.8rem;
                margin-bottom: 0;
            }
        }
    </style>

    @push('scripts')
        <!-- Three.js Libraries -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

        <!-- Customizer Core Logic (Excluding UI Handlers) -->
        <script src="{{ asset('js/customizer/state.js') }}"></script>
        <script src="{{ asset('js/customizer/core.js') }}"></script>
        <script src="{{ asset('js/customizer/models/mug.js') }}"></script>
        <script src="{{ asset('js/customizer/models/t-shirt.js') }}"></script>
        <script src="{{ asset('js/customizer/models/polo.js') }}"></script>
        <script src="{{ asset('js/customizer/models/shorts.js') }}"></script>
        <script src="{{ asset('js/customizer/models/umbrella.js') }}"></script>
        <script src="{{ asset('js/customizer/models/bag.js') }}"></script>
        <script src="{{ asset('js/customizer/rendering.js') }}"></script>
        <script src="{{ asset('js/customizer/persistence.js') }}"></script>

        @include('partials.customizer-textures')

        <script>
            $(document).ready(function () {

                // View Order Details
                $('.btn-view-order').on('click', function () {
                    const order = $(this).data('order');
                    const items = $(this).data('items');

                    $('#viewOrderNumber').text(order.order_number);
                    $('#viewCustomerName').text(order.user ? order.user.fullname : 'Guest');
                    $('#viewCustomerEmail').text(order.user ? order.user.email : 'No email available');
                    $('#viewCustomerAddress').text(order.user && order.user.address ? order.user.address : 'No address provided');
                    $('#viewCustomerPhone').text(order.user && order.user.contact_number ? order.user.contact_number : 'No contact provided');

                    // Avatar Initial
                    const name = order.user ? order.user.fullname : 'G';
                    $('#customerAvatar').text(name.substring(0, 2).toUpperCase());

                    $('#viewOrderDate').text(new Date(order.created_at).toLocaleString());
                    $('#viewPaymentMethod').text('Cash on Pickup');

                    $('#viewPaymentRef').text(order.payment_reference || '—');

                    const tbody = $('#viewOrderItems');
                    tbody.empty();

                    items.forEach(item => {
                        const product = item.product || {};
                        const productName = product.name || 'Unknown Product';
                        const subtotal = (Number(item.price) || 0) * (Number(item.quantity) || 0);

                        const design = item.custom_design || item.customDesign;
                        const isCustom = !!(item.custom_design_id && design);
                        if (isCustom) design.product_name = productName;

                        const snapshot = isCustom ? design.snapshot : null;
                        const imgSrc = snapshot || product.image_url || '/img/FABLAB-LOGO.png';

                        const thumbHtml = isCustom && snapshot
                            ? `<img src="${imgSrc}" class="w-100 h-100 object-fit-cover rounded btn-popout-design" style="cursor: zoom-in;" data-design='${JSON.stringify(design).replace(/'/g, "&apos;")}' alt="">`
                            : `<img src="${imgSrc}" class="w-100 h-100 object-fit-cover rounded" alt="">`;

                        const customBadge = isCustom
                            ? '<span class="badge ms-1" style="background-color: rgba(255, 197, 8, 0.18); color: #997404; font-size: 0.55rem;">TAILORED</span>'
                            : '';

                        tbody.append(`
                            <tr>
                                <td class="ps-3 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded border bg-white p-1 me-2" style="width: 40px; height: 40px;">
                                            ${thumbHtml}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">${productName}${customBadge}</div>
                                            <div class="text-muted font-monospace" style="font-size: 0.7rem;">SKU: ${product.sku ?? '-'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark">${item.quantity}</td>
                                <td class="text-end text-muted small">₱${parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                                <td class="text-end pe-3 fw-bold text-dark">₱${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                            </tr>
                        `);
                    });

                    $('#viewOrderTotal').text('₱' + parseFloat(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }));

                    // The slip only exists once the order is approved; before that
                    // there is nothing for the cashier to be handed.
                    const slipStatuses = @json(\App\Support\TransactionSlip::PRINTABLE_STATUSES);
                    const $printSlip = $('#viewPrintSlip');

                    if (slipStatuses.includes(order.status)) {
                        $printSlip
                            .attr('href', '{{ route('staff.orders.receipt', ['id' => '__ORDER_ID__']) }}'.replace('__ORDER_ID__', order.order_id))
                            .removeClass('d-none');
                    } else {
                        $printSlip.attr('href', '#').addClass('d-none');
                    }

                    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
                    modal.show();
                });

                // Design Detail Popout (Zoom/Recipe + 3D View)
                $(document).on('click', '.btn-popout-design', function() {
                    const design = $(this).data('design');
                    if (!design) return;

                    $('#detailPopupImage').attr('src', design.snapshot || '').removeClass('d-none');
                    $('#preview-loader').removeClass('d-none');

                    let recipe = design.recipe;
                    if (typeof recipe === 'string') {
                        try { recipe = JSON.parse(recipe); } catch(e) {}
                    }
                    $('#detailPopupRecipe').text(JSON.stringify(recipe || {}, null, 4));

                    const modal = new bootstrap.Modal(document.getElementById('designDetailPopup'));
                    modal.show();

                    setTimeout(() => {
                        try {
                            const container = document.getElementById('staff-three-container');
                            if (container.querySelector('canvas')) {
                                container.querySelector('canvas').remove();
                                if (typeof renderer !== 'undefined' && renderer) {
                                    renderer.dispose();
                                    renderer = null;
                                }
                            }

                            const itemName = (design.product_name || '').toLowerCase();
                            let baseShape = 't-shirt';

                            // Polo before any shirt match: a polo is its own
                            // model, and falling through would preview it as a
                            // t-shirt.
                            if (itemName.includes('polo')) baseShape = 'polo';
                            else if (itemName.includes('mug')) baseShape = 'mug';
                            else if (itemName.includes('umbrella')) baseShape = 'umbrella';
                            else if (itemName.includes('bag')) baseShape = 'bag';
                            else if (itemName.includes('shorts')) baseShape = 'shorts';
                            else if (recipe.base_style) baseShape = recipe.base_style;

                            // Merge, don't replace — a wholesale assignment here
                            // dropped the texture catalogue and every previewed
                            // design rendered blank white.
                            window.CustomizerConfig = Object.assign(window.CustomizerConfig || {}, {
                                initialShape: baseShape
                            });

                            init('staff-three-container');

                            setTimeout(() => {
                                loadDesignRecipePreview(recipe);
                                $('#preview-loader').addClass('d-none');
                                $('#detailPopupImage').addClass('d-none');
                            }, 800);

                        } catch (err) {
                            console.error('3D Preview failed to initialize:', err);
                            $('#preview-loader').html('<div class="text-danger small">3D Engine failed to start. Reviewing snapshot instead.</div>');
                        }
                    }, 500);
                });

                $('#designDetailPopup').on('hidden.bs.modal', function () {
                    const container = document.getElementById('staff-three-container');
                    if (container.querySelector('canvas')) {
                        container.querySelector('canvas').remove();
                    }
                    if (typeof renderer !== 'undefined' && renderer) {
                        renderer.dispose();
                        renderer = null;
                    }
                });

                // Update Status Modal
                $('.btn-update-status').on('click', function () {
                    const id = $(this).data('id');
                    const nextStatus = $(this).data('next-status');

                    $('#updateOrderId').val(id);
                    $('#updateStatusInput').val(nextStatus);

                    const actionUrl = "{{ route('staff.orders.updateStatus', ':id') }}".replace(':id', id);
                    $('#updateStatusForm').attr('action', actionUrl);

                    const paymentContainer = $('#paymentRefContainer');
                    const paymentInput = $('#paymentReference');
                    const confirmText = $('#modalConfirmationText');

                    // Starting production is the step that actually cuts into
                    // the shelf — approval only set the materials aside. So it
                    // is the one step worth showing them for; the later ones
                    // move no stock at all.
                    const materialsPanel = document.getElementById('updateMaterialsPanel');
                    if (nextStatus === 'processing') {
                        paymentContainer.removeClass('d-none');
                        paymentInput.prop('required', true);
                        confirmText.text("Please enter the Payment Reference Number from the Cashier to start processing this order.");
                        fetchOrderMaterials(
                            'updateMaterialsPanel',
                            @json(route('staff.orders.materials', ':id')).replace(':id', id)
                        );
                    } else {
                        materialsPanel.classList.add('d-none');
                        paymentContainer.addClass('d-none');
                        paymentInput.prop('required', false);

                        let statusText = nextStatus.replace(/_/g, ' ');
                        statusText = statusText.charAt(0).toUpperCase() + statusText.slice(1);
                        confirmText.text(`Are you sure you want to mark this order as ${statusText}?`);
                    }

                    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
                    modal.show();
                });
            });
        </script>
    @endpush
@endsection

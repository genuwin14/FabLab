@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
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
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark">Order Management</h2>
                            <p class="text-muted small mb-0">View and manage customer orders.</p>
                        </div>
                        <div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card border-0 p-2 shadow-sm rounded-2 mb-2  ">
                        <div class="card-body p-2">
                            <form action="{{ route('admin.orders.index') }}" method="GET" class="w-100">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <!-- Search -->
                                    <div class="flex-grow-1">
                                        <div class="input-group bg-light rounded-2 shadow-sm">
                                            <span class="input-group-text bg-transparent border-0 ps-3"><i
                                                    class="bi bi-search text-muted"></i></span>
                                            <input type="text" name="search" value="{{ request('search') }}"
                                                class="form-control bg-transparent border-0 shadow-none"
                                                placeholder="Search orders by ID, Customer..." autocomplete="off">
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex gap-2">
                                        <button type="button"
                                            class="btn btn-light rounded-2 border-1 shadow-sm px-3 fw-bold small d-flex align-items-center gap-2">
                                            <i class="bi bi-download"></i> <span class="d-none d-md-inline">Export</span>
                                        </button>

                                        <!-- Filter Dropdown -->
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-light rounded-2 border-0 shadow-sm px-3 fw-bold small d-flex align-items-center gap-2"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-sliders2"></i> Filters
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end p-3 border-0 shadow-lg rounded-4"
                                                style="width: 300px;">
                                                <h6 class="dropdown-header text-uppercase small fw-bold text-muted ps-0 mb-2">
                                                    Order Status
                                                </h6>
                                                <div class="d-flex flex-column gap-2 mb-3">
                                                    @foreach(['pending', 'processing', 'completed', 'cancelled'] as $status)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="status[]"
                                                                value="{{ $status }}" id="status{{ ucfirst($status) }}"
                                                                {{ in_array($status, request('status', [])) ? 'checked' : '' }}
                                                                onchange="this.form.submit()">
                                                            <label class="form-check-label small fw-bold text-capitalize"
                                                                for="status{{ ucfirst($status) }}">
                                                                {{ $status }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <h6 class="dropdown-header text-uppercase small fw-bold text-muted ps-0 mb-2">
                                                    Time Period
                                                </h6>
                                                <div class="d-flex flex-column gap-2 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="date" value="today" id="dateToday" {{ request('date') == 'today' ? 'checked' : '' }} onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-bold" for="dateToday">
                                                            Today
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="date" value="week" id="dateThisWeek" {{ request('date') == 'week' ? 'checked' : '' }} onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-bold" for="dateThisWeek">
                                                            This Week
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="date" value="month" id="dateThisMonth" {{ request('date') == 'month' ? 'checked' : '' }} onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-bold" for="dateThisMonth">
                                                            This Month
                                                        </label>
                                                    </div>
                                                    <!-- Clear Date Filter Option if date is selected -->
                                                    @if(request('date'))
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="date" value="" id="dateAll" onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-bold text-muted" for="dateAll">
                                                            All Time
                                                        </label>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="card border-0 shadow-sm rounded-2 overflow-hidden">
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col"
                                                class="py-3 ps-4 border-0 rounded-start text-uppercase small text-muted fw-bold">
                                                Order ID</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">
                                                Ref No</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">
                                                Customer</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">
                                                Date</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">
                                                Items</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">
                                                Total</th>
                                            <th scope="col"
                                                class="py-3 pe-4 border-0 text-uppercase small text-muted fw-bold">
                                                Status</th>
                                            <th scope="col"
                                                class="py-3 pe-4 border-0 rounded-end text-uppercase small text-muted fw-bold text-end">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $order)
                                            <tr>
                                                <td class="ps-4 fw-bold">#{{ $order->order_number }}</td>
                                                <td class="small fw-bold text-muted">REF:
                                                    {{ $order->payment_reference ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle fw-bold text-white d-flex align-items-center justify-content-center me-2"
                                                            style="width: 32px; height: 32px;">
                                                            {{ substr($order->user->fullname ?? 'G', 0, 2) }}
                                                        </div>
                                                        <span>{{ $order->user->fullname ?? 'Guest' }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                <td>{{ $order->orderItems->count() }}</td>
                                                <td class="fw-bold">₱{{ number_format($order->total_amount, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($order->status) {
                                                            'completed' => 'bg-success text-success',
                                                            'processing' => 'bg-info text-info',
                                                            'cancelled' => 'bg-danger text-danger',
                                                            default => 'bg-warning text-dark bg-opacity-25 text-opacity-75',
                                                        };

                                                        // Adjust opacity for success/info/danger if needed to match the design
                                                        if ($order->status == 'completed' || $order->status == 'processing' || $order->status == 'cancelled') {
                                                            $statusClass .= ' bg-opacity-10';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass }} px-3 py-2 rounded-pill text-capitalize">{{ $order->status }}</span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    @if($order->status == 'pending')
                                                        <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm btn-review-order" 
                                                            data-bs-toggle="modal" data-bs-target="#reviewOrderModal"
                                                            data-order="{{ json_encode($order) }}"
                                                            data-items="{{ json_encode($order->orderItems()->with('product')->get()) }}">
                                                            Review
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
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
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-4 px-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing <span class="fw-bold text-dark">{{ $orders->firstItem() ?? 0 }}</span> to <span
                                        class="fw-bold text-dark">{{ $orders->lastItem() ?? 0 }}</span> of <span
                                        class="fw-bold text-dark">{{ $orders->total() }}</span>
                                    results
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0 gap-1">
                                        {{-- Previous Page Link --}}
                                        <li class="page-item {{ $orders->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link border-0 bg-transparent text-muted"
                                                href="{{ $orders->previousPageUrl() }}" tabindex="-1"
                                                aria-disabled="{{ $orders->onFirstPage() ? 'true' : 'false' }}">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>

                                        {{-- Pagination Elements --}}
                                        @foreach (range(1, $orders->lastPage()) as $i)
                                            @if($i >= $orders->currentPage() - 2 && $i <= $orders->currentPage() + 2)
                                                <li class="page-item {{ $i == $orders->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link border-0 rounded-pill fw-bold shadow-sm {{ $i == $orders->currentPage() ? 'bg-primary text-white' : 'bg-transparent text-muted' }}"
                                                        href="{{ $orders->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        <li class="page-item {{ $orders->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link border-0 bg-transparent text-muted"
                                                href="{{ $orders->nextPageUrl() }}">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
    @include('auth.modal-logout')
    @include('admin.order.components.review-modal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reviewModal = document.getElementById('reviewOrderModal');
            if (reviewModal) {
                reviewModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const order = JSON.parse(button.getAttribute('data-order'));
                    const items = JSON.parse(button.getAttribute('data-items'));
                    
                    // Set Form Action
                    const form = document.getElementById('reviewOrderForm');
                    form.action = `/admin/orders/${order.order_id}/review`;

                    // Populate Info
                    document.getElementById('reviewOrderNumber').textContent = order.order_number;
                    document.getElementById('reviewCustomerName').textContent = order.user ? order.user.name : 'Guest';

                    // Populate Items
                    const tbody = document.getElementById('reviewItemsBody');
                    tbody.innerHTML = '';
                    
                    let allAvailable = true;

                    items.forEach(item => {
                        const product = item.product;
                        const stock = product ? product.stock : 0;
                        const isAvailable = stock >= item.quantity;
                        
                        if (!isAvailable) allAvailable = false;

                        const row = `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded border p-1 me-2" style="width: 40px; height: 40px;">
                                            <img src="${product.image ? product.image : '/img/FABLAB-LOGO.png'}" class="w-100 h-100 object-fit-cover" alt="">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">${product.name}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">SKU: ${product.sku}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark">${item.quantity}</td>
                                <td class="text-center fw-bold ${stock < item.quantity ? 'text-danger' : 'text-success'}">${stock}</td>
                                <td class="text-end">
                                    ${isAvailable 
                                        ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill">Available</span>' 
                                        : '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Insufficient</span>'}
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });

                    // Toggle Buttons based on availability (Optional: Admin can decided regardless, but visually we can warn)
                    // Currently we allow admin to decide.
                });
            }
        });
    </script>
@endsection
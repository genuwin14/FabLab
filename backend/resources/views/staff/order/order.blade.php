@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark">Order Management</h2>
                            <p class="text-muted small mb-0">Manage incoming orders and update their status.</p>
                        </div>
                    </div>

                    <!-- Filters Card -->
                    <div class="card border-0 p-2 shadow-sm rounded-2 mb-2">
                        <div class="card-body p-2">
                            <form action="{{ route('staff.orders.index') }}" method="GET" class="w-100">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <!-- Search -->
                                    <div class="flex-grow-1">
                                        <div class="input-group bg-light rounded-2 shadow-sm">
                                            <span class="input-group-text bg-transparent border-0 ps-3"><i
                                                    class="bi bi-search text-muted"></i></span>
                                            <input type="text" name="search" value="{{ request('search') }}"
                                                class="form-control bg-transparent border-0 shadow-none"
                                                placeholder="Search orders by ID, Customer..." autocomplete="off"
                                                onchange="this.form.submit()"
                                                onkeydown="if(event.key === 'Enter'){this.form.submit();}">
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
                                                    @foreach(['pending', 'approved', 'processing', 'ready_for_pickup', 'completed', 'cancelled'] as $status)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="status[]"
                                                                value="{{ $status }}" id="status{{ ucfirst($status) }}"
                                                                {{ in_array($status, request('status', [])) ? 'checked' : '' }}
                                                                onchange="this.form.submit()">
                                                            <label class="form-check-label small fw-bold text-capitalize"
                                                                for="status{{ ucfirst($status) }}">
                                                                {{ str_replace('_', ' ', $status) }}
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

                    <!-- Orders Table Card -->
                    <div class="card border-0 shadow-sm rounded-2 overflow-hidden">
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="ordersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col" class="py-3 ps-4 border-0 rounded-start text-uppercase small text-muted fw-bold">Order ID</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">Ref No</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">Customer</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">Date</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">Status</th>
                                            <th scope="col" class="py-3 border-0 text-uppercase small text-muted fw-bold">Total</th>
                                            <th scope="col" class="py-3 pe-4 border-0 rounded-end text-uppercase small text-muted fw-bold text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $order)
                                            <tr class="order-row filter-item" data-status="{{ $order->status }}">
                                                <td class="ps-4 fw-bold text-dark">#{{ $order->order_number }}</td>
                                                <td class="small fw-bold text-muted">
                                                    @if($order->payment_reference)
                                                        REF: {{ $order->payment_reference }}
                                                    @else
                                                        <span class="text-muted fst-italic fw-normal">No reference</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle fw-bold text-white d-flex align-items-center justify-content-center me-2"
                                                            style="width: 32px; height: 32px;">
                                                            {{ substr($order->user->fullname ?? 'G', 0, 2) }}
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-dark fw-bold small">{{ $order->user->fullname ?? 'Guest' }}</span>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">{{ $order->user->email ?? '' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted small fw-bold">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($order->status) {
                                                            'completed' => 'bg-success text-success bg-opacity-10',
                                                            'processing' => 'bg-info text-info bg-opacity-10',
                                                            'cancelled' => 'bg-danger text-danger bg-opacity-10',
                                                            default => 'bg-warning text-dark bg-opacity-25 text-opacity-75',
                                                        };
                                                        // Ensure pending keys match admin style
                                                        if($order->status == 'pending') {
                                                            $statusClass = 'bg-warning text-dark bg-opacity-25 text-opacity-75';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill text-capitalize">{{ $order->status }}</span>
                                                </td>
                                                <td class="fw-bold text-dark">₱{{ number_format($order->total_amount, 2) }}</td>
                                                <td class="pe-4 text-end">
                                                    @if($order->status === 'cancelled')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 me-1">Cancelled</span>
                                                    @elseif($order->status === 'completed')
                                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 me-1">Completed</span>
                                                    @else
                                                        @php
                                                            $nextStatus = match ($order->status) {
                                                                'pending' => 'processing',
                                                                'approved' => 'processing',
                                                                'processing' => 'ready_for_pickup',
                                                                'ready_for_pickup' => 'completed',
                                                                default => 'pending'
                                                            };

                                                            $btnLabel = match ($order->status) {
                                                                'pending' => 'Waiting for admin approval',
                                                                'approved' => 'Process',
                                                                'processing' => 'Ready',
                                                                'ready_for_pickup' => 'Complete',
                                                                default => 'Update'
                                                            };

                                                            $btnClass = match ($order->status) {
                                                                'pending' => 'btn-light text-muted border',
                                                                'approved' => 'btn-primary',
                                                                'processing' => 'btn-info text-white',
                                                                'ready_for_pickup' => 'btn-success text-white',
                                                                default => 'btn-secondary'
                                                            };
                                                        @endphp

                                                        @if($order->status == 'pending')
                                                            <span class="badge bg-light text-muted border rounded-pill px-3 py-2 me-1">
                                                                <i class="bi bi-hourglass-split me-1"></i> Waiting for admin approval
                                                            </span>
                                                        @else
                                                            <button class="btn btn-sm {{ $btnClass }} rounded-2 border-0 shadow-sm btn-update-status fw-bold px-3 me-1"
                                                                data-id="{{ $order->order_id }}" data-status="{{ $order->status }}"
                                                                data-next-status="{{ $nextStatus }}">
                                                                {{ $btnLabel }}
                                                            </button>
                                                        @endif
                                                    @endif

                                                    <button class="btn btn-sm btn-light rounded-2 border shadow-sm btn-view-order fw-bold"
                                                        data-order="{{ json_encode($order) }}"
                                                        data-items="{{ json_encode($order->orderItems) }}">
                                                        <i class="bi bi-eye"></i>
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
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer bg-transparent border-0 py-4 px-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing <span class="fw-bold text-dark">{{ $orders->firstItem() ?? 0 }}</span> to <span
                                        class="fw-bold text-dark">{{ $orders->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $orders->total() }}</span>
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

    @include('staff.order.components.order-detail-modal')
    @include('staff.order.components.update-status-modal')

    @push('scripts')
        <script>
            $(document).ready(function () {


                // View Order Details
                $('.btn-view-order').on('click', function () {
                    const order = $(this).data('order');
                    const items = $(this).data('items');

                    $('#viewOrderNumber').text(order.order_number);
                    $('#viewCustomerName').text(order.user ? order.user.name : 'Guest');
                    $('#viewOrderDate').text(new Date(order.created_at).toLocaleString());
                    $('#viewPaymentMethod').text('Cash on Pickup');

                    if (order.payment_reference) {
                        $('#viewPaymentRef').text(order.payment_reference);
                        $('#viewPaymentRefContainer').removeClass('d-none');
                    } else {
                        $('#viewPaymentRefContainer').addClass('d-none');
                    }

                    const tbody = $('#viewOrderItems');
                    tbody.empty();

                    let total = 0;
                    items.forEach(item => {
                        const productName = item.product ? item.product.name : 'Unknown Product';
                        const subtotal = item.quantity * item.price;
                        total += subtotal; 

                        tbody.append(`
                            <tr>
                                <td>
                                     <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam h-100 w-100 d-flex align-items-center justify-content-center text-muted"></i>
                                        </div>
                                        <span class="fw-medium text-dark small">${productName}</span>
                                    </div>
                                </td>
                                <td class="text-center text-muted small">x${item.quantity}</td>
                                <td class="text-end fw-bold text-dark small">₱${parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                                <td class="text-end fw-bold text-dark small">₱${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                            </tr>
                        `);
                    });

                    $('#viewOrderTotal').text('₱' + parseFloat(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }));

                    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
                    modal.show();
                });

                // Update Status Modal
                $('.btn-update-status').on('click', function () {
                    const id = $(this).data('id');
                    const nextStatus = $(this).data('next-status');

                    $('#updateOrderId').val(id);
                    $('#updateStatusInput').val(nextStatus);

                    // Set form action dynamically
                    const actionUrl = "{{ route('staff.orders.updateStatus', ':id') }}".replace(':id', id);
                    $('#updateStatusForm').attr('action', actionUrl);

                    // Logic for Payment Reference Field
                    const paymentContainer = $('#paymentRefContainer');
                    const paymentInput = $('#paymentReference');
                    const confirmText = $('#modalConfirmationText');

                    if (nextStatus === 'processing') {
                        paymentContainer.removeClass('d-none');
                        paymentInput.prop('required', true);
                        confirmText.text("Please enter the Payment Reference Number from the Cashier to start processing this order.");
                    } else {
                        paymentContainer.addClass('d-none');
                        paymentInput.prop('required', false);

                        // Human readable status
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
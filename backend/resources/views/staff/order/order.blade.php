@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm" style="width: 280px; z-index: 1040;">
            @include('staff.partials.sidebar')
        </aside>

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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Order Management</h4>
                            <p class="text-muted small mb-0">Manage incoming orders and update their status.</p>
                        </div>
                    </div>

                    <!-- Orders Table Card -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <ul class="nav nav-pills card-header-pills bg-light rounded-pill p-1 d-inline-flex"
                                id="orderFilters" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active rounded-pill px-3 small fw-bold" href="#all"
                                        data-bs-toggle="tab">All Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill px-3 small fw-bold" href="#pending"
                                        data-bs-toggle="tab">Pending</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill px-3 small fw-bold" href="#processing"
                                        data-bs-toggle="tab">Processing</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill px-3 small fw-bold" href="#completed"
                                        data-bs-toggle="tab">Completed</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="ordersTable">
                                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                                        <tr>
                                            <th class="ps-4 py-3 border-0">Order ID</th>
                                            <th class="py-3 border-0">Payment Ref</th>
                                            <th class="py-3 border-0">Customer</th>
                                            <th class="py-3 border-0">Date</th>
                                            <th class="py-3 border-0">Status</th>
                                            <th class="py-3 border-0">Total</th>
                                            <th class="pe-4 py-3 border-0 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($orders as $order)
                                            <tr class="order-row filter-item" data-status="{{ $order->status }}">
                                                <td class="ps-4 py-4 fw-bold text-dark">{{ $order->order_number }}</td>
                                                <td class="py-4">
                                                    @if($order->payment_reference)
                                                        <span class="fw-bold text-dark small text-monospace">Ref:
                                                            {{ $order->payment_reference }}</span>
                                                    @else
                                                        <span class="text-muted small fst-italic">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                            style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                            {{ substr($order->user->name ?? 'G', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark small">
                                                                {{ $order->user->name ?? 'Guest' }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                                {{ $order->user->email ?? '' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4 text-muted small">
                                                    {{ $order->created_at->format('M d, Y h:i A') }}
                                                </td>
                                                <td class="py-4">
                                                    @php
                                                        $badgeClass = match ($order->status) {
                                                            'pending' => 'bg-warning text-dark',
                                                            'processing' => 'bg-info text-white',
                                                            'completed' => 'bg-success text-white',
                                                            'cancelled' => 'bg-danger text-white',
                                                            default => 'bg-secondary text-white'
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge {{ $badgeClass }} rounded-pill px-3 py-1 fw-bold">{{ ucfirst($order->status) }}</span>
                                                </td>
                                                <td class="py-4 fw-bold text-dark">₱{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td class="pe-4 py-4 text-end">
                                                    <button
                                                        class="btn btn-sm btn-light rounded-pill fw-bold border shadow-sm me-1 btn-view-order"
                                                        data-order="{{ json_encode($order) }}"
                                                        data-items="{{ json_encode($order->orderItems) }}">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </button>
                                                    @if($order->status === 'cancelled')
                                                        <span class="text-danger small fw-bold fst-italic ms-2">
                                                            <i class="bi bi-x-circle me-1"></i>Cancelled
                                                        </span>
                                                    @elseif($order->status === 'completed')
                                                        <span class="text-success small fw-bold fst-italic ms-2">
                                                            <i class="bi bi-check-circle me-1"></i>Completed
                                                        </span>
                                                    @else
                                                        @php
                                                            $nextStatus = match ($order->status) {
                                                                'pending' => 'processing',
                                                                'processing' => 'ready_for_pickup',
                                                                'ready_for_pickup' => 'completed',
                                                                default => 'pending'
                                                            };

                                                            $btnLabel = match ($order->status) {
                                                                'pending' => 'Process Order',
                                                                'processing' => 'Ready for Pickup',
                                                                'ready_for_pickup' => 'Complete Order',
                                                                default => 'Update'
                                                            };

                                                            $btnClass = match ($order->status) {
                                                                'pending' => 'btn-primary',
                                                                'processing' => 'btn-info text-white',
                                                                'ready_for_pickup' => 'btn-success',
                                                                default => 'btn-secondary'
                                                            };
                                                        @endphp
                                                        <button
                                                            class="btn btn-sm {{ $btnClass }} rounded-pill fw-bold shadow-sm btn-update-status"
                                                            data-id="{{ $order->order_id }}" data-status="{{ $order->status }}"
                                                            data-next-status="{{ $nextStatus }}">
                                                            {{ $btnLabel }}
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">No orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing <span class="fw-bold text-dark">{{ $orders->firstItem() ?? 0 }}</span> to <span
                                    class="fw-bold text-dark">{{ $orders->lastItem() ?? 0 }}</span> of <span
                                    class="fw-bold text-dark">{{ $orders->total() }}</span> results
                            </div>
                            <div>
                                {{ $orders->links() }}
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
                // Filter Logic
                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("href").replace('#', '');
                    if (target === 'all') {
                        $('.filter-item').show();
                    } else {
                        $('.filter-item').hide();
                        $('.filter-item[data-status="' + target + '"]').show();
                    }
                });

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
                        total += subtotal; // Assuming total_amount in order is correct, but let's calc for display if needed

                        tbody.append(`
                                                                            <tr>
                                                                                <td>
                                                                                     <div class="d-flex align-items-center">
                                                                                        <div class="bg-light rounded p-1 me-2" style="width: 40px; height: 40px;">
                                                                                            <!-- Placeholder image or item.product.image -->
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
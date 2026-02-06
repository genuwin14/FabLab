@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm flex-shrink-0" style="width: 280px; z-index: 1040;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background-color: #f1f4f8;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 overflow-y-auto custom-scrollbar">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark">Order History</h3>
                    </div>

                    @if($orders->count() > 0)
                        <div class="row g-4">
                            @foreach($orders as $order)
                                <div class="col-12 col-md-6 col-lg-4">
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
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#orderItems-{{ $order->order_id }}" aria-expanded="false">
                                                        Details <i class="bi bi-chevron-down ms-1"></i>
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

                                                <!-- Collapsible Items Section -->
                                                <div class="collapse mt-3" id="orderItems-{{ $order->order_id }}">
                                                    <div class="bg-light rounded-3 p-3">
                                                        <h6 class="fw-bold mb-3 text-muted small text-uppercase">Items</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-borderless mb-0 small">
                                                                <tbody>
                                                                    @foreach($order->orderItems as $item)
                                                                        <tr class="align-middle">
                                                                            <td style="width: 40px;">
                                                                                <div class="rounded-2 overflow-hidden border bg-white"
                                                                                    style="width: 30px; height: 30px;">
                                                                                    <img src="{{ $item->product->image ?: asset('img/FABLAB-LOGO.png') }}"
                                                                                        class="w-100 h-100 object-fit-cover" alt="">
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="fw-bold text-dark">
                                                                                    {{ $item->product->name }}
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-end fw-medium">
                                                                                x{{ $item->quantity }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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

    @include('customer.order.components.cancel-modal')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
<!-- Order Details Right Drawer -->
@php
    $statusColors = [
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-info text-white',
        'processing' => 'bg-info text-white',
        'ready_for_pickup' => 'bg-primary text-white',
        'completed' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
    ];
    $statusLabel = ucwords(str_replace('_', ' ', $order->status));
    $badgeClass = $statusColors[$order->status] ?? 'bg-secondary text-white';
@endphp
<div class="offcanvas offcanvas-end customer-order-details-drawer" tabindex="-1"
    id="orderDetails-{{ $order->order_id }}" aria-labelledby="orderDetailsLabel-{{ $order->order_id }}"
    style="width: 440px; max-width: 100%;">

    <!-- Themed Header -->
    <div class="customer-order-details-header">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <div class="customer-order-details-eyebrow">Order #{{ $order->order_number }}</div>
                <h5 class="fw-bold text-white mb-0 mt-1" id="orderDetailsLabel-{{ $order->order_id }}">
                    Order Details
                </h5>
                <p class="text-white-50 small mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->format('M d, Y · h:i A') }}
                </p>
            </div>
            <button type="button" class="customer-order-details-close" data-bs-dismiss="offcanvas"
                aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Body -->
    <div class="offcanvas-body customer-order-details-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">{{ $statusLabel }}</span>
            @if($order->payment_reference ?? false)
                <span class="text-muted small font-monospace">{{ $order->payment_reference }}</span>
            @endif
        </div>

        <div class="customer-order-details-summary mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Total Amount</span>
                <span class="fw-bold text-primary fs-5">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Items</span>
                <span class="fw-medium">{{ $order->orderItems->sum('quantity') }} items</span>
            </div>
        </div>

        <h6 class="customer-order-details-section-title">Items</h6>
        <div class="d-flex flex-column gap-2">
            @foreach($order->orderItems as $item)
                <div class="customer-order-details-item">
                    <div class="rounded-2 overflow-hidden border bg-white flex-shrink-0"
                        style="width: 48px; height: 48px;">
                        <img src="{{ ($item->customDesign && $item->customDesign->snapshot) ? $item->customDesign->snapshot : ($item->product->image_url ?: asset('img/FABLAB-LOGO.png')) }}"
                            class="w-100 h-100 object-fit-cover" alt="">
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-dark text-truncate">
                            {{ $item->product->name }}
                            @if($item->custom_design_id)
                                <span
                                    class="badge bg-soft-primary text-primary tiny rounded-pill border border-primary-subtle ms-1">Custom</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            ₱{{ number_format($item->price ?? 0, 2) }}
                        </div>
                    </div>
                    <span class="fw-bold text-dark flex-shrink-0">x{{ $item->quantity }}</span>
                </div>
            @endforeach
        </div>

        @if($order->status == 'cancelled' && $order->reason)
            <div class="alert alert-danger d-flex align-items-start mt-3 p-2 small border-0 bg-danger bg-opacity-10 text-danger rounded-3"
                role="alert">
                <i class="bi bi-x-circle-fill me-2 mt-1"></i>
                <div class="lh-sm">
                    <span class="fw-bold">Order Cancelled</span><br>
                    Reason: {{ $order->reason }}
                </div>
            </div>
        @endif

        {{-- The transaction slip exists from approval onwards; this is where a
             customer looking at the order expects to find it. --}}
        @if(in_array($order->status, ['approved', 'processing', 'ready_for_pickup', 'completed']))
            <a href="{{ route('customer.orders.receipt', $order->order_id) }}" target="_blank"
                class="btn w-100 rounded-pill fw-bold mt-3 d-flex align-items-center justify-content-center"
                style="background-color: #0e2e45; color: #ffffff;">
                <i class="bi bi-receipt me-2"></i> View Slip
            </a>
        @endif
    </div>
</div>

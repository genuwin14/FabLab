{{-- How an order is paid — at PAXS, or through CSPC procurement — and who it
     is for: an office, or the customer themself. The customer's order list
     gives it a column of its own; the admin and staff lists already fill a
     laptop screen, so there it rides under the customer's email. It wraps
     rather than truncating, so it never makes a column wider than its
     neighbours already need. --}}
@php
    $isOfficeOrder = filled($order->office) || $order->isPurchaseRequest();
    $channelStyle = $order->isPurchaseRequest()
        ? 'background-color: rgba(111, 66, 193, 0.12); color: #6f42c1;'
        : 'background-color: rgba(32, 201, 151, 0.15); color: #0f8a6a;';
@endphp
<div class="d-flex flex-wrap align-items-center column-gap-2 row-gap-1 lh-sm">
    <span class="d-inline-flex align-items-center rounded-pill fw-semibold flex-shrink-0"
        style="{{ $channelStyle }} font-size: 0.65rem; padding: 1px 8px;">
        {{ $order->channel_label }}
    </span>
    <span class="fw-semibold text-dark small">
        <i class="bi {{ $isOfficeOrder ? 'bi-building' : 'bi-person' }} me-1 text-muted"></i>{{ $order->ordered_for }}
    </span>
</div>

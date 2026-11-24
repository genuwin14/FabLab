{{-- Whether an order's total is still the system's estimate or the shop has
     confirmed it. The panel asked that the cost the system works out be
     shown as an estimate the shop confirms; approving an order is that
     confirmation (Admin\OrderController::review). Takes $order. --}}
@if($order->priceIsConfirmed())
    <span class="order-price-tag is-confirmed" title="FabLab confirmed this price when it approved the order">
        <i class="bi bi-patch-check-fill"></i> Confirmed
    </span>
    @if($order->priceWasAdjusted())
        <span class="order-price-was" title="The estimate at checkout">est. ₱{{ number_format($order->estimatedTotal(), 2) }}</span>
    @endif
@elseif(in_array($order->status, ['pending', 'awaiting_pr'], true))
    <span class="order-price-tag is-estimate" title="FabLab confirms the final price when it reviews this order">
        <i class="bi bi-hourglass-split"></i> Estimate
    </span>
@endif

@once
    <style>
        .order-price-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
            vertical-align: middle;
        }
        .order-price-tag.is-estimate { background: rgba(255, 197, 8, 0.2); color: #7a5c00; }
        .order-price-tag.is-confirmed { background: rgba(25, 135, 84, 0.12); color: #198754; }
        .order-price-was {
            margin-left: 0.25rem;
            font-size: 0.68rem;
            color: #6c757d;
            text-decoration: line-through;
            white-space: nowrap;
        }
    </style>
@endonce

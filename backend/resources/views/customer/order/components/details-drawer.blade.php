<!-- Order Details Right Drawer -->
@php
    $statusColors = [
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-info text-white',
        'paid' => 'bg-success text-white',
        'processing' => 'bg-info text-white',
        'awaiting_pr' => 'bg-secondary text-white',
        'ready_for_pickup' => 'bg-primary text-white',
        'for_delivery' => 'bg-primary text-white',
        'completed' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
    ];
    $statusLabel = \App\Models\Order::statusLabel($order->status);
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
        </div>

        {{-- The receipt number the cashier issued, recorded by staff when the
             order went into production. It is what the customer shows at the
             counter to collect the order, so it gets its own block rather
             than a footnote beside the status. --}}
        @if($order->payment_reference)
            <div class="customer-order-details-receipt mb-3">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <div class="customer-order-details-receipt-label">Receipt Number</div>
                        <div class="customer-order-details-receipt-number">{{ $order->payment_reference }}</div>
                    </div>
                    <i class="bi bi-receipt-cutoff fs-3 text-primary opacity-50"></i>
                </div>
                @if(in_array($order->status, ['approved', 'paid', 'processing', 'ready_for_pickup', 'for_delivery']))
                    <div class="text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>Bring this number to the FabLab to collect your order.
                    </div>
                @endif
            </div>
        @elseif($order->isAwaitingPayment())
            {{-- Approved but not yet paid: the slip below is what the customer
                 takes to the cashier, and the receipt number appears here once
                 the admin records it. --}}
            <div class="customer-order-details-receipt mb-3">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <div class="customer-order-details-receipt-label">Awaiting Payment</div>
                        <div class="small text-dark mt-1">
                            Pay at the CSPC Cashier with your transaction slip. Your receipt number will show here once it is recorded, and production starts then.
                        </div>
                    </div>
                    <i class="bi bi-cash-coin fs-3 text-primary opacity-50"></i>
                </div>
            </div>
        @endif

        @if($order->isPurchaseRequest())
            <div class="customer-order-details-summary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Paid by</span>
                    <span class="fw-medium">Purchase Request</span>
                </div>
                @if($order->pr_number)
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">PR Number</span>
                        <span class="fw-medium font-monospace">{{ $order->pr_number }}</span>
                    </div>
                @elseif($order->isAwaitingPr())
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">PR due by</span>
                        <span class="fw-medium">{{ $order->pr_deadline?->format('j M Y') ?? '—' }}</span>
                    </div>
                @endif
            </div>
        @endif

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
                @php
                    $design = $item->customDesign;
                    $product = $item->product;
                    $unitPrice = (float) ($item->price ?? 0);
                    $variantLabel = $item->productVariant?->label ?: '';
                    $snapshot = $design?->snapshot;
                    $thumb = $snapshot ?: ($product?->image_url ?: asset('img/FABLAB-LOGO.png'));

                    // What a tailored item is made of, read off the design the
                    // customer saved: the size and finish they picked, every
                    // line of text as they typed it, and how many images and
                    // shapes went on. The itemised charges come from the same
                    // recipe; the base is whatever is left of the unit price
                    // once they are taken off, so the lines add up to what
                    // was actually charged even if a rate has moved since.
                    $recipe = $design?->recipe ?? [];
                    $elements = $recipe['elements'] ?? [];
                    $sizeKey = $design ? \App\Models\CustomizationRate::keyForSize($recipe['size'] ?? null) : null;
                    $sizeLabel = $sizeKey ? \App\Models\CustomizationRate::DEFINITIONS[$sizeKey]['label'] : null;
                    $sizeShort = $sizeKey ? \App\Models\CustomizationRate::DEFINITIONS[$sizeKey]['short'] : null;
                    $finishLabel = null;
                    if ($design) {
                        if ($texture = $design->texture()) {
                            $finishLabel = $texture->name . ' texture';
                        } elseif ($color = $design->color()) {
                            $finishLabel = $color->name;
                        } elseif (!empty($recipe['color_hex'])) {
                            $finishLabel = strtoupper($recipe['color_hex']);
                        }
                    }
                    $textLines = collect($elements['text'] ?? [])->pluck('text')->filter(fn ($t) => trim((string) $t) !== '')->values();
                    $logoCount = count($elements['logos'] ?? []);
                    $shapeCount = count($elements['shapes'] ?? []);
                    $hasLed = !empty($recipe['features']['led_lighting']);
                    $breakdown = $design ? $design->price_breakdown : [];
                    $extras = array_sum(array_column($breakdown, 'amount'));
                    $basePrice = $unitPrice - $extras >= 0 ? $unitPrice - $extras : (float) ($product?->price ?? 0);
                @endphp
                <div class="customer-order-details-item flex-column align-items-stretch gap-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 overflow-hidden border bg-white flex-shrink-0"
                            style="width: 52px; height: 52px;">
                            <img src="{{ $thumb }}" class="w-100 h-100 object-fit-cover" alt="">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark text-truncate">
                                {{ $product?->name ?? 'Product' }}
                                @if($item->custom_design_id)
                                    <span
                                        class="badge bg-soft-primary text-primary tiny rounded-pill border border-primary-subtle ms-1">Custom</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                @if($variantLabel !== '')
                                    <span class="badge bg-light text-dark border tiny rounded-pill fw-semibold">{{ $variantLabel }}</span>
                                @elseif($sizeLabel)
                                    <span class="badge bg-light text-dark border tiny rounded-pill fw-semibold">{{ $sizeShort }}</span>
                                @endif
                                @if($product?->sku)
                                    <span class="text-muted tiny font-monospace">{{ $product->sku }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold text-dark">₱{{ number_format($unitPrice * $item->quantity, 2) }}</div>
                            <div class="text-muted tiny">₱{{ number_format($unitPrice, 2) }} × {{ $item->quantity }}</div>
                        </div>
                    </div>

                    @if($design)
                        <div class="customer-order-details-design mt-2">
                            @if($snapshot)
                                <div class="customer-order-details-design-preview mb-2">
                                    <img src="{{ $snapshot }}" alt="Your design">
                                </div>
                            @endif

                            <div class="customer-order-details-design-grid">
                                @if($sizeLabel)
                                    <span class="text-muted">Size</span><span class="fw-medium text-dark">{{ $sizeLabel }}</span>
                                @endif
                                @if($finishLabel)
                                    <span class="text-muted">Finish</span><span class="fw-medium text-dark">{{ $finishLabel }}</span>
                                @endif
                                @if($textLines->isNotEmpty())
                                    <span class="text-muted">Text</span>
                                    <span class="fw-medium text-dark">
                                        @foreach($textLines as $line)
                                            <span class="d-block text-truncate">“{{ $line }}”</span>
                                        @endforeach
                                    </span>
                                @endif
                                @if($logoCount)
                                    <span class="text-muted">Images</span><span class="fw-medium text-dark">{{ $logoCount }} uploaded</span>
                                @endif
                                @if($shapeCount)
                                    <span class="text-muted">Shapes</span><span class="fw-medium text-dark">{{ $shapeCount }}</span>
                                @endif
                                @if($hasLed)
                                    <span class="text-muted">Lighting</span><span class="fw-medium text-dark">Internal LED</span>
                                @endif
                            </div>

                            <div class="customer-order-details-design-price mt-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Base {{ $product?->name ?? 'product' }}</span>
                                    <span>₱{{ number_format($basePrice, 2) }}</span>
                                </div>
                                @foreach($breakdown as $line)
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{{ $line['label'] }}</span>
                                        <span>+ ₱{{ number_format($line['amount'], 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-between fw-bold text-dark border-top pt-1 mt-1">
                                    <span>Per item</span>
                                    <span>₱{{ number_format($unitPrice, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
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
        @if(in_array($order->status, ['approved', 'paid', 'processing', 'ready_for_pickup', 'for_delivery', 'completed']))
            <a href="{{ route('customer.orders.receipt', $order->order_id) }}" target="_blank"
                class="btn w-100 rounded-pill fw-bold mt-3 d-flex align-items-center justify-content-center"
                style="background-color: #0e2e45; color: #ffffff;">
                <i class="bi bi-receipt me-2"></i> View Slip
            </a>
        @endif
    </div>
</div>

{{--
    The cells behind a product's total, under the stock figure in the
    product lists.

    "200 shirts" is the total of a grid; what the shop needs to see is that
    the Navy 5XL is down to one. Collapsed by default so the list stays a
    list, and the low and empty cells are the ones that stand out.

    Expects: $product with variants.color loaded.
--}}
@if($product->variants->isNotEmpty())
    @php
        // A cell's line is its own threshold, else its share of the product's.
        $cells = max(1, $product->variants->count());
        $threshold = max(1, (int) ceil((int) $product->low_stock_threshold / $cells));
        $short = $product->variants->filter(fn ($v) => (int) $v->stock <= max(0, (int) ($v->low_stock_threshold ?? $threshold)));
    @endphp
    <button class="btn btn-link btn-sm p-0 text-decoration-none mt-1 variant-breakdown-toggle" type="button"
        data-bs-toggle="collapse" data-bs-target="#variants-{{ $product->product_id }}"
        aria-expanded="false" aria-controls="variants-{{ $product->product_id }}">
        <i class="bi bi-grid-3x3-gap me-1"></i>by size / colour
        @if($short->isNotEmpty())
            <span class="badge rounded-pill bg-danger ms-1" title="{{ $short->count() }} low or empty">{{ $short->count() }}</span>
        @endif
    </button>
    <div class="collapse" id="variants-{{ $product->product_id }}">
        <div class="variant-breakdown mt-1">
            @foreach($product->variants as $variant)
                @php
                    $cellThreshold = max(0, (int) ($variant->low_stock_threshold ?? $threshold));
                    $state = (int) $variant->stock <= 0 ? 'out' : ((int) $variant->stock <= $cellThreshold ? 'low' : 'ok');
                @endphp
                <div class="variant-breakdown-row is-{{ $state }}">
                    <span class="text-truncate">
                        @if($variant->color)
                            <span class="variant-breakdown-swatch" style="background: {{ $variant->color->hex_code }}"></span>
                        @endif
                        {{ $variant->label !== '' ? $variant->label : 'Standard' }}
                    </span>
                    <span class="fw-bold">{{ $variant->stock }}</span>
                </div>
            @endforeach
        </div>
    </div>

    @once
        <style>
            .variant-breakdown-toggle { font-size: 0.7rem; white-space: nowrap; }
            .variant-breakdown { font-size: 0.72rem; width: 150px; }
            .variant-breakdown-row { display: flex; justify-content: space-between; gap: 6px; padding: 1px 4px; border-radius: 4px; }
            .variant-breakdown-row.is-low { background: rgba(255, 193, 7, .18); }
            .variant-breakdown-row.is-out { background: rgba(220, 53, 69, .12); color: #b02a37; }
            .variant-breakdown-swatch { display: inline-block; width: 9px; height: 9px; border-radius: 2px; border: 1px solid rgba(0,0,0,.15); vertical-align: -1px; margin-right: 3px; }
        </style>
    @endonce
@endif

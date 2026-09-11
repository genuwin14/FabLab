{{--
    The cells behind a product's total, under the stock figure in the
    product lists.

    "200 shirts" is the total of a grid; what the shop needs to see is that
    the Navy 5XL is down to one. Thirty-two cells inline made the row a
    screen tall, so the figure carries a button that opens the grid in a
    modal: sizes down, colours across, the low and empty cells tinted. The
    button itself shows how many cells are low or empty.

    One modal per page, filled from the button's data, so the list carries
    no markup per cell. Expects: $product with variants.color loaded, and
    $sizes (CustomizationRate::sizes()) in scope for the row order.
--}}
@if($product->variants->isNotEmpty())
    @php
        // A cell's line is its own threshold, else its share of the product's.
        $cells = max(1, $product->variants->count());
        $threshold = max(1, (int) ceil((int) $product->low_stock_threshold / $cells));

        $breakdown = $product->variants->map(function ($v) use ($threshold) {
            $line = max(0, (int) ($v->low_stock_threshold ?? $threshold));
            return [
                'size' => $v->size,
                'size_short' => $v->sizeShort(),
                'color_id' => $v->color_id,
                'color' => $v->color?->name,
                'hex' => $v->color?->hex_code,
                'stock' => (int) $v->stock,
                'state' => (int) $v->stock <= 0 ? 'out' : ((int) $v->stock <= $line ? 'low' : 'ok'),
            ];
        })->values();
        $short = $breakdown->filter(fn ($c) => $c['state'] !== 'ok')->count();
    @endphp
    <button class="btn btn-link btn-sm p-0 text-decoration-none mt-1 variant-breakdown-toggle" type="button"
        onclick="openVariantBreakdown(this)"
        data-name="{{ $product->name }}"
        data-unit="{{ $product->unit }}"
        data-total="{{ $product->stock }}"
        data-has-sizes="{{ $product->has_sizes ? 1 : 0 }}"
        data-cells="{{ $breakdown->toJson() }}">
        <i class="bi bi-grid-3x3-gap me-1"></i>by size / colour
        @if($short > 0)
            <span class="badge rounded-pill bg-danger ms-1" title="{{ $short }} low or empty">{{ $short }}</span>
        @endif
    </button>

    @once
        <div class="modal fade" id="variantBreakdownModal" tabindex="-1" aria-labelledby="variantBreakdownTitle" aria-hidden="true">
            {{-- Wide enough for four colours across; more scroll inside the table. --}}
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 pb-0 align-items-start">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" id="variantBreakdownTitle"></h6>
                            <small class="text-muted" id="variantBreakdownSubtitle"></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive border rounded-3">
                            <table class="table table-sm align-middle mb-0 variant-breakdown-table">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Tinted cells are at or below their low-stock line; red ones are empty. Edit the product to change them.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .variant-breakdown-toggle { font-size: 0.7rem; white-space: nowrap; }
            .variant-breakdown-table th, .variant-breakdown-table td { font-size: 0.8rem; white-space: nowrap; }
            .variant-breakdown-table thead th { background: #f1f4f8; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: .04em; color: #0e2e45; }
            .variant-breakdown-table td.variant-breakdown-cell { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
            .variant-breakdown-table td.is-low { background: rgba(255, 193, 7, .18); }
            .variant-breakdown-table td.is-out { background: rgba(220, 53, 69, .12); color: #b02a37; font-weight: 700; }
            .variant-breakdown-table tfoot td { font-weight: 700; background: #f8f9fa; }
            .variant-breakdown-swatch { display: inline-block; width: 11px; height: 11px; border-radius: 3px; border: 1px solid rgba(0,0,0,.15); vertical-align: -1px; margin-right: 4px; }
        </style>

        <script>
            /** The sizes in the order the grid lists them, Small to 5XL. */
            const variantBreakdownSizes = @json($sizes);

            /**
             * Fill the breakdown modal from a list button and open it: one row
             * per size the product carries (or one row), one column per colour
             * (or one column), a total row underneath.
             */
            function openVariantBreakdown(button) {
                const data = button.dataset;
                let cells = [];
                try { cells = JSON.parse(data.cells || '[]'); } catch (e) { cells = []; }

                const hasSizes = data.hasSizes === '1';
                const colours = [];
                cells.forEach(c => { if (c.color_id && !colours.find(x => x.id === c.color_id)) colours.push({ id: c.color_id, name: c.color, hex: c.hex }); });
                const columns = colours.length ? colours : [null];

                const sizeRows = hasSizes
                    ? Object.entries(variantBreakdownSizes)
                        .filter(([key]) => cells.some(c => c.size === key))
                        .map(([key, def]) => ({ key, label: def.label, short: def.short }))
                    : [{ key: null, label: 'One size', short: '—' }];

                const cellFor = (size, colourId) => cells.find(c => (c.size || null) === size && String(c.color_id || '') === String(colourId || ''));

                const table = document.querySelector('#variantBreakdownModal .variant-breakdown-table');
                const thead = table.querySelector('thead');
                const tbody = table.querySelector('tbody');
                thead.innerHTML = '';
                tbody.innerHTML = '';
                table.querySelector('tfoot')?.remove();

                const head = document.createElement('tr');
                head.innerHTML = '<th class="ps-3">' + (hasSizes ? 'Size' : '') + '</th>' + columns.map(c => c
                    ? `<th class="text-end pe-3"><span class="variant-breakdown-swatch" style="background:${c.hex}"></span>${c.name}</th>`
                    : '<th class="text-end pe-3">Stock</th>').join('');
                thead.appendChild(head);

                const columnTotals = columns.map(() => 0);
                sizeRows.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td class="ps-3 fw-semibold text-dark">${row.short}<span class="text-muted fw-normal ms-1">${hasSizes ? row.label : ''}</span></td>`;
                    columns.forEach((c, i) => {
                        const cell = cellFor(row.key, c ? c.id : null);
                        const td = document.createElement('td');
                        td.className = 'pe-3 variant-breakdown-cell' + (cell ? ' is-' + cell.state : '');
                        td.textContent = cell ? cell.stock : '—';
                        if (cell) columnTotals[i] += cell.stock;
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });

                if (columns.length > 1 || sizeRows.length > 1) {
                    const tfoot = document.createElement('tfoot');
                    tfoot.innerHTML = '<tr><td class="ps-3">Total</td>' + columnTotals.map(t => `<td class="pe-3 text-end">${t}</td>`).join('') + '</tr>';
                    table.appendChild(tfoot);
                }

                const short = cells.filter(c => c.state !== 'ok').length;
                document.getElementById('variantBreakdownTitle').textContent = data.name;
                document.getElementById('variantBreakdownSubtitle').textContent =
                    `${data.total} ${data.unit} in ${cells.length} cells` + (short ? ` · ${short} low or empty` : '');

                bootstrap.Modal.getOrCreateInstance(document.getElementById('variantBreakdownModal')).show();
            }
        </script>
    @endonce
@endif

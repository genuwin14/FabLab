{{--
    Stock by size and colour: the grid half of a product's stock.

    A product that comes in sizes, or has colours assigned, is stocked per
    cell — a Navy 5XL and a White Medium are different stock — and its
    single Stock field is the read-only total of the cells. This draws the
    grid from the product JSON the edit modal is opened with, posts one
    figure per cell as `variants[<key>][stock]`, and keeps the total in step
    as figures are typed.

    A cell the grid should have but the product doesn't yet — the admin has
    just ticked "comes in sizes", or assigned a colour — is shown but can't
    be typed into until the product is saved: saving creates the cell (and
    moves any stock the product had into it), and then the figure can be
    set. That way a figure is never posted for a cell that doesn't exist.

    Expects: $gridId, $totalInputId, $sizesInputId (the "comes in sizes"
    checkbox, so the grid follows it live), $sizes (CustomizationRate::sizes()).
--}}
<div class="variant-grid" id="{{ $gridId }}" hidden
    data-total-input="{{ $totalInputId }}" data-sizes-input="{{ $sizesInputId }}">
    <h6 class="product-section-title mt-4">
        <i class="bi bi-grid-3x3-gap me-2"></i>Stock by size and colour
    </h6>
    <p class="small text-muted mb-2 variant-grid-intro"></p>
    <div class="table-responsive border rounded-3">
        <table class="table table-sm align-middle mb-0 variant-grid-table">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
    <small class="text-muted d-block mt-2 variant-grid-hint"></small>
</div>

@once
    <style>
        .variant-grid-table th, .variant-grid-table td { font-size: 0.8rem; white-space: nowrap; }
        .variant-grid-table thead th { background: #f1f4f8; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: .04em; color: #0e2e45; }
        .variant-grid-table .variant-cell { width: 84px; text-align: right; font-variant-numeric: tabular-nums; }
        .variant-grid-table .variant-cell.is-low { border-color: #dc3545; background: rgba(220, 53, 69, .06); }
        .variant-grid-table .variant-cell.is-out { border-color: #dc3545; background: rgba(220, 53, 69, .12); font-weight: 700; color: #b02a37; }
        .variant-grid-table .variant-cell:disabled { background: #f8f9fa; color: #adb5bd; font-style: italic; }
        .variant-grid-table .variant-swatch { display: inline-block; width: 11px; height: 11px; border-radius: 3px; border: 1px solid rgba(0,0,0,.15); vertical-align: -1px; margin-right: 4px; }
    </style>

    <script>
        /**
         * Draw a product's stock grid into #gridId and wire it to the total.
         *
         * Cells come from product.variants (keyed by variant_key); the rows
         * and columns from product.has_sizes (or the checkbox, live) and
         * product.colors. Returns nothing; hides the grid and frees the
         * total field for a product that has no cells.
         */
        function renderVariantGrid(gridId, product, sizes) {
            const grid = document.getElementById(gridId);
            if (!grid) return;

            const total = document.getElementById(grid.dataset.totalInput);
            const sizesInput = document.getElementById(grid.dataset.sizesInput);
            const hasSizes = sizesInput ? sizesInput.checked : !!product.has_sizes;
            const colours = Array.isArray(product.colors) ? product.colors : [];
            const existing = {};
            (product.variants || []).forEach(v => { existing[v.variant_key] = v; });

            const tracks = hasSizes || colours.length > 0;
            grid.hidden = !tracks;
            if (total) {
                total.readOnly = tracks;
                total.classList.toggle('bg-light', tracks);
                total.title = tracks ? 'The total of the cells below. Set the figures there.' : '';
            }
            if (!tracks) return;

            const sizeRows = hasSizes
                ? Object.entries(sizes).map(([key, def]) => ({ key, label: def.label, short: def.short }))
                : [{ key: null, label: 'One size', short: '—' }];
            const colourCols = colours.length ? colours : [null];
            const keyFor = (size, colourId) => (size || '-') + ':' + (colourId || '-');
            // A cell's low line is its share of the product's threshold.
            const threshold = Math.max(1, Math.ceil(Number(product.low_stock_threshold ?? 0) / (sizeRows.length * colourCols.length)));

            const thead = grid.querySelector('thead');
            const tbody = grid.querySelector('tbody');
            thead.innerHTML = '';
            tbody.innerHTML = '';

            const head = document.createElement('tr');
            head.innerHTML = '<th class="ps-3">' + (hasSizes ? 'Size' : '') + '</th>' + colourCols.map(c => c
                ? `<th class="text-end pe-2"><span class="variant-swatch" style="background:${c.hex_code}"></span>${c.name}</th>`
                : '<th class="text-end pe-2">Stock</th>').join('');
            thead.appendChild(head);

            let pending = 0;
            sizeRows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td class="ps-3 fw-semibold text-dark">${row.short}<span class="text-muted fw-normal ms-1">${hasSizes ? row.label : ''}</span></td>`;
                colourCols.forEach(c => {
                    const key = keyFor(row.key, c ? c.color_id : null);
                    const cell = existing[key];
                    const td = document.createElement('td');
                    td.className = 'text-end pe-2';
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.min = '0';
                    input.step = '1';
                    input.className = 'form-control form-control-sm variant-cell';
                    if (cell) {
                        input.name = `variants[${key}][stock]`;
                        input.value = cell.stock ?? 0;
                        input.setAttribute('aria-label', `Stock of ${row.label}${c ? ' in ' + c.name : ''}`);
                    } else {
                        input.disabled = true;
                        input.placeholder = 'save first';
                        input.title = 'Save the product to create this cell, then set its stock.';
                        pending++;
                    }
                    td.appendChild(input);
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

            const recalc = () => {
                let sum = 0;
                grid.querySelectorAll('.variant-cell:not(:disabled)').forEach(input => {
                    const n = Math.max(0, parseInt(input.value, 10) || 0);
                    sum += n;
                    input.classList.toggle('is-out', n <= 0);
                    input.classList.toggle('is-low', n > 0 && threshold > 0 && n <= threshold);
                });
                if (total) total.value = pending ? sum : sum;
            };
            grid.querySelectorAll('.variant-cell').forEach(input => input.addEventListener('input', recalc));
            recalc();

            grid.querySelector('.variant-grid-intro').textContent = hasSizes && colours.length
                ? 'One figure per size and colour. The Stock field above is their total.'
                : hasSizes ? 'One figure per size. The Stock field above is their total.'
                : 'One figure per colour. The Stock field above is their total.';
            grid.querySelector('.variant-grid-hint').textContent = pending
                ? (pending === grid.querySelectorAll('.variant-cell').length
                    ? 'Save the product to create these cells; its current stock moves into the first one, and you can spread it from there.'
                    : 'Greyed cells are new. Save the product to create them, then set their stock.')
                : (colours.length ? 'Colours come from the palette assigned to this product.' : 'Assign colours to this product to stock it per colour as well.');
        }

        /** Re-draw when "comes in sizes" is toggled, so the rows follow it. */
        function wireVariantGrid(gridId, product, sizes) {
            renderVariantGrid(gridId, product, sizes);
            const grid = document.getElementById(gridId);
            const sizesInput = grid ? document.getElementById(grid.dataset.sizesInput) : null;
            if (sizesInput) {
                sizesInput.onchange = () => renderVariantGrid(gridId, product, sizes);
            }
        }
    </script>
@endonce

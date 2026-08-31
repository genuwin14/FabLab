{{--
    "What will this take off the shelf?" — the materials half of an order,
    beside the finished-goods stock check that was there before.

    The finished-goods table answers "do we have a mug to sell?". This answers
    "do we have what it takes to make the one they designed?", which is the
    question the customization BOM made answerable: a design with twelve lines
    of text and internal lighting draws ink and an LED kit that the product's
    own bill of materials says nothing about.

    Filled by fetchOrderMaterials() below when the modal opens. Everything is
    rendered from the JSON rather than baked into the page, because working it
    out per order is too expensive to do for a whole list.

    Expects: $panelId. The caller passes the URL to fetchOrderMaterials() when
    it opens the modal, because only the caller knows which order it is for.
--}}
<div id="{{ $panelId }}" class="order-materials d-none">
    <h6 class="order-section-title">
        <i class="bi bi-boxes me-2"></i><span class="materials-heading">Materials Required</span>
    </h6>

    <div class="materials-loading text-center text-muted small py-3">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Working out what this order needs…
    </div>

    <div class="materials-error alert alert-warning border-0 rounded-3 small d-none mb-3">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Couldn't load the materials for this order. Approving still checks stock, so nothing can be overdrawn.
    </div>

    <div class="materials-content d-none">
        <div class="materials-shortage alert alert-danger border-0 rounded-3 small d-none mb-3">
            <div class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-1"></i>Not enough stock to approve this order</div>
            <ul class="mb-0 ps-3 materials-shortage-list"></ul>
        </div>

        <div class="table-responsive border rounded-3 mb-2 overflow-hidden modal-table-scroll">
            <table class="table table-hover align-middle mb-0 modal-table">
                <thead>
                    <tr class="bg-primary bg-opacity-10">
                        <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Material</th>
                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0 col-deduct">To Deduct</th>
                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">In Stock</th>
                        <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0 col-remaining">Remaining</th>
                    </tr>
                </thead>
                <tbody class="materials-body border-top-0"></tbody>
            </table>
        </div>

        <p class="materials-note text-muted small mb-0"></p>
    </div>

    <div class="materials-empty text-muted small d-none">
        No raw materials are mapped to this order's product or its design, so nothing will be deducted.
    </div>
</div>

@once
    <style>
        .order-materials .materials-short td { background-color: rgba(220, 53, 69, 0.06); }
        .order-materials .materials-qty { font-variant-numeric: tabular-nums; }
        .order-materials .materials-note { line-height: 1.4; }
    </style>

    <script>
        /**
         * Load an order's material draw into a panel rendered by this partial.
         *
         * Kept tolerant on purpose: this is a preview, and the approval itself
         * re-checks stock server-side before writing anything. A panel that
         * fails to load must not stop an admin approving an order.
         */
        function fetchOrderMaterials(panelId, url) {
            const panel = document.getElementById(panelId);
            if (!panel) return;

            const loading = panel.querySelector('.materials-loading');
            const error = panel.querySelector('.materials-error');
            const content = panel.querySelector('.materials-content');
            const empty = panel.querySelector('.materials-empty');
            const body = panel.querySelector('.materials-body');

            panel.classList.remove('d-none');
            loading.classList.remove('d-none');
            error.classList.add('d-none');
            content.classList.add('d-none');
            empty.classList.add('d-none');
            body.innerHTML = '';

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(data => {
                    loading.classList.add('d-none');

                    panel.querySelector('.materials-heading').textContent =
                        data.stage === 'consume' ? 'Materials To Be Consumed'
                        : data.stage === 'consumed' ? 'Materials Already Drawn'
                        : 'Materials Required';

                    // "Remaining" only means something while the stock has yet
                    // to move. After approval it already has, so the column
                    // would just repeat In Stock.
                    const showRemaining = data.stage === 'reserve';
                    panel.querySelectorAll('.col-remaining').forEach(th => th.classList.toggle('d-none', !showRemaining));

                    if (!data.lines.length) {
                        empty.classList.remove('d-none');
                        return;
                    }

                    data.lines.forEach(line => {
                        const row = document.createElement('tr');
                        if (line.short) row.className = 'materials-short';

                        const unit = line.unit ? ' ' + line.unit : '';
                        row.innerHTML = `
                            <td class="ps-3 fw-semibold text-dark"></td>
                            <td class="text-center materials-qty fw-bold"></td>
                            <td class="text-center materials-qty"></td>
                            <td class="text-end pe-3 materials-qty col-remaining${showRemaining ? '' : ' d-none'}"></td>`;

                        const cells = row.querySelectorAll('td');
                        cells[0].textContent = line.name;
                        cells[1].textContent = '−' + line.quantity + unit;
                        cells[1].classList.add(line.short ? 'text-danger' : 'text-dark');
                        cells[2].textContent = line.stock + unit;
                        cells[3].textContent = line.remaining === null ? '—' : line.remaining + unit;

                        body.appendChild(row);
                    });

                    const shortage = panel.querySelector('.materials-shortage');
                    const list = panel.querySelector('.materials-shortage-list');
                    list.innerHTML = '';
                    shortage.classList.toggle('d-none', !data.shortages.length);
                    data.shortages.forEach(text => {
                        const li = document.createElement('li');
                        li.textContent = text;
                        list.appendChild(li);
                    });

                    panel.querySelector('.materials-note').textContent = data.note;
                    content.classList.remove('d-none');
                })
                .catch(() => {
                    loading.classList.add('d-none');
                    error.classList.remove('d-none');
                });
        }
    </script>
@endonce

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

        {{-- Only shown when the panel is editable. The estimate is a formula's
             best guess at coverage; the reviewer is looking at the artwork.

             Shown and hidden through the hidden attribute rather than d-none,
             because pairing it with d-flex pits two !important display
             utilities against each other and d-flex wins — which left this row
             and its Reset button on screens that never wired them up. --}}
        <div class="materials-adjust-hint justify-content-between align-items-start gap-2 mb-2" hidden>
            <small class="text-muted">
                <i class="bi bi-pencil-square me-1"></i>Ink is measured from the artwork; the working is under each
                line. Correct any figure if the print in front of you says otherwise.
            </small>
            {{-- Disabled until something has actually been changed. It used to
                 sit there enabled with nothing to undo, so clicking it did
                 nothing and read as broken. --}}
            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-nowrap materials-reset-btn"
                title="Put the calculated estimates back" disabled>
                <i class="bi bi-arrow-counterclockwise"></i> Undo my changes
            </button>
        </div>

        {{-- The flat prints the ink figures were measured from — every panel's
             artwork, none of the garment — so the reviewer sees exactly what
             the printer will lay down. Hidden when the order has none. --}}
        <div class="materials-prints d-none mb-2">
            <small class="text-muted d-block mb-1"><i class="bi bi-printer me-1"></i>Measured from these prints</small>
            <div class="d-flex flex-wrap gap-2 materials-prints-list"></div>
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

        /* Not a d-flex utility — those are !important and would beat the
           hidden attribute this row is toggled with. */
        .order-materials .materials-adjust-hint { display: flex; }
        .order-materials .materials-adjust-hint[hidden] { display: none; }

        /* A row the reviewer overrode, so the two kinds of number are told
           apart at a glance. */
        .order-materials .materials-edited td { background-color: rgba(13, 110, 253, 0.05); }
        .order-materials .materials-edited .materials-input {
            border-color: #0d6efd;
            font-weight: 600;
        }

        .order-materials .materials-reset-btn:disabled { opacity: .4; }

        .order-materials .materials-input { width: 92px; }

        /* The working behind a measured ink figure, under the bottle's name. */
        .order-materials .materials-working { font-size: 0.72rem; line-height: 1.3; white-space: normal; }

        /* Checkerboard behind a print, so white artwork on a transparent
           canvas is visible rather than lost against the modal. */
        .order-materials .materials-print {
            width: 72px; height: 72px; object-fit: contain; border-radius: 6px; border: 1px solid #dee2e6;
            background-color: #eee;
            background-image: linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%),
                              linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%);
            background-size: 12px 12px; background-position: 0 0, 6px 6px;
        }
    </style>

    <script>
        /**
         * Load an order's material draw into a panel rendered by this partial.
         *
         * Kept tolerant on purpose: this is a preview, and the approval itself
         * re-checks stock server-side before writing anything. A panel that
         * fails to load must not stop an admin approving an order.
         */
        /**
         * Keep Remaining and the shortage warning honest while the reviewer
         * types.
         *
         * Recalculated in the browser rather than round-tripping: the server
         * re-checks every figure on submit anyway, so this only has to be good
         * enough to steer someone away from a number that won't fit.
         */
        function wireAdjustments(panel) {
            const shortage = panel.querySelector('.materials-shortage');
            const list = panel.querySelector('.materials-shortage-list');
            const inputs = panel.querySelectorAll('.materials-input');
            const resetBtn = panel.querySelector('.materials-reset-btn');

            const round = value => Math.round(value * 100) / 100;

            function recalculate() {
                const problems = [];
                let changed = 0;

                inputs.forEach(input => {
                    const row = input.closest('tr');
                    const stock = parseFloat(input.dataset.stock);
                    const wanted = parseFloat(input.value);
                    const remaining = row.querySelector('.col-remaining');
                    const unit = row.querySelector('td:nth-child(3)').textContent.replace(/^[\d.,]+\s*/, '');

                    if (!Number.isFinite(wanted) || wanted < 0) {
                        remaining.textContent = '—';
                        return;
                    }

                    // Mark the rows a person has overridden, so it is obvious
                    // which figures are a judgement and which the formula's.
                    const edited = Math.abs(wanted - parseFloat(input.dataset.calculated)) > 0.0001;
                    row.classList.toggle('materials-edited', edited);
                    if (edited) changed++;

                    const over = wanted > stock;
                    row.classList.toggle('materials-short', over);
                    input.classList.toggle('is-invalid', over);
                    remaining.textContent = round(Math.max(0, stock - wanted)) + (unit ? ' ' + unit : '');

                    if (over) {
                        const name = row.querySelector('td').textContent;
                        problems.push(`${name} (needs ${round(wanted)}${unit ? ' ' + unit : ''}, ${round(stock)} in stock)`);
                    }
                });

                list.innerHTML = '';
                shortage.classList.toggle('d-none', !problems.length);
                problems.forEach(text => {
                    const li = document.createElement('li');
                    li.textContent = text;
                    list.appendChild(li);
                });

                // Nothing changed means nothing to undo.
                resetBtn.disabled = changed === 0;
            }

            inputs.forEach(input => input.addEventListener('input', recalculate));

            resetBtn.onclick = () => {
                inputs.forEach(input => { input.value = input.dataset.calculated; });
                recalculate();
            };

            recalculate();
        }

        function fetchOrderMaterials(panelId, url, editable = false) {
            const panel = document.getElementById(panelId);
            if (!panel) return;

            const loading = panel.querySelector('.materials-loading');
            const error = panel.querySelector('.materials-error');
            const content = panel.querySelector('.materials-content');
            const empty = panel.querySelector('.materials-empty');
            const body = panel.querySelector('.materials-body');

            panel.classList.remove('d-none');
            panel.dataset.editable = editable ? '1' : '';
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

                    // Correcting the estimate only makes sense while nothing has
                    // moved yet, and only for the materials a reviewer can weigh
                    // up. The caller decides whether this screen allows it.
                    const canEdit = editable && data.stage === 'reserve';
                    panel.querySelector('.materials-adjust-hint').hidden = !canEdit;

                    data.lines.forEach(line => {
                        const row = document.createElement('tr');
                        const unit = line.unit ? ' ' + line.unit : '';
                        const editThis = canEdit && line.editable && line.id !== null;

                        row.innerHTML = `
                            <td class="ps-3 fw-semibold text-dark"></td>
                            <td class="text-center materials-qty fw-bold"></td>
                            <td class="text-center materials-qty"></td>
                            <td class="text-end pe-3 materials-qty col-remaining${showRemaining ? '' : ' d-none'}"></td>`;

                        const cells = row.querySelectorAll('td');
                        cells[0].textContent = line.name;
                        // A measured ink line explains itself: coverage,
                        // area, rate. Shown at every stage, because "why
                        // 9ml of cyan?" is as fair a question at the bench
                        // as at review.
                        (line.notes || []).forEach(text => {
                            const note = document.createElement('small');
                            note.className = 'text-muted d-block fw-normal materials-working';
                            note.textContent = text;
                            cells[0].appendChild(note);
                        });
                        cells[2].textContent = line.stock + unit;

                        if (editThis) {
                            const input = document.createElement('input');
                            input.type = 'number';
                            input.step = '0.01';
                            input.min = '0';
                            input.className = 'form-control form-control-sm text-end materials-input';
                            input.name = `material_quantities[${line.id}]`;
                            input.value = line.quantity;
                            input.dataset.calculated = line.quantity;
                            input.dataset.stock = line.stock;
                            input.setAttribute('aria-label', 'Quantity of ' + line.name + ' to deduct');

                            const wrap = document.createElement('div');
                            wrap.className = 'd-flex align-items-center justify-content-center gap-1';
                            wrap.appendChild(input);
                            if (line.unit) {
                                const suffix = document.createElement('span');
                                suffix.className = 'text-muted small';
                                suffix.textContent = line.unit;
                                wrap.appendChild(suffix);
                            }

                            cells[1].classList.remove('fw-bold');
                            cells[1].appendChild(wrap);
                        } else {
                            cells[1].textContent = '−' + line.quantity + unit;
                            cells[1].classList.add(line.short ? 'text-danger' : 'text-dark');
                            if (line.short) row.classList.add('materials-short');
                        }

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

                    const prints = panel.querySelector('.materials-prints');
                    const printList = panel.querySelector('.materials-prints-list');
                    printList.innerHTML = '';
                    (data.prints || []).forEach(url => {
                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = 'The flat print this order\'s ink was measured from';
                        img.className = 'materials-print';
                        printList.appendChild(img);
                    });
                    prints.classList.toggle('d-none', !(data.prints || []).length);

                    panel.querySelector('.materials-note').textContent = data.note;
                    content.classList.remove('d-none');

                    if (canEdit) wireAdjustments(panel);
                })
                .catch(() => {
                    loading.classList.add('d-none');
                    error.classList.remove('d-none');
                });
        }
    </script>
@endonce

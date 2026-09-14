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

    Layout follows the panel's own width rather than the viewport (a container
    query): in the admin's wide review modal it is a table, in the staff's
    narrower status modal and on a phone each material becomes a stacked card
    with its three figures labelled underneath. The same partial serves both,
    so the switch has to come from the space it is given, not from the screen.

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

        {{-- Worth a look but not a block: a print bigger than the transfer
             sheet, which the printers can't do in one piece. --}}
        <div class="materials-warnings alert alert-warning border-0 rounded-3 small d-none mb-3">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Check before approving</div>
            <ul class="mb-0 ps-3 materials-warnings-list"></ul>
        </div>

        <div class="table-responsive border rounded-3 mb-2 overflow-hidden modal-table-scroll materials-table-wrap">
            <table class="table table-hover align-middle mb-0 modal-table materials-table">
                {{-- The three figures get fixed columns so the material's
                     name and its working take whatever is left. Without this
                     the auto layout squeezed In Stock until "2575 pcs" broke
                     across two lines. --}}
                <colgroup>
                    <col class="materials-col-name">
                    <col class="materials-col-deduct">
                    <col class="materials-col-stock">
                    <col class="materials-col-remaining">
                </colgroup>
                <thead>
                    <tr class="bg-primary bg-opacity-10">
                        <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0">Material</th>
                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0 text-nowrap col-deduct">To Deduct</th>
                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0 text-nowrap">In Stock</th>
                        <th class="text-end pe-3 py-2 text-primary small text-uppercase fw-bold border-0 text-nowrap col-remaining">Remaining</th>
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

        {{-- The prints the ink figures were measured from — every panel's
             artwork, none of the garment — so the reviewer sees exactly what
             the printer will lay down. Hidden when the order has none.

             The studio's print is the model's whole texture atlas, and a
             shirt's chest is a small rectangle in it. So each panel the
             design records is cropped out and shown on its own, labelled
             Front, Back and so on; a design saved before panels were
             recorded is shown whole.

             A thumbnail is the affordance, not the view: clicking one opens it
             in the stage below at a size coverage can actually be judged
             from. Inline rather than a second modal, for the same reason the
             design preview above is — a modal on a modal fights the backdrop. --}}
        <div class="materials-prints d-none mb-2">
            <small class="text-muted d-block mb-1"><i class="bi bi-printer me-1"></i>Measured from these prints — click a panel to enlarge</small>
            <div class="d-flex flex-wrap gap-2 materials-prints-list"></div>

            <div class="materials-print-preview mt-2" hidden>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                    <small class="text-muted"><i class="bi bi-zoom-in me-1"></i><span class="materials-print-preview-title">Print</span></small>
                    <div class="d-flex align-items-center gap-3">
                        <a class="small text-decoration-none materials-print-open" href="#" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open whole print
                        </a>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted materials-print-close" aria-label="Close print preview">
                            <i class="bi bi-x-lg"></i> Close
                        </button>
                    </div>
                </div>
                {{-- One chip per ink the order draws. Picking one repaints the
                     stage with only that channel's ink, so the reviewer can see
                     where in the artwork the bottle is actually going. --}}
                <div class="materials-channel-chips d-flex flex-wrap align-items-center gap-1 mb-1" hidden></div>
                <div class="materials-print-stage border rounded-3">
                    <img class="materials-print-large" src="" alt="The print this order's ink was measured from">
                </div>
                <small class="text-muted d-block mt-1 materials-print-caption">
                    This panel's artwork on a transparent canvas, nothing of the garment. This is what the printer
                    lays down on it, and the ink figures above are measured from it.
                </small>
            </div>
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

        /* Table mode: the figures never wrap, the name column takes the rest. */
        .order-materials .materials-col-deduct { width: 150px; }
        .order-materials .materials-col-stock { width: 100px; }
        .order-materials .materials-col-remaining { width: 110px; }
        .order-materials .materials-table td.materials-qty { white-space: nowrap; }

        /* The working behind a measured ink figure, under the bottle's name.
           It is the one thing in the table that is meant to wrap. */
        .order-materials .materials-working { font-size: 0.72rem; line-height: 1.3; white-space: normal; }

        /* Checkerboard behind a print, so white artwork on a transparent
           canvas is visible rather than lost against the modal. */
        .order-materials .materials-print,
        .order-materials .materials-print-stage {
            background-color: #eee;
            background-image: linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%),
                              linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%);
            background-size: 12px 12px; background-position: 0 0, 6px 6px;
        }
        .order-materials .materials-print {
            width: 96px; height: 96px; object-fit: contain; border-radius: 6px; border: 1px solid #dee2e6;
            cursor: zoom-in; transition: border-color .15s, box-shadow .15s;
        }
        /* One panel: its crop with the panel's name under it. */
        .order-materials .materials-print-figure { margin: 0; width: 96px; text-align: center; }
        .order-materials .materials-print-figure figcaption {
            font-size: 0.68rem; line-height: 1.2; color: #6c757d; margin-top: 3px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .order-materials .materials-print:hover,
        .order-materials .materials-print.is-open { border-color: #0e2e45; box-shadow: 0 0 0 2px rgba(14, 46, 69, .15); }
        .order-materials .materials-print-preview[hidden] { display: none; }

        /* An ink bottle's row wears its channel's colour, and can jump to
           the print with only that channel showing. */
        .order-materials .materials-ink-swatch {
            display: inline-block; width: 11px; height: 11px; border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, .25); vertical-align: -1px; margin-right: 6px;
        }
        .order-materials .materials-ink-where { font-size: 0.72rem; }
        .order-materials .materials-channel-chips[hidden] { display: none; }
        .order-materials .materials-channel-chip {
            font-size: 0.72rem; line-height: 1.2; padding: 3px 9px; border-radius: 999px;
            border: 1px solid #dee2e6; background: #fff; color: #495057; cursor: pointer;
        }
        .order-materials .materials-channel-chip:hover { border-color: #adb5bd; }
        .order-materials .materials-channel-chip.is-active { border-color: #0e2e45; box-shadow: 0 0 0 2px rgba(14, 46, 69, .15); color: #0e2e45; font-weight: 600; }
        .order-materials .materials-channel-chip:disabled { opacity: .4; cursor: default; }
        .order-materials .materials-print-stage { padding: 8px; text-align: center; }
        .order-materials .materials-print-large {
            display: block; margin: 0 auto; max-width: 100%; max-height: 60vh; width: auto; height: auto;
        }

        /* Card mode, decided by the panel's own width: the staff status modal
           is narrower than the admin's review modal, and a phone is narrower
           than both. Each material becomes a block with its figures in a
           labelled grid beneath, so nothing has to scroll sideways.

           These rules are more specific than the pages' own mobile rules on
           .modal-table (nowrap, min-width), so they win where both apply. */
        .order-materials { container-type: inline-size; }

        @container (max-width: 600px) {
            .order-materials .materials-table-wrap { overflow: visible !important; }
            .order-materials .materials-table { min-width: 0; display: block; }
            .order-materials .materials-table colgroup,
            .order-materials .materials-table thead { display: none; }
            .order-materials .materials-table tbody { display: block; }
            .order-materials .materials-table tr {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 6px 10px;
                padding: 10px 12px;
                border-bottom: 1px solid #dee2e6;
            }
            /* The input plus its unit has to fit one grid cell on a phone;
               at the table width it clipped the unit to "pc". */
            .order-materials .materials-input { width: 68px; }
            .order-materials .materials-table tr:last-child { border-bottom: 0; }
            .order-materials .materials-table td {
                display: block; border: 0; padding: 0; white-space: normal; text-align: left;
            }
            .order-materials .materials-table td:first-child { grid-column: 1 / -1; }
            .order-materials .materials-table td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 0.62rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
                color: #6c757d; margin-bottom: 2px;
            }
            .order-materials .materials-table td .d-flex { justify-content: flex-start !important; }
            /* Row tints move to the row, since its cells are no longer laid out
               as one strip — including Bootstrap's hover, which otherwise paints
               each cell as a separate grey block. */
            .order-materials .materials-short td,
            .order-materials .materials-edited td { background-color: transparent; }
            .order-materials .materials-table.table-hover > tbody > tr:hover > * { --bs-table-bg-state: transparent; }
            .order-materials .materials-table.table-hover > tbody > tr:hover { background-color: rgba(0, 0, 0, 0.02); }
            .order-materials tr.materials-short { background-color: rgba(220, 53, 69, 0.06); }
            .order-materials tr.materials-edited { background-color: rgba(13, 110, 253, 0.05); }

            .order-materials .materials-adjust-hint { flex-direction: column; }
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

            const round = value => Math.round(value * 10000) / 10000;

            function recalculate() {
                const problems = [];
                let changed = 0;

                inputs.forEach(input => {
                    const row = input.closest('tr');
                    const stock = parseFloat(input.dataset.stock);
                    const wanted = parseFloat(input.value);
                    const remaining = row.querySelector('.col-remaining');
                    const unit = input.dataset.unit || '';

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
                        const name = row.querySelector('.materials-name').textContent;
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

        /**
         * Cut one panel out of a print.
         *
         * The zone is a UV rectangle on the atlas. A panel the model unwraps
         * mirrored (the bag's back) was drawn mirrored so it reads right on
         * the garment; the same flip is applied again here so the crop reads
         * right on screen. Returns a data URL, or null if the canvas can't
         * be read back (a cross-origin print), in which case the caller
         * shows the print whole instead.
         */
        function cropPrintZone(img, zone) {
            const W = img.naturalWidth, H = img.naturalHeight;
            const sx = Math.round(zone.u0 * W), sy = Math.round(zone.v0 * H);
            const sw = Math.max(1, Math.round((zone.u1 - zone.u0) * W));
            const sh = Math.max(1, Math.round((zone.v1 - zone.v0) * H));

            const canvas = document.createElement('canvas');
            canvas.width = sw;
            canvas.height = sh;
            const ctx = canvas.getContext('2d');
            ctx.translate(zone.flipU ? sw : 0, zone.flipV ? sh : 0);
            ctx.scale(zone.flipU ? -1 : 1, zone.flipV ? -1 : 1);
            ctx.drawImage(img, sx, sy, sw, sh, 0, 0, sw, sh);

            try {
                return canvas.toDataURL('image/png');
            } catch (e) {
                return null;
            }
        }

        /**
         * Repaint a print with only one ink channel.
         *
         * Every pixel is split the way the server splits it when it measures
         * the print — the plain RGB→CMYK conversion, weighted by the pixel's
         * opacity — and the chosen channel's share is painted in the
         * channel's colour, heavier where more of it goes down. Transparent
         * stays transparent: nothing printed there. What comes back is the
         * artwork the bottle is charged for, and nothing else.
         *
         * Resolves to a data URL, or null when the pixels cannot be read (a
         * cross-origin print); the caller then keeps the full-colour view.
         */
        function separateChannel(src, channel, swatch) {
            return new Promise(resolve => {
                const img = new Image();
                img.onload = () => {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);

                        const image = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const d = image.data;
                        const tint = [parseInt(swatch.slice(1, 3), 16), parseInt(swatch.slice(3, 5), 16), parseInt(swatch.slice(5, 7), 16)];

                        for (let i = 0; i < d.length; i += 4) {
                            const a = d[i + 3];
                            if (a === 0) continue;

                            const r = d[i] / 255, g = d[i + 1] / 255, b = d[i + 2] / 255;
                            const k = 1 - Math.max(r, g, b);
                            let value;
                            if (k >= 1) {
                                value = channel === 'black' ? 1 : 0;
                            } else if (channel === 'black') {
                                value = k;
                            } else {
                                const c = channel === 'cyan' ? r : channel === 'magenta' ? g : b;
                                value = (1 - c - k) / (1 - k);
                            }

                            d[i] = tint[0];
                            d[i + 1] = tint[1];
                            d[i + 2] = tint[2];
                            d[i + 3] = Math.round(a * Math.max(0, Math.min(1, value)));
                        }

                        ctx.putImageData(image, 0, 0);
                        resolve(canvas.toDataURL('image/png'));
                    } catch (e) {
                        resolve(null);
                    }
                };
                img.onerror = () => resolve(null);
                img.src = src;
            });
        }

        /**
         * Show the order's prints, one thumbnail per panel, and open any of
         * them in the panel's own stage.
         *
         * Each print is `{ url, label, zones }`. With zones, every panel is
         * cropped out of the print and labelled with its name; without them
         * (a design saved before panels were recorded) the print is shown
         * whole. Clicking the thumbnail that is already open closes it again,
         * so the thumbnails work as a toggle and the stage never has to be
         * hunted for.
         */
        function wirePrintPreview(panel, prints, inks = []) {
            const list = panel.querySelector('.materials-prints-list');
            const preview = panel.querySelector('.materials-print-preview');
            const large = panel.querySelector('.materials-print-large');
            const title = panel.querySelector('.materials-print-preview-title');
            const open = panel.querySelector('.materials-print-open');
            const close = panel.querySelector('.materials-print-close');
            const caption = panel.querySelector('.materials-print-caption');

            list.innerHTML = '';
            preview.hidden = true;

            // Which ink, if any, the stage is isolating. Kept across panels
            // so a reviewer checking magenta on the front sees magenta on
            // the back too. The full-colour chip clears it.
            let channel = null;
            const chips = panel.querySelector('.materials-channel-chips');
            chips.innerHTML = '';
            chips.hidden = !inks.length;

            const addChip = (ink) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'materials-channel-chip';
                chip.dataset.channel = ink ? ink.key : '';
                if (ink) {
                    const dot = document.createElement('span');
                    dot.className = 'materials-ink-swatch';
                    dot.style.background = ink.swatch;
                    chip.appendChild(dot);
                }
                chip.appendChild(document.createTextNode(ink ? ink.label + ' only' : 'Full colour'));
                chip.addEventListener('click', () => { channel = ink ? ink.key : null; paint(); });
                chips.appendChild(chip);
            };
            if (inks.length) {
                addChip(null);
                inks.forEach(addChip);
            }

            // Put the open view on the stage in the current channel. A
            // separation is computed once per view and channel and kept.
            const paint = () => {
                const view = views[Number(large.dataset.view)];
                if (!view || preview.hidden) return;

                chips.querySelectorAll('.materials-channel-chip').forEach(c => c.classList.toggle('is-active', (c.dataset.channel || null) === channel));

                if (channel === null) {
                    large.src = view.src;
                    caption.textContent = view.whole
                        ? 'Every panel\'s artwork on a transparent canvas, nothing of the garment. This is what the printer lays down, and the ink figures above are measured from it.'
                        : 'This panel\'s artwork on a transparent canvas, nothing of the garment. This is what the printer lays down on it, and the ink figures above are measured from it.';
                    return;
                }

                const ink = inks.find(i => i.key === channel);
                view.separations = view.separations || {};
                const ready = view.separations[channel] || (view.separations[channel] = separateChannel(view.src, channel, ink.swatch));
                const wanted = channel;
                ready.then(url => {
                    if (channel !== wanted || large.dataset.view !== String(views.indexOf(view))) return;
                    if (url === null) {
                        // The pixels could not be read, so no channel can be
                        // shown for this print; say so by greying the chips.
                        channel = null;
                        chips.querySelectorAll('.materials-channel-chip').forEach(c => { c.disabled = !!c.dataset.channel; });
                        paint();
                        return;
                    }
                    large.src = url;
                    caption.textContent = 'Only the ' + ink.label.toLowerCase() + ' the printer lays down ' + (view.whole ? 'across every panel' : 'on this panel')
                        + ', heavier where the colour is darker. Everything else is left out — this is the artwork the ' + ink.label + ' figure above is charged for.';
                });
            };

            // One entry per thumbnail: what to show large, what to call it,
            // and which whole print it came from.
            const views = [];

            const addThumb = (view) => {
                const figure = document.createElement('figure');
                figure.className = 'materials-print-figure';

                const img = document.createElement('img');
                img.src = view.src;
                img.alt = view.name + ', click to enlarge';
                img.title = 'Click to enlarge';
                img.className = 'materials-print';
                img.tabIndex = 0;
                img.setAttribute('role', 'button');

                const cap = document.createElement('figcaption');
                cap.textContent = view.caption;
                cap.title = view.name;

                figure.appendChild(img);
                figure.appendChild(cap);
                list.appendChild(figure);

                view.thumb = img;
                const index = views.push(view) - 1;
                img.addEventListener('click', () => show(index));
                img.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); show(index); }
                });
            };

            const show = (index) => {
                const view = views[index];
                const isOpen = !preview.hidden && large.dataset.view === String(index);
                views.forEach(v => v.thumb && v.thumb.classList.remove('is-open'));

                if (isOpen) {
                    preview.hidden = true;
                    return;
                }

                // A panel crop is a slice of the atlas, so it can be a couple
                // of hundred pixels tall. Let it grow to fill the stage, but
                // no more than twice its own size — past that it is blur, not
                // detail.
                large.style.height = '';
                large.onload = () => {
                    const cap = Math.min(window.innerHeight * 0.6, large.naturalHeight * 2);
                    large.style.height = Math.round(cap) + 'px';
                    large.style.width = 'auto';
                };
                large.dataset.view = String(index);
                open.href = view.printUrl;
                title.textContent = view.name;
                view.thumb.classList.add('is-open');
                preview.hidden = false;
                paint();
                preview.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            };

            const several = prints.length > 1;

            prints.forEach(print => {
                const zones = Array.isArray(print.zones) ? print.zones : [];
                const productName = print.label || 'Design';
                const wholeView = () => addThumb({
                    src: print.url, printUrl: print.url, whole: true,
                    name: several ? productName + ' — whole print' : 'Whole print',
                    caption: several ? productName : 'Whole print',
                });

                if (!zones.length) {
                    wholeView();
                    return;
                }

                // Panels need the pixels, so they wait for the print to load.
                // Until then, and if it never does, nothing is shown for it —
                // a broken image would say less than nothing.
                const img = new Image();
                img.onload = () => {
                    const crops = zones.map(zone => ({ zone, src: cropPrintZone(img, zone) }));
                    if (crops.some(c => c.src === null)) {
                        wholeView();
                        return;
                    }
                    // A panel that was measured says how big it prints, in
                    // the units the shop cuts paper by. Nothing on it, no size.
                    const sizeOf = zone => (zone.width_cm && zone.height_cm)
                        ? Math.round(zone.width_cm) + '×' + Math.round(zone.height_cm) + ' cm'
                        : null;
                    const longSizeOf = zone => (zone.width_cm && zone.height_cm)
                        ? zone.width_cm + ' × ' + zone.height_cm + ' cm (' + (zone.width_cm / 2.54).toFixed(1) + ' × ' + (zone.height_cm / 2.54).toFixed(1) + ' in)'
                        : null;

                    crops.forEach(({ zone, src }) => addThumb({
                        src, printUrl: print.url, whole: false,
                        name: (several ? productName + ' — ' : '') + zone.label + (longSizeOf(zone) ? ', prints ' + longSizeOf(zone) : ', nothing printed'),
                        caption: zone.label + (sizeOf(zone) ? ' · ' + sizeOf(zone) : ''),
                    }));
                };
                img.onerror = wholeView;
                img.src = print.url;
            });

            close.onclick = () => {
                preview.hidden = true;
                views.forEach(v => v.thumb && v.thumb.classList.remove('is-open'));
            };

            // An ink row asks for its channel on whichever view is open, or
            // the first if none is. Thumbnails arrive as their prints load,
            // so a click before then does nothing rather than half a view.
            return {
                showChannel(key) {
                    channel = key;
                    if (preview.hidden) {
                        if (views.length) show(0);
                        return;
                    }
                    paint();
                    preview.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                },
            };
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
                    panel.querySelectorAll('.col-remaining, .materials-col-remaining').forEach(el => el.classList.toggle('d-none', !showRemaining));

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

                        // data-label is what card mode prints above each figure
                        // once the header row is gone.
                        row.innerHTML = `
                            <td class="ps-3 fw-semibold text-dark"><span class="materials-name"></span></td>
                            <td class="text-center materials-qty fw-bold" data-label="To deduct"></td>
                            <td class="text-center materials-qty" data-label="In stock"></td>
                            <td class="text-end pe-3 materials-qty col-remaining${showRemaining ? '' : ' d-none'}" data-label="Remaining"></td>`;

                        const cells = row.querySelectorAll('td');
                        cells[0].querySelector('.materials-name').textContent = line.name;
                        if (line.ink) {
                            const dot = document.createElement('span');
                            dot.className = 'materials-ink-swatch';
                            dot.style.background = line.ink.swatch;
                            dot.title = line.ink.label + ' channel';
                            cells[0].insertBefore(dot, cells[0].firstChild);
                        }
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
                        // Where in the artwork this bottle goes: the print,
                        // with only this channel painted. Only where there is
                        // a print to paint.
                        if (line.ink && (data.prints || []).length) {
                            const where = document.createElement('button');
                            where.type = 'button';
                            where.className = 'btn btn-link p-0 text-decoration-none materials-ink-where';
                            where.innerHTML = '<i class="bi bi-eye me-1"></i>';
                            where.appendChild(document.createTextNode('Show where the ' + line.ink.label.toLowerCase() + ' prints'));
                            where.addEventListener('click', () => panel.printPreview && panel.printPreview.showChannel(line.ink.key));
                            cells[0].appendChild(where);
                        }
                        cells[2].textContent = line.stock + unit;

                        if (editThis) {
                            const input = document.createElement('input');
                            input.type = 'number';
                            input.step = '0.0001';
                            input.min = '0';
                            input.className = 'form-control form-control-sm text-end materials-input';
                            input.name = `material_quantities[${line.id}]`;
                            input.value = line.quantity;
                            input.dataset.calculated = line.quantity;
                            input.dataset.stock = line.stock;
                            input.dataset.unit = line.unit || '';
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

                    const warnings = data.warnings || [];
                    const warningList = panel.querySelector('.materials-warnings-list');
                    warningList.innerHTML = '';
                    panel.querySelector('.materials-warnings').classList.toggle('d-none', !warnings.length);
                    warnings.forEach(text => {
                        const li = document.createElement('li');
                        li.textContent = text;
                        warningList.appendChild(li);
                    });

                    const prints = data.prints || [];
                    panel.querySelector('.materials-prints').classList.toggle('d-none', !prints.length);
                    const inks = data.lines.filter(l => l.ink).map(l => l.ink);
                    panel.printPreview = wirePrintPreview(panel, prints, inks);

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

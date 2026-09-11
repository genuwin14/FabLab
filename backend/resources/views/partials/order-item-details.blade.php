{{-- What an order line is, beneath its row in the admin and staff detail
     modals: the size-and-colour cell it took, and for a tailored item the
     design's summary and how its price was built up. The modals render
     lines from JSON, so this is a browser-side helper; the customer's own
     drawer says the same things from Blade. --}}
<script>
    window.orderItemDetailsHtml = function (item) {
        const esc = (value) => String(value ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const money = (value) => '₱' + (Number(value) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const design = item.custom_design || item.customDesign || null;
        const variant = item.product_variant || item.productVariant || null;
        const summary = design?.summary || null;
        const variantLabel = variant?.label || (summary?.size_short ?? '');

        if (!design && !variantLabel) return '';

        const cellBadge = variantLabel
            ? `<span class="order-item-cell"><i class="bi bi-tag me-1"></i>${esc(variantLabel)}</span>`
            : '';

        // A plain stocked product only has its cell to say.
        if (!summary) {
            return `<div class="order-item-details order-item-details-plain">${cellBadge}</div>`;
        }

        const facts = [];
        if (summary.size) facts.push(['Size', esc(summary.size)]);
        if (summary.finish) facts.push(['Finish', esc(summary.finish)]);
        if (summary.text?.length) facts.push(['Text', summary.text.map(line => '&ldquo;' + esc(line) + '&rdquo;').join('<br>')]);
        if (summary.logos) facts.push(['Images', `${summary.logos} uploaded`]);
        if (summary.shapes) facts.push(['Shapes', String(summary.shapes)]);
        if (summary.led_lighting) facts.push(['Lighting', 'Internal LED']);

        const factsHtml = facts.length
            ? '<dl class="order-item-facts">' +
              facts.map(([label, value]) => `<dt>${label}</dt><dd>${value}</dd>`).join('') +
              '</dl>'
            : '<div class="text-muted fst-italic">Nothing added to the design.</div>';

        // The base is what is left of the unit price once the extras are
        // taken off, so the lines add up to what was charged even if a
        // rate has moved since.
        const breakdown = Array.isArray(design.price_breakdown) ? design.price_breakdown : [];
        const unit = Number(item.price) || 0;
        const extras = breakdown.reduce((sum, line) => sum + (Number(line.amount) || 0), 0);
        const base = unit - extras >= 0 ? unit - extras : Number(item.product?.price) || 0;

        const priceRows =
            `<tr><td>Base ${esc(item.product?.name || 'product')}</td><td>${money(base)}</td></tr>` +
            breakdown.map(line => `<tr><td>${esc(line.label)}</td><td>+ ${money(line.amount)}</td></tr>`).join('') +
            `<tr class="order-item-price-total"><td>Per item</td><td>${money(unit)}</td></tr>`;

        return `
            <div class="order-item-details">
                <div class="order-item-details-col">
                    <div class="order-item-details-heading">
                        <span><i class="bi bi-palette me-1"></i>Design</span>
                        ${cellBadge}
                    </div>
                    ${factsHtml}
                </div>
                <div class="order-item-details-col">
                    <div class="order-item-details-heading">
                        <span><i class="bi bi-receipt me-1"></i>Price per item</span>
                    </div>
                    <table class="order-item-price"><tbody>${priceRows}</tbody></table>
                </div>
            </div>`;
    };

    // The details stay folded under the row until asked for; the toggle
    // sits on the product cell and flips the row that carries its id.
    window.orderItemToggleHtml = function (rowId) {
        return `<button type="button" class="order-item-toggle" data-target="${rowId}" aria-expanded="false">` +
               `<i class="bi bi-chevron-down"></i><span>Details</span></button>`;
    };

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.order-item-toggle');
        if (!toggle) return;

        const row = document.getElementById(toggle.dataset.target);
        if (!row) return;

        const open = row.classList.toggle('d-none') === false;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.querySelector('span').textContent = open ? 'Hide details' : 'Details';
    });
</script>

<style>
    /* The details row hangs off the item row above it: no hover tint of its
       own, and its panel is indented to the product name, past the thumbnail. */
    .order-item-details-row > td { padding-top: 0 !important; }
    .table-hover > tbody > tr.order-item-details-row:hover > * { --bs-table-bg-state: transparent; }

    .order-item-details {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px 32px;
        margin: 0;
        padding: 4px 0 8px 52px;
        font-size: 0.78rem;
        line-height: 1.4;
    }
    .order-item-details-plain {
        display: block;
        padding: 0 0 6px 52px;
    }

    .order-item-toggle {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        padding: 1px 8px 1px 6px;
        border: 1px solid rgba(14, 46, 69, 0.18);
        border-radius: 999px;
        background-color: #fff;
        color: #0e2e45;
        font-size: 0.66rem;
        font-weight: 600;
        line-height: 1.5;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .order-item-toggle:hover { background-color: rgba(255, 197, 8, 0.15); border-color: rgba(255, 197, 8, 0.6); }
    .order-item-toggle .bi { font-size: 0.6rem; transition: transform 0.15s ease; }
    .order-item-toggle[aria-expanded="true"] .bi { transform: rotate(180deg); }
    .order-item-details-col { min-width: 0; }

    .order-item-details-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.07);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6c757d;
    }

    .order-item-cell {
        display: inline-flex;
        align-items: center;
        padding: 2px 9px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 999px;
        background-color: #fff;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
        color: #212529;
    }

    .order-item-facts {
        display: grid;
        grid-template-columns: 4.25rem minmax(0, 1fr);
        column-gap: 10px;
        row-gap: 4px;
        margin: 0;
    }
    .order-item-facts dt { font-weight: 400; color: #6c757d; }
    .order-item-facts dd { margin: 0; font-weight: 500; color: #212529; overflow-wrap: anywhere; }

    .order-item-price { width: 100%; border-collapse: collapse; }
    .order-item-price td { padding: 2px 0; vertical-align: top; }
    .order-item-price td:first-child { color: #6c757d; padding-right: 12px; }
    .order-item-price td:last-child { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .order-item-price .order-item-price-total td {
        padding-top: 6px;
        margin-top: 4px;
        border-top: 1px solid rgba(0, 0, 0, 0.12);
        font-weight: 700;
        color: #212529;
    }

    @media (max-width: 767.98px) {
        .order-item-details, .order-item-details-plain { grid-template-columns: 1fr; padding-left: 0; }
    }
</style>

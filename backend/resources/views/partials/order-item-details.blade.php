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

        const parts = [];

        if (variantLabel) {
            parts.push(`<span class="badge bg-light text-dark border rounded-pill fw-semibold me-2" style="font-size: 0.65rem;">${esc(variantLabel)}</span>`);
        }

        if (summary) {
            const facts = [];
            if (summary.size) facts.push(['Size', esc(summary.size)]);
            if (summary.finish) facts.push(['Finish', esc(summary.finish)]);
            if (summary.text?.length) facts.push(['Text', summary.text.map(line => '&ldquo;' + esc(line) + '&rdquo;').join('<br>')]);
            if (summary.logos) facts.push(['Images', `${summary.logos} uploaded`]);
            if (summary.shapes) facts.push(['Shapes', String(summary.shapes)]);
            if (summary.led_lighting) facts.push(['Lighting', 'Internal LED']);

            if (facts.length) {
                parts.push(
                    '<div class="order-item-facts">' +
                    facts.map(([label, value]) => `<span class="text-muted">${label}</span><span class="text-dark fw-medium">${value}</span>`).join('') +
                    '</div>'
                );
            }

            // The base is what is left of the unit price once the extras are
            // taken off, so the lines add up to what was charged even if a
            // rate has moved since.
            const breakdown = Array.isArray(design.price_breakdown) ? design.price_breakdown : [];
            const unit = Number(item.price) || 0;
            const extras = breakdown.reduce((sum, line) => sum + (Number(line.amount) || 0), 0);
            const base = unit - extras >= 0 ? unit - extras : Number(item.product?.price) || 0;

            parts.push(
                '<div class="order-item-price">' +
                `<div class="d-flex justify-content-between"><span class="text-muted">Base ${esc(item.product?.name || 'product')}</span><span>${money(base)}</span></div>` +
                breakdown.map(line => `<div class="d-flex justify-content-between"><span class="text-muted">${esc(line.label)}</span><span>+ ${money(line.amount)}</span></div>`).join('') +
                `<div class="d-flex justify-content-between fw-bold text-dark border-top pt-1 mt-1"><span>Per item</span><span>${money(unit)}</span></div>` +
                '</div>'
            );
        }

        return `<div class="order-item-details">${parts.join('')}</div>`;
    };
</script>

<style>
    .order-item-details {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.75rem;
    }
    .order-item-details .order-item-facts {
        display: grid;
        grid-template-columns: 4.5rem minmax(0, 1fr);
        row-gap: 2px;
        column-gap: 8px;
        align-items: start;
        margin-top: 6px;
    }
    .order-item-details .order-item-price {
        border-top: 1px dashed rgba(0, 0, 0, 0.1);
        margin-top: 8px;
        padding-top: 6px;
        max-width: 28rem;
    }
    .order-item-details .order-item-price > div + div { margin-top: 2px; }
</style>

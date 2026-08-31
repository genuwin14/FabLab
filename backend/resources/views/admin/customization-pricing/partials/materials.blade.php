{{--
    The bill of materials for one customization option.

    What the option charges is set beside this; this is what it costs the shop
    to make. Leave it empty and the option draws nothing, which is how every
    option behaved before materials existed.

    Rows repopulate from old() after a failed save so an admin doesn't lose a
    half-built list to a typo in a price field three panels away.

    Expects: $key, $rate, $materials
--}}
@php
    $savedRows = collect($rate['materials'] ?? [])
        ->map(fn ($quantity, $materialId) => ['raw_material_id' => $materialId, 'quantity' => $quantity])
        ->values()
        ->all();

    $rows = old('materials.' . $key, $savedRows);

    // "each at 1× size" is the image rate, whose draw scales with the printed
    // size the same way its fee does. Everything else is a flat per-one figure.
    $per = $rate['suffix'] === 'each at 1× size' ? 'per image at 1× size' : 'per one';
@endphp

<div class="rate-materials mt-1" data-rate-key="{{ $key }}">
    <div class="d-flex justify-content-between align-items-center gap-2">
        <small class="text-muted">
            <i class="bi bi-box-seam me-1"></i>Materials used
            <span class="text-muted-2">— {{ $per }}</span>
        </small>
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none add-material-btn"
            @disabled($materials->isEmpty())>
            <i class="bi bi-plus-lg"></i> Add material
        </button>
    </div>

    <div class="material-rows mt-2">
        @foreach ($rows as $index => $row)
            <div class="material-row d-flex align-items-center gap-2 mb-2">
                <select class="form-select form-select-sm"
                    name="materials[{{ $key }}][{{ $index }}][raw_material_id]"
                    aria-label="Material for {{ $rate['label'] }}">
                    <option value="">Choose a material…</option>
                    @foreach ($materials as $material)
                        <option value="{{ $material->raw_material_id }}"
                            @selected((int) ($row['raw_material_id'] ?? 0) === $material->raw_material_id)>
                            {{ $material->name }} ({{ $material->unit }})
                        </option>
                    @endforeach
                </select>
                <input type="number" step="0.0001" min="0.0001" max="99999999.9999"
                    class="form-control form-control-sm text-end material-qty"
                    name="materials[{{ $key }}][{{ $index }}][quantity]"
                    value="{{ $row['quantity'] ?? '' }}"
                    placeholder="Qty"
                    aria-label="Quantity of material for {{ $rate['label'] }}">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-material-btn"
                    aria-label="Remove this material">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endforeach
    </div>

    {{-- Shown only while the list is empty, so a blank option reads as a
         deliberate "draws nothing" rather than a broken panel.

         Deliberately not d-block: Bootstrap's display utilities are !important
         and beat the hidden attribute, which left "draws nothing" showing
         underneath a list of materials. .material-empty carries the display
         rule instead. --}}
    <small class="text-muted-2 material-empty" @if (count($rows)) hidden @endif>
        @if ($materials->isEmpty())
            No raw materials on file yet — add some under Raw Materials first.
        @else
            Draws nothing. Approved orders won't deduct anything for this.
        @endif
    </small>

    {{-- The blank row Add material clones. It lives in a <template> rather than
         as a hidden row because template contents are inert: a hidden row would
         still post its empty fields, and an option that starts with no
         materials would have nothing to clone from at all. --}}
    @unless ($materials->isEmpty())
        <template class="material-row-template">
            <div class="material-row d-flex align-items-center gap-2 mb-2">
                <select class="form-select form-select-sm" aria-label="Material for {{ $rate['label'] }}">
                    <option value="">Choose a material…</option>
                    @foreach ($materials as $material)
                        <option value="{{ $material->raw_material_id }}">{{ $material->name }} ({{ $material->unit }})</option>
                    @endforeach
                </select>
                <input type="number" step="0.0001" min="0.0001" max="99999999.9999"
                    class="form-control form-control-sm text-end material-qty" placeholder="Qty"
                    aria-label="Quantity of material for {{ $rate['label'] }}">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-material-btn"
                    aria-label="Remove this material">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </template>
    @endunless
</div>

{{--
    The "what does this finish cost us?" pair, shared by the colour and texture
    modals on both the admin and staff screens.

    A finish used to be priced and never costed. A colour in particular had no
    stock of its own to draw on, so a red shirt collected the colour's surcharge
    and no ink ever left the shelf. Linking a raw material here is what closes
    that: approving an order now deducts this quantity per item finished in it.

    Leaving the material unset keeps the old behaviour, which is why nothing
    here is required.

    Expects:
      $prefix     unique id prefix, e.g. 'addColor' — the modals are all on one
                  page, so ids can't be shared between them
      $materials  the pickable raw materials
      $finish     the model being edited, or null when adding
      $noun       'colour' or 'texture', for the hint line
      $inputClass optional — the host modal's field class, since the colour and
                  texture screens style their inputs differently
--}}
@php
    $inputClass ??= 'color-field-input';

    $selectedId = old('raw_material_id', $finish?->raw_material_id);
    $quantity = old('material_quantity', $finish && $finish->material_quantity > 0
        ? rtrim(rtrim(number_format($finish->material_quantity, 4, '.', ''), '0'), '.')
        : '');
@endphp

<div class="col-12">
    <label class="form-label small fw-bold text-muted text-uppercase" for="{{ $prefix }}Material">
        Material used <span class="text-muted fw-normal text-lowercase">(optional)</span>
    </label>
    <div class="row g-2">
        <div class="col-8">
            <select name="raw_material_id" id="{{ $prefix }}Material" class="form-select {{ $inputClass }}">
                <option value="">None — draws no material</option>
                @foreach ($materials as $material)
                    <option value="{{ $material->raw_material_id }}"
                        @selected((int) $selectedId === $material->raw_material_id)>
                        {{ $material->name }} ({{ $material->unit }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-4">
            <input type="number" step="0.0001" min="0" max="99999999.9999"
                name="material_quantity" id="{{ $prefix }}MaterialQty"
                class="form-control {{ $inputClass }} text-end"
                placeholder="Qty per item" value="{{ $quantity }}"
                aria-label="Quantity of material per item">
        </div>
    </div>
    <small class="text-muted">
        Deducted per item finished in this {{ $noun }} when an order is approved.
        @if ($noun === 'texture')
            Drawn on top of the texture's own stock — the texture is the printed sheet, this is what printed it.
        @endif
        Leave the material blank and this {{ $noun }} deducts nothing.
    </small>
</div>

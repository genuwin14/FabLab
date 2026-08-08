{{--
    Unit dropdown, shared by every form that sets one: raw materials, textures
    and products, across both roles.

    Caller passes:
      $id        element id (required — the edit modals address it from JS)
      $selected  current value, for server-rendered forms
      $class     form-control class of the host modal's theme
      $required  false where the column is nullable (textures)
      $hint      false to drop the explanatory line in tighter layouts

    Grouped by what is being measured, with a typical material beside each unit
    so the right one is obvious — ink is grams, lanyard strap is metres.
--}}
@php
    use App\Enums\MaterialUnit;

    $selected ??= null;
    $class ??= 'material-field-input';
    $required ??= true;
    $hint ??= true;
@endphp

<label class="form-label small fw-bold text-muted text-uppercase" for="{{ $id }}">
    Unit @if($required)<span class="text-danger">*</span>@endif
</label>
<select id="{{ $id }}" name="unit" class="form-select {{ $class }} unit-select" {{ $required ? 'required' : '' }}>
    <option value="" {{ $required ? 'disabled' : '' }} {{ $selected ? '' : 'selected' }}>
        {{ $required ? 'Select a unit…' : '— None —' }}
    </option>
    @foreach(MaterialUnit::grouped() as $group => $units)
        <optgroup label="{{ $group }}">
            @foreach($units as $unit)
                <option value="{{ $unit->value }}" {{ $selected === $unit->value ? 'selected' : '' }}>
                    {{ $unit->value }} — {{ $unit->example() }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
@if($hint)
    <p class="text-muted mb-0 mt-2 d-flex align-items-start gap-2" style="font-size: 0.72rem;">
        <i class="bi bi-rulers mt-1 flex-shrink-0"></i>
        <span>Pick how this is counted. Stock and bills of materials are measured in
            this unit, so it cannot be mixed — ink in grams, strap in metres, clips in pcs.</span>
    </p>
@endif

<script>
    // Selecting a legacy unit that predates this list. Rather than silently
    // resetting the field — which would rewrite a value the user never touched —
    // the stored value is added to the dropdown and marked as such.
    if (!window.setMaterialUnit) {
        window.setMaterialUnit = function (select, value) {
            if (!select) return;

            if (!value) {
                select.selectedIndex = 0;
                return;
            }

            var known = Array.prototype.some.call(select.options, function (o) {
                return o.value === value;
            });

            if (!known) {
                var legacy = document.createElement('option');
                legacy.value = value;
                legacy.textContent = value + ' — not in the standard list';
                legacy.dataset.legacy = 'true';
                select.appendChild(legacy);
            }

            select.value = value;
        };
    }
</script>

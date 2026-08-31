{{--
    What one unit of a customization option takes off the shelf, read-only.

    The staff copy of this screen exists so the bench can answer "what will
    this design cost us?" without asking an admin. Options with nothing mapped
    are shown as drawing nothing rather than hidden, because "we don't deduct
    anything for lighting" is itself worth knowing at the bench.

    Expects: $key, $rate, $materials (keyed lookup built by the parent)
--}}
@php
    $lines = collect($rate['materials'] ?? [])
        ->map(fn ($quantity, $materialId) => [
            'material' => $materials[$materialId] ?? null,
            'quantity' => $quantity,
        ])
        ->filter(fn ($line) => $line['material'] !== null);

    $trim = fn ($value) => rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
@endphp

<div class="rate-materials mt-2">
    <small class="text-muted d-block">
        <i class="bi bi-box-seam me-1"></i>Materials used
    </small>

    @if ($lines->isEmpty())
        <small class="text-muted-2 d-block mt-1">Draws nothing.</small>
    @else
        <ul class="list-unstyled mb-0 mt-1 material-list">
            @foreach ($lines as $line)
                <li class="d-flex justify-content-between gap-2">
                    <span class="text-dark">{{ $line['material']->name }}</span>
                    <span class="text-muted fw-semibold text-nowrap">
                        {{ $trim($line['quantity']) }} {{ $line['material']->unit }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

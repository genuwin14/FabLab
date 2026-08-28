{{--
    Whether a product opens in the 3D studio, for the admin and staff product
    tables. The flag is what makes the customizer reachable at all — assigning
    textures or colours only narrows what it offers — so it is worth reading off
    the row rather than opening the edit modal.

    Caller passes:
      $product  the row's product

    The colours are written out rather than built from bg-primary / bg-secondary
    + bg-opacity-10 like the badges beside them. `--bs-secondary` is the gold
    accent here, so an off state built that way came out looking like a warning
    rather than a neutral "no". (The layout used to flatten `.bg-primary` with a
    hex `!important` too, which outranked the opacity variable and painted this
    badge navy-on-navy; it goes through rgba now, but the gold still stands.)
--}}
@if($product->is_customizable)
    <span class="badge rounded-pill fw-normal d-inline-flex align-items-center gap-1"
        style="background-color: rgba(14, 46, 69, 0.08); color: #0e2e45; border: 1px solid rgba(14, 46, 69, 0.2);">
        {{-- Not the palette or layers icon: those are the row's Manage Colors /
             Manage Textures buttons, and reusing one here reads as a third
             action rather than a state. --}}
        <i class="bi bi-magic" style="font-size: 0.7rem;"></i> Customizable
    </span>
@else
    <span class="badge rounded-pill fw-normal d-inline-flex align-items-center gap-1"
        style="background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border: 1px solid rgba(108, 117, 125, 0.25);">
        <i class="bi bi-slash-circle" style="font-size: 0.7rem;"></i> Not customizable
    </span>
@endif

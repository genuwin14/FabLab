<!-- Quick View Modal -->
<style>
    /* The size and colour picker for a product stocked per cell. */
    #quickViewModal .qv-size {
        min-width: 44px; padding: 6px 10px; border: 2px solid #dee2e6; border-radius: 10px; background: #fff;
        font-weight: 700; font-size: 0.8rem; color: #0e2e45; cursor: pointer;
    }
    #quickViewModal .qv-size.active { border-color: #0e2e45; background: #0e2e45; color: #fff; }
    #quickViewModal .qv-size.is-out, #quickViewModal .qv-colour.is-out { opacity: .35; cursor: not-allowed; text-decoration: line-through; }
    #quickViewModal .qv-colour {
        width: 34px; height: 34px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px #dee2e6; cursor: pointer;
    }
    #quickViewModal .qv-colour.active { box-shadow: 0 0 0 2px #0e2e45; }

    /* The rail used to be as tall as the details column beside it, which left
       the image floating in a lot of empty grey. The product's facts fill the
       rest of it now.

       Below md this is plain stacked blocks in source order — image, details,
       facts. From md up it is a grid, because the facts have to sit under the
       IMAGE on a wide dialog and under the DETAILS on a phone, and no single
       source order gives you both without grid areas. Bootstrap's own row/col
       classes are deliberately not used here: their percentage widths fight
       the grid tracks. */
    @media (min-width: 768px) {
        #quickViewModal .qv-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr auto;
            grid-template-areas:
                "image details"
                "facts details";
        }
        #quickViewModal .qv-cell-image { grid-area: image; min-height: 0; }
        #quickViewModal .qv-cell-facts { grid-area: facts; }
        #quickViewModal .qv-cell-details { grid-area: details; }
    }
</style>
<div class="modal fade qv-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true"
    style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="qv-split">
                    <!-- Product Image -->
                    <div class="qv-rail qv-cell-image d-flex align-items-center justify-content-center p-4">
                        <img id="qv-image" src="" class="img-fluid rounded-3 shadow-sm" alt="Product Image"
                            style="max-height: 340px; width: 100%; object-fit: contain;">
                    </div>

                    <!-- Product Details -->
                    <div class="qv-cell-details p-4 p-lg-5 d-flex flex-column">
                        <div class="mb-4">
                            <span id="qv-category" class="qv-eyebrow"></span>
                            <h3 id="qv-name" class="qv-title mb-1 mt-2"></h3>
                            <div id="qv-sku" class="small text-muted mb-3"></div>
                            <h2 id="qv-price" class="fw-bold text-primary mb-0"></h2>
                        </div>

                        <div class="mb-4 flex-grow-1">
                            <h6 class="qv-label">Description</h6>
                            <p id="qv-description" class="text-muted small mb-0" style="line-height: 1.6;"></p>
                        </div>

                        {{-- Size and colour, for a product stocked per cell. Filled by
                             the shop script from the card's variants; hidden otherwise.
                             The availability row in the rail follows the picked cell. --}}
                        <div id="qv-variants" class="mb-4" hidden>
                            <div id="qv-sizes-block" class="mb-3" hidden>
                                <h6 class="qv-label">Size</h6>
                                <div id="qv-sizes" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div id="qv-colours-block" hidden>
                                <h6 class="qv-label">Colour</h6>
                                <div id="qv-colours" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="mt-auto qv-actions">
                            <button id="qv-add-to-cart-btn" class="btn btn-primary btn-lg shadow-sm">
                                <i class="bi bi-cart-plus me-2"></i> Add to Cart
                            </button>
                            <div id="qv-pick-hint" class="small text-muted text-center" hidden>Pick a size and colour to add this to your cart.</div>
                            <button class="btn btn-light py-2 small" data-bs-dismiss="modal">
                                Continue Shopping
                            </button>
                        </div>
                    </div>

                    {{-- The facts that fill the rail under the image on a wide
                         dialog, and read as a spec list under the details on a
                         phone. Same markup either way — see .qv-split above. --}}
                    <div class="qv-rail qv-cell-facts px-4 pb-4 pt-0">
                        <h6 class="qv-label">Product Details</h6>
                        <div class="qv-meta-row">
                            <span class="qv-meta-key">Availability</span>
                            <span id="qv-stock-status" class="qv-meta-val"></span>
                        </div>
                        <div class="qv-meta-row">
                            <span class="qv-meta-key">Brand</span>
                            <span id="qv-brand" class="qv-meta-val"></span>
                        </div>
                        <div class="qv-meta-row">
                            <span class="qv-meta-key">Category</span>
                            <span id="qv-meta-category" class="qv-meta-val"></span>
                        </div>
                        <div class="qv-meta-row">
                            <span class="qv-meta-key">Sold per</span>
                            <span id="qv-meta-unit" class="qv-meta-val"></span>
                        </div>
                        {{-- Only for a product the customiser accepts, so the row
                             is never a promise the shop cannot keep. --}}
                        <div id="qv-customizable" class="pt-3" hidden>
                            <span class="badge bg-accent text-primary rounded-pill px-3 py-2 small fw-bold">
                                <i class="bi bi-magic me-1"></i> Customizable
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

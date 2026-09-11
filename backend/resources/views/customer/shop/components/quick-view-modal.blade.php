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
</style>
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true"
    style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <div class="row g-0">
                    <!-- Product Image -->
                    <div class="col-md-6 bg-light">
                        <div class="h-100 d-flex align-items-center justify-content-center p-4">
                            <img id="qv-image" src="" class="img-fluid rounded-3 shadow-sm" alt="Product Image"
                                style="max-height: 400px; width: 100%; object-fit: cover;">
                        </div>
                    </div>
                    <!-- Product Details -->
                    <div class="col-md-6 p-4 p-lg-5 d-flex flex-column">
                        <div class="mb-4">
                            <span id="qv-category"
                                class="badge bg-light text-primary rounded-pill px-3 py-2 mb-2 small fw-bold"></span>
                            <h3 id="qv-name" class="fw-bold text-dark mb-1"></h3>
                            <div id="qv-sku" class="small text-muted mb-3"></div>
                            <h2 id="qv-price" class="fw-bold text-primary mb-0"></h2>
                        </div>

                        <div class="mb-4 flex-grow-1">
                            <h6 class="fw-bold text-dark small text-uppercase tracking-wider opacity-75 mb-2">
                                Description</h6>
                            <p id="qv-description" class="text-muted small mb-0" style="line-height: 1.6;"></p>
                        </div>

                        {{-- Size and colour, for a product stocked per cell. Filled by
                             the shop script from the card's variants; hidden otherwise.
                             The stock status below follows the picked cell. --}}
                        <div id="qv-variants" class="mb-4" hidden>
                            <div id="qv-sizes-block" class="mb-3" hidden>
                                <h6 class="fw-bold text-dark small text-uppercase tracking-wider opacity-75 mb-2">Size</h6>
                                <div id="qv-sizes" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div id="qv-colours-block" hidden>
                                <h6 class="fw-bold text-dark small text-uppercase tracking-wider opacity-75 mb-2">Colour</h6>
                                <div id="qv-colours" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light text-center">
                                    <div class="small text-muted mb-1">Stock Status</div>
                                    <div id="qv-stock-status" class="small fw-bold"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light text-center">
                                    <div class="small text-muted mb-1">Brand</div>
                                    <div id="qv-brand" class="small fw-bold"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <button id="qv-add-to-cart-btn"
                                class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3">
                                <i class="bi bi-cart-plus me-2"></i> Add to Cart
                            </button>
                            <div id="qv-pick-hint" class="small text-muted text-center" hidden>Pick a size and colour to add this to your cart.</div>
                            <button class="btn btn-light rounded-pill fw-bold py-2 small" data-bs-dismiss="modal">
                                Continue Shopping
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
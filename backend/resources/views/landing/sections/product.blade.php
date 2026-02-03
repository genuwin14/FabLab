<style>
    #product {
        background: radial-gradient(circle at top left, #0e2e45 0%, #05111a 60%);
        position: relative;
    }

    .feature-icon-box {
        width: 60px;
        height: 60px;
        background: rgba(255, 197, 8, 0.1);
        border: 1px solid rgba(255, 197, 8, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #ffc508;
        transition: all 0.3s ease;
    }

    .feature-item:hover .feature-icon-box {
        background: #ffc508;
        color: #05111a;
        box-shadow: 0 0 20px rgba(255, 197, 8, 0.4);
        transform: scale(1.1);
    }

    .glass-card-mockup {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
</style>

<section id="product" class="py-5 min-vh-100 d-flex align-items-center position-relative overflow-hidden">
    <!-- Top Fade for Seamless Transition -->
    <div class="position-absolute top-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to top, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>

    <!-- Ambient Background -->
    <div class="position-absolute top-50 start-0 translate-middle rounded-circle"
        style="width: 600px; height: 600px; background: rgba(14, 46, 69, 0.4); filter: blur(100px); z-index: 0;">
    </div>

    <div class="container py-5 position-relative z-1">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span
                    class="badge bg-white bg-opacity-10 backdrop-blur text-white px-3 py-2 rounded-pill border border-white border-opacity-25 animate-fade-up">
                    <i class="bi text-accent bi-layers-fill me-2"></i> Product Modules
                </span>

                <h2 class="display-4 fw-bold mt-2 text-white mb-4">
                    Comprehensive <br>
                    <span class="text-gradient-gold">Inventory Solutions</span>
                </h2>

                <p class="text-white-50 lead mb-5" style="font-weight: 300;">
                    Designed to handle every aspect of your stock management lifecycle, from procurement to
                    distribution.
                </p>

                <div class="d-flex flex-column gap-4 mt-4">
                    <!-- Feature 1 -->
                    <div class="d-flex gap-4 feature-item align-items-start">
                        <div class="flex-shrink-0">
                            <div class="feature-icon-box">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white">Smart Stock Management</h5>
                            <p class="text-white-50 mb-0">Automated tracking of stock levels with batch and expiry
                                management.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="d-flex gap-4 feature-item align-items-start">
                        <div class="flex-shrink-0">
                            <div class="feature-icon-box">
                                <i class="bi bi-clipboard-data fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white">Usage Monitoring</h5>
                            <p class="text-white-50 mb-0">Track equipment usage and material consumption in real-time
                                for accurate inventory control.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="d-flex gap-4 feature-item align-items-start">
                        <div class="flex-shrink-0">
                            <div class="feature-icon-box">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white">Predictive Analytics</h5>
                            <p class="text-white-50 mb-0">Forecast demand and optimize reorder points using historical
                                data analysis.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative ps-lg-5">
                    <!-- Decorative blurred blob -->
                    <!-- <div class="position-absolute top-50 start-50 translate-middle rounded-circle opacity-20"
                        style="width: 400px; height: 400px; background: #ffc508; filter: blur(300px); z-index: -1;">
                    </div> -->

                    <div class="card glass-card-mockup rounded-4 overflow-hidden border-0">
                        <div
                            class="card-header border-bottom border-white border-opacity-10 bg-white bg-opacity-5 p-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-danger p-1"></span>
                                <span class="d-inline-block rounded-circle bg-warning p-1"></span>
                                <span class="d-inline-block rounded-circle bg-success p-1"></span>
                            </div>
                            <div class="text-white-50 small font-monospace">inventory_dashboard.exe</div>
                        </div>

                        <div class="card-body p-0">
                            <!-- Mock Product UI -->
                            <div
                                class="p-4 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center bg-transparent">
                                <span class="fw-bold text-accent"><i class="bi bi-grid-fill me-2"></i>Stock List</span>
                                <!-- <div class="d-flex gap-2">
                                    <div
                                        class="btn btn-sm btn-outline-light rounded-pill px-3 border-opacity-25 text-white-50">
                                        <i class="bi bi-filter"></i> Filter
                                    </div>
                                    <div class="btn btn-sm btn-accent rounded-pill px-3 fw-bold">
                                        <i class="bi bi-plus-lg"></i> Add Item
                                    </div>
                                </div> -->
                            </div>

                            <div class="p-4">
                                <!-- List Item 1 -->
                                <div class="rounded-3 p-3 mb-3 d-flex align-items-center justify-content-between border border-white border-opacity-10 hover-bg-light-5 transition-all"
                                    style="background-color: rgba(0,0,0,0.2);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white bg-opacity-10 rounded p-2 text-white">
                                            <i class="bi bi-tag-fill text-accent"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">Cotton T-Shirt (Black)</div>
                                            <div class="small text-white-50">SKU-SHIRT-BLK</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div
                                            class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                            In Stock</div>
                                        <div class="small text-white-50 mt-1">150 Units</div>
                                    </div>
                                </div>

                                <!-- List Item 2 -->
                                <div class="rounded-3 p-3 mb-3 d-flex align-items-center justify-content-between border border-white border-opacity-10 hover-bg-light-5 transition-all"
                                    style="background-color: rgba(0,0,0,0.2);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white bg-opacity-10 rounded p-2 text-white">
                                            <i class="bi bi-bag-fill text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">Canvas Tote Bag</div>
                                            <div class="small text-white-50">SKU-BAG-001</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div
                                            class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25">
                                            Low Stock</div>
                                        <div class="small text-white-50 mt-1">15 Units</div>
                                    </div>
                                </div>

                                <!-- List Item 3 -->
                                <div class="rounded-3 p-3 d-flex align-items-center justify-content-between border border-white border-opacity-10 hover-bg-light-5 transition-all"
                                    style="background-color: rgba(0,0,0,0.2);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white bg-opacity-10 rounded p-2 text-white">
                                            <i class="bi bi-cup-hot-fill text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">Insulated Tumbler</div>
                                            <div class="small text-white-50">SKU-TUMB-500</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div
                                            class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                            In Stock</div>
                                        <div class="small text-white-50 mt-1">85 Units</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom Fade for Seamless Transition -->
    <div class="position-absolute bottom-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to bottom, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>
</section>
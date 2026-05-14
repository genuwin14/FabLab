<style>
    #about {
        background: radial-gradient(circle at center, #0e2e45 0%, #05111a 60%);
        position: relative;
    }

    .about-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .about-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-10px);
        border-color: rgba(255, 197, 8, 0.3);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }

    .icon-wrapper-about {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(255, 197, 8, 0.2) 0%, rgba(255, 197, 8, 0.05) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        position: relative;
        transition: all 0.3s ease;
    }

    .icon-wrapper-about::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 1px solid rgba(255, 197, 8, 0.3);
        top: 0;
        left: 0;
        transition: all 0.3s ease;
    }

    .about-card:hover .icon-wrapper-about {
        transform: scale(1.1);
        background: rgba(255, 197, 8, 0.2);
    }

    .about-card:hover .icon-wrapper-about i {
        color: #ffc508;
    }
</style>

<section id="about" class="py-5 min-vh-100 d-flex align-items-center position-relative overflow-hidden">
    <!-- Top Fade for Seamless Transition -->
    <div class="position-absolute top-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to top, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>

    <!-- Ambient Background Elements -->
    <div class="position-absolute bottom-0 end-0 translate-middle-x rounded-circle"
        style="width: 800px; height: 800px; background: radial-gradient(circle, rgba(14, 46, 69, 0.3) 0%, transparent 70%); filter: blur(80px); z-index: 0;">
    </div>

    <div class="container py-5 position-relative z-1">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="badge bg-opacity-10 bg-white text-white border border-white border-opacity-10 px-3 py-2 rounded-pill mb-3 uppercase tracking-wider">
                    <i class="bi text-accent bi-info-circle-fill me-2"></i> About Us
                </span>
                <h2 class="display-4 fw-bold mt-2 text-white mb-3">Why Choose <span class="text-white">FAB</span><span
                        class="text-gradient-gold">LAB</span>?</h2>
                <p class="text-white-50 lead mx-auto" style="max-width: 600px; font-weight: 300;">
                    We're committed to delivering exceptional quality and innovation in every product we create.
                </p>
            </div>
        </div>

        <div class="row g-4 text-center">
            <!-- Card 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 h-100 about-card d-flex flex-column align-items-center">
                    <div class="icon-wrapper-about">
                        <i class="bi bi-award-fill fs-2 text-white transition-all"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-3">Premium Quality</h5>
                    <p class="text-white-50 small mb-0 lh-lg">Every product undergoes rigorous testing to ensure it
                        meets our
                        high standards for durability and performance.</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 h-100 about-card d-flex flex-column align-items-center">
                    <div class="icon-wrapper-about">
                        <i class="bi bi-rocket-takeoff-fill fs-2 text-white transition-all"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-3">Innovation First</h5>
                    <p class="text-white-50 small mb-0 lh-lg">We stay ahead of the curve, incorporating the latest
                        technologies and design principles into our products.</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 h-100 about-card d-flex flex-column align-items-center">
                    <div class="icon-wrapper-about">
                        <i class="bi bi-headset fs-2 text-white transition-all"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-3">Expert Support</h5>
                    <p class="text-white-50 small mb-0 lh-lg">Our dedicated support team is always ready to help you get
                        the
                        most out of your FABLAB products.</p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 h-100 about-card d-flex flex-column align-items-center">
                    <div class="icon-wrapper-about">
                        <i class="bi bi-truck fs-2 text-white transition-all"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-3">Fast Delivery</h5>
                    <p class="text-white-50 small mb-0 lh-lg">Quick and reliable shipping ensures you get your products
                        when
                        you need them, anywhere in the world.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom Fade for Seamless Transition -->
    <div class="position-absolute bottom-0 start-0 w-100"
        style="height: 150px; background: linear-gradient(to bottom, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>
</section>
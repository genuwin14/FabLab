<section id="home" class="pt-5 pb-5 bg-transparent min-vh-100 d-flex align-items-center">
    <div class="container pt-5">
        <div class="row align-items-center h-100">
            <!-- Left Panel: Content -->
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <h1 class="display-3 fw-bold mb-4 text-white">
                    Innovation Meets <br>
                    <span class="text-accent">Excellence</span>
                </h1>

                <p class="lead text-white-50 mb-5">
                    Discover cutting-edge products designed to transform your world. From revolutionary tech solutions
                    to premium fabricated goods, we bring tomorrow's innovations to today's market.
                </p>

                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('register') }}"
                        class="btn btn-accent btn-lg rounded-pill px-5 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-bag-fill"></i>
                        Shop Now
                    </a>
                    <a href="#demo"
                        class="btn btn-outline-light btn-lg rounded-pill px-5 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-play-circle"></i>
                        Watch Demo
                    </a>
                </div>
            </div>

            <!-- Right Panel: Logo -->
            <div class="col-lg-6 text-center">
                <div class="position-relative d-inline-block">
                    <!-- Optional decoration/blob behind logo could go here if desired -->
                    <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary bg-opacity-10 rounded-circle filter blur-3xl opacity-50"
                        style="filter: blur(40px); z-index: -1;"></div>
                    <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FabLab Logo" class="img-fluid position-relative"
                        style="max-height:600px; width: auto;">
                </div>
            </div>
        </div>
    </div>
</section>
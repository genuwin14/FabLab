<style>
    /* Hero Section Specific Styles */
    #home {
        background: radial-gradient(circle at top right, #0e2e45 0%, #05111a 60%);
        position: relative;
    }

    /* Ambient Background Glow */
    .hero-glow {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.4;
        z-index: 0;
    }

    .glow-1 {
        top: -10%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: #0e2e45;
    }

    .glow-2 {
        bottom: 10%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, #1a4d70 0%, transparent 70%);
    }

    .glow-3 {
        top: 40%;
        left: 40%;
        width: 300px;
        height: 300px;
        background: rgba(255, 197, 8, 0.15);
        /* Accent color low opacity */
    }

    /* Gradient Typography */
    .text-gradient-gold {
        background: linear-gradient(135deg, #ffc508 0%, #fff6b9 50%, #d4a000 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0px 4px 15px rgba(255, 197, 8, 0.3);
    }

    /* Glassmorphism Button */
    .btn-glass {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .btn-glass::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: 0.5s;
    }

    .btn-glass:hover::after {
        left: 100%;
    }

    /* Primary Accent Button Glow */
    .btn-glow-accent {
        box-shadow: 0 0 20px rgba(255, 197, 8, 0.4);
        transition: all 0.3s ease;
    }

    .btn-glow-accent:hover {
        box-shadow: 0 0 35px rgba(255, 197, 8, 0.6);
        transform: translateY(-3px) scale(1.02);
    }

    /* Hero logo sizing */
    .hero-logo-img {
        max-height: 550px;
    }

    /* Floating Animation for Hero Image */
    .floating-animate {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-25px) rotate(1deg);
        }

        100% {
            transform: translateY(0px) rotate(0deg);
        }
    }

    /* Text Fade In Up Animation */
    .animate-fade-up {
        animation: fadeUp 1s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .delay-100 {
        animation-delay: 0.1s;
    }

    .delay-200 {
        animation-delay: 0.3s;
    }

    .delay-300 {
        animation-delay: 0.5s;
    }

    @keyframes fadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<section id="home" class="min-vh-100 d-flex align-items-center overflow-hidden position-relative pt-5 pb-5">

    <!-- Dynamic Background Elements -->
    <div class="hero-glow glow-1"></div>
    <div class="hero-glow glow-2"></div>
    <div class="hero-glow glow-3"></div>

    <div class="container position-relative z-2">
        <div class="row align-items-center h-100">
            <!-- Left Panel: Content -->
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <div class="mb-4">
                    <span
                        class="badge bg-white bg-opacity-10 backdrop-blur text-white px-3 py-2 rounded-pill border border-white border-opacity-25 animate-fade-up">
                        <i class="bi bi-stars text-accent me-2"></i> Welcome to the Future of Fabrication
                    </span>
                </div>

                <h1 class="display-3 fw-bolder mb-4 text-white lh-base animate-fade-up delay-100"
                    style="font-weight: 800; letter-spacing: -1px;">
                    Innovation Meets <br>
                    <span class="text-gradient-gold">Excellence</span>
                </h1>

                <p class="lead text-white-50 mb-5 animate-fade-up delay-200"
                    style="max-width: 550px; font-weight: 300;">
                    Discover cutting-edge products designed to transform your world. From revolutionary tech solutions
                    to premium fabricated goods, we bring tomorrow's innovations to today's market.
                </p>

                <div
                    class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start animate-fade-up delay-300">
                    <a href="{{ route('register') }}"
                        class="btn btn-accent btn-lg rounded-pill px-5 py-3 d-flex align-items-center justify-content-center gap-2 btn-glow-accent fw-bold text-uppercase tracking-wider">
                        <i class="bi bi-bag-fill"></i>
                        Start Shopping
                    </a>
                    <a href="#demo"
                        class="btn btn-glass btn-lg rounded-pill px-5 py-3 d-flex align-items-center justify-content-center gap-2 text-uppercase tracking-wider">
                        <i class="bi bi-play-circle-fill"></i>
                        Watch Demo
                    </a>
                </div>

                <!-- Trust Indicators / Small stats could go here -->
                <div
                    class="mt-5 pt-3 animate-fade-up delay-300 d-flex gap-4 justify-content-center justify-content-lg-start opacity-75">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-accent"></i> <span class="text-white-50 small">Premium
                            Quality</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-accent"></i> <span class="text-white-50 small">Fast
                            Delivery</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Logo / Visual -->
            <div class="col-lg-6 text-center position-relative">
                <!-- Decorative Circle behind logo -->
                <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-white bg-opacity-10"
                    style="width: 450px; height: 450px; filter: blur(50px); z-index: -1;">
                </div>

                <div class="position-relative d-inline-block floating-animate">
                    <img src="{{ asset('FABLAB-LOGO.png') }}" alt="FabLab Logo"
                        class="img-fluid position-relative drop-shadow-2xl hero-logo-img"
                        style="width: auto; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));">

                    <!-- Floating Badge Example -->
                    <div class="position-absolute bottom-0 end-0 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-25 rounded-4 p-3 mb-4 me-4 text-start animate-fade-up delay-300"
                        style="backdrop-filter: blur(15px);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-accent rounded-circle p-2 d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-shield-check text-dark fs-5"></i>
                            </div>
                            <div>
                                <small class="d-block text-white-50" style="font-size: 0.75rem;">Verified System</small>
                                <span class="fw-bold text-white">100% Secure</span>
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
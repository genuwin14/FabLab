<style>
    .footer-section {
        background: radial-gradient(circle at top right, #0e2e45 0%, #05111a 60%);
        position: relative;
    }

    .footer-link {
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        display: inline-block;
    }

    .footer-link:hover {
        color: #ffc508;
        transform: translateX(5px);
    }

    .footer-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0%;
        height: 1px;
        background: #ffc508;
        transition: width 0.3s ease;
    }

    .footer-link:hover::after {
        width: 100%;
    }

    .social-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .social-btn:hover {
        background: #ffc508;
        color: #05111a;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(255, 197, 8, 0.3);
    }

    /* Newsletter Input */
    .input-glass {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
    }

    .input-glass:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #ffc508;
        color: white;
        box-shadow: none;
    }

    .logo-gradient-gold {
        background: linear-gradient(135deg, #ffc508 0%, #fff6b9 50%, #d4a000 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }
</style>

<footer class="footer-section pt-5 pb-4 overflow-hidden text-white">
    <!-- Top Fade for Seamless Transition -->
    <div class="position-absolute top-0 start-0 w-100"
        style="height: 100px; background: linear-gradient(to top, transparent 0%, #05111a 100%); z-index: 1; pointer-events: none;">
    </div>

    <div class="container position-relative z-2 pt-5">
        <div class="row g-5 mb-5">
            <!-- Brand Column -->
            <div class="col-lg-4">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none mb-4">
                    <!-- Optional: Insert Logo Image here if desired -->
                    <span class="h3 mb-0 logo-gradient-gold tracking-wider">FABLAB</span>
                </a>
                <p class="text-white-50 mb-4 lh-lg" style="max-width: 300px;">
                    Making inventory management simple, smart, and efficient for businesses of all sizes. Empowering
                    growth through innovation.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <!-- Links Column 1 -->
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-4 text-white uppercase tracking-wider small">Pages</h6>
                <ul class="list-unstyled d-flex flex-column gap-3">
                    <li><a href="#home" class="footer-link">Home</a></li>
                    <li><a href="#product" class="footer-link">Product</a></li>
                </ul>
            </div>

            <!-- Links Column 2 -->
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-4 text-white uppercase tracking-wider small">Company</h6>
                <ul class="list-unstyled d-flex flex-column gap-3">
                    <li><a href="#about" class="footer-link">About</a></li>
                    <li><a href="#contact" class="footer-link">Contact</a></li>
                </ul>
            </div>

            <!-- Newsletter Column -->
            <div class="col-lg-4">
                <h6 class="fw-bold mb-4 text-white uppercase tracking-wider small">Stay Updated</h6>
                <p class="text-white-50 small mb-3">Subscribe to our newsletter for the latest updates and inventory
                    tips.</p>
                <form action="#" class="mb-3">
                    <div class="input-group">
                        <input type="email" class="form-control input-glass py-2" placeholder="Enter your email"
                            aria-label="Email">
                        <button class="btn btn-accent fw-bold" type="button">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="border-top border-white border-opacity-10 pt-4 text-center">
            <p class="text-white-50 small mb-0">© 2026 FABLAB Inventory System. All rights reserved.</p>
        </div>
    </div>
</footer>
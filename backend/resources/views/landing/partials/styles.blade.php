<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease;
    }

    /* Dark Theme Placeholders */
    ::placeholder {
        color: rgba(255, 255, 255, 0.5) !important;
        opacity: 1;
        /* Firefox */
    }

    :-ms-input-placeholder {
        /* Internet Explorer 10-11 */
        color: rgba(255, 255, 255, 0.5) !important;
    }

    ::-ms-input-placeholder {
        /* Microsoft Edge */
        color: rgba(255, 255, 255, 0.5) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    /* Prevent horizontal scroll from absolutely-positioned glow circles */
    .landing-page-wrapper {
        overflow-x: hidden;
    }

    /* ========================================
       Thin scrollbar (landing page)
       ======================================== */
    html {
        scrollbar-width: thin;
        scrollbar-color: #1a2a38 #05111a;
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #05111a;
    }

    ::-webkit-scrollbar-thumb {
        background: #1a2a38;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #25394d;
    }

    /* ========================================
       Side gutters: keep navbar pill & section
       content from sitting flush against screen
       edges on tablet/mobile.
       ======================================== */
    @media (max-width: 1199.98px) {
        /* All section containers (incl. footer) */
        #home > .container,
        #product > .container,
        #about > .container,
        #contact > .container,
        footer.footer-section > .container {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        /* Floating navbar: push pill inward from viewport edges */
        nav.navbar.fixed-top {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }

    @media (max-width: 575.98px) {
        #home > .container,
        #product > .container,
        #about > .container,
        #contact > .container,
        footer.footer-section > .container {
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }

        nav.navbar.fixed-top {
            padding-left: 0.6rem;
            padding-right: 0.6rem;
        }
    }

    /* ========================================
       Responsive: Tablet (≤991.98px)
       ======================================== */
    @media (max-width: 991.98px) {
        /* Drop strict viewport-height so content sizes naturally */
        #home,
        #product,
        #about,
        #contact {
            min-height: auto !important;
        }

        /* Hero: clear the floating navbar on tablets */
        #home {
            padding-top: 7rem !important;
        }

        /* Headline scale-down */
        #home .display-3 {
            font-size: 3rem !important;
        }

        #product .display-4,
        #about .display-4,
        #contact .display-4 {
            font-size: 2.4rem;
        }

        /* Hero image: cap height so it doesn't dominate the screen */
        #home .hero-logo-img {
            max-height: 380px !important;
        }

        /* Hero decorative circle behind logo */
        #home .col-lg-6:last-child > .position-absolute.rounded-circle {
            width: 320px !important;
            height: 320px !important;
        }
    }

    /* ========================================
       Responsive: Mobile (≤767.98px)
       ======================================== */
    @media (max-width: 767.98px) {
        /* Hero gets extra top room so the floating navbar doesn't overlap it */
        #home {
            padding-top: 7.5rem !important;
            padding-bottom: 3rem !important;
        }

        /* Other sections */
        #product,
        #about,
        #contact {
            padding-top: 4rem !important;
            padding-bottom: 3rem !important;
        }

        /* Anchor-link scroll offset so jumps don't hide content under the fixed nav */
        #home,
        #product,
        #about,
        #contact {
            scroll-margin-top: 80px;
        }

        /* Hero headline */
        #home .display-3 {
            font-size: 2.25rem !important;
            letter-spacing: -0.5px !important;
        }

        /* Section headlines */
        #product .display-4,
        #about .display-4,
        #contact .display-4 {
            font-size: 1.85rem;
        }

        /* Lead text */
        #home .lead,
        #product .lead,
        #about .lead,
        #contact .lead {
            font-size: 0.95rem !important;
        }

        /* Hero image smaller */
        #home .hero-logo-img {
            max-height: 260px !important;
        }

        /* Hero buttons trim */
        #home .btn-lg {
            padding: 0.7rem 1.5rem !important;
            font-size: 0.82rem !important;
        }

        /* Hero floating badge */
        #home .floating-animate > .position-absolute {
            padding: 0.5rem !important;
            margin: 0.5rem !important;
        }

        #home .floating-animate > .position-absolute .rounded-circle {
            width: 32px !important;
            height: 32px !important;
        }

        #home .floating-animate > .position-absolute .rounded-circle i {
            font-size: 0.9rem !important;
        }

        /* Trust indicators */
        #home .opacity-75 {
            flex-wrap: wrap;
            gap: 0.75rem !important;
        }

        #home .mt-5.pt-3 {
            margin-top: 1.5rem !important;
            padding-top: 0 !important;
        }

        /* Glow circles smaller so they don't cause horizontal scroll */
        .hero-glow.glow-1 {
            width: 280px !important;
            height: 280px !important;
        }

        .hero-glow.glow-2 {
            width: 320px !important;
            height: 320px !important;
        }

        .hero-glow.glow-3 {
            width: 180px !important;
            height: 180px !important;
        }

        /* Hero decorative circle behind logo */
        #home .col-lg-6:last-child > .position-absolute.rounded-circle {
            width: 240px !important;
            height: 240px !important;
        }

        /* Product mock UI */
        #product .glass-card-mockup .card-header {
            padding: 0.85rem 1rem !important;
        }

        #product .glass-card-mockup .card-body > .p-4 {
            padding: 0.85rem 1rem !important;
        }

        #product .glass-card-mockup .rounded-3.p-3 {
            padding: 0.65rem !important;
        }

        #product .glass-card-mockup .rounded-3.p-3 .fw-bold {
            font-size: 0.85rem;
        }

        #product .glass-card-mockup .rounded-3.p-3 .small {
            font-size: 0.7rem;
        }

        #product .glass-card-mockup .badge {
            font-size: 0.65rem;
        }

        /* Product feature icons */
        #product .feature-icon-box {
            width: 48px !important;
            height: 48px !important;
        }

        #product .feature-icon-box i {
            font-size: 1.15rem !important;
        }

        #product .feature-item h5 {
            font-size: 1rem;
        }

        #product .feature-item p {
            font-size: 0.85rem;
        }

        #product .feature-item.d-flex {
            gap: 1rem !important;
        }

        /* About cards */
        #about .about-card {
            padding: 1.25rem !important;
        }

        #about .icon-wrapper-about {
            width: 60px !important;
            height: 60px !important;
            margin-bottom: 1rem !important;
        }

        #about .icon-wrapper-about i {
            font-size: 1.5rem !important;
        }

        #about .about-card h5 {
            font-size: 1rem;
        }

        /* Contact card */
        #contact .contact-card {
            padding: 1.25rem !important;
        }

        #contact .contact-card h4 {
            font-size: 1.1rem;
        }

        #contact .contact-icon-box {
            width: 40px !important;
            height: 40px !important;
        }

        #contact .contact-icon-box .fs-5 {
            font-size: 1rem !important;
        }

        #contact .contact-item h5 {
            font-size: 0.95rem;
        }

        #contact .contact-item p {
            font-size: 0.85rem;
        }

        /* Quote decoration smaller */
        #contact .contact-card .display-1 {
            font-size: 3rem !important;
        }

        /* Footer */
        footer.footer-section {
            padding-top: 2.5rem !important;
        }

        footer .row.g-5 {
            --bs-gutter-y: 2rem;
            --bs-gutter-x: 1.5rem;
        }

        footer h6 {
            margin-bottom: 1rem !important;
        }

        footer .social-btn {
            width: 36px !important;
            height: 36px !important;
            font-size: 0.9rem;
        }

        /* Newsletter input + button stack on tiny screens */
        footer form .input-group {
            flex-direction: column;
            gap: 0.5rem;
        }

        footer form .input-group .form-control,
        footer form .input-group .btn {
            border-radius: 0.5rem !important;
            width: 100%;
        }

        footer form .input-group .btn {
            padding: 0.55rem 0.75rem;
        }

        /* Badges (section "labels") */
        #home .badge,
        #product .badge,
        #about .badge,
        #contact .badge {
            font-size: 0.7rem;
        }
    }

    /* ========================================
       Very small phones (≤400px)
       ======================================== */
    @media (max-width: 400px) {
        #home .display-3 {
            font-size: 1.9rem !important;
        }

        #product .display-4,
        #about .display-4,
        #contact .display-4 {
            font-size: 1.6rem;
        }

        #home .hero-logo-img {
            max-height: 200px !important;
        }
    }
</style>
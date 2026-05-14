<nav class="navbar navbar-expand-lg fixed-top mt-3">
    <div
        class="container bg-primary bg-opacity-50 backdrop-blur rounded-pill px-3 px-md-4 py-2 shadow-lg border border-white border-opacity-10 landing-navbar">
        <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>
        <button class="navbar-toggler border-0 text-white p-1" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link text-white text-opacity-75 hover-text-white px-3" href="#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white text-opacity-75 hover-text-white px-3" href="#product">Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white text-opacity-75 hover-text-white px-3" href="#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white text-opacity-75 hover-text-white px-3" href="#contact">Contact</a>
                </li>
            </ul>
            <div class="d-flex gap-2 mt-3 mt-lg-0 nav-actions">
                <a href="{{ route('login') }}" class="btn btn-sm btn-link text-white text-decoration-none fw-medium">Log
                    in</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-accent rounded-pill px-4 fw-bold">Get
                    Started</a>
            </div>
        </div>
    </div>
</nav>

<style>
    .backdrop-blur {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .hover-text-white:hover {
        color: #fff !important;
        opacity: 1 !important;
    }

    /* Mobile/tablet: pill gets side margins so it floats inside the viewport */
    @media (max-width: 991.98px) {
        .landing-navbar {
            width: calc(100% - 2rem) !important;
            margin-left: auto !important;
            margin-right: auto !important;
            border-radius: 20px !important;
        }

        .landing-navbar .navbar-nav {
            padding-top: 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 0.5rem;
        }

        .landing-navbar .nav-link {
            padding: 0.55rem 0.5rem !important;
        }

        .landing-navbar .nav-actions {
            justify-content: center;
            padding-bottom: 0.5rem;
        }
    }

    /* Very small phones: a touch less margin so the pill keeps usable width */
    @media (max-width: 400px) {
        .landing-navbar {
            width: calc(100% - 1.25rem) !important;
        }
    }

    @media (max-width: 360px) {
        .landing-navbar .nav-actions {
            flex-direction: column;
            width: 100%;
        }

        .landing-navbar .nav-actions .btn {
            width: 100%;
        }
    }
</style>
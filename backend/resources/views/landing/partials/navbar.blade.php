<nav class="navbar navbar-expand-lg fixed-top mt-3">
    <div
        class="container bg-primary bg-opacity-50 backdrop-blur rounded-pill px-4 py-2 shadow-lg border border-white border-opacity-10">
        <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse"
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
            <div class="d-flex gap-2">
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
</style>
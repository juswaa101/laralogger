@include('components.header')

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark-custom">
    <div class="container-fluid">
        <a class="btn btn-outline-light btn-github" href="https://github.com/juswaa101" target="_blank" role="button">
            <i class="fab fa-github"></i>
        </a>
        <a class="navbar-brand" href="/">Laralogger</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#aboutUsModal">About
                        Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

@include('components.modal.about-us')

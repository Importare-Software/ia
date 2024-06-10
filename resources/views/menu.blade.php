<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        <span class="fs-4">Opciones</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : 'link-dark' }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ url('/manual-input') }}" class="nav-link {{ request()->is('manual-input') ? 'active' : 'link-dark' }}">
                Modelo Manual
            </a>
        </li>
        <li>
            <a href="{{ url('/test-dusk') }}" class="nav-link {{ request()->is('test-dusk') ? 'active' : 'link-dark' }}">
                Modelo de Imagen
            </a>
        </li>
        <li>
            <a href="{{ url('/upload-excel') }}" class="nav-link {{ request()->is('upload-excel') ? 'active' : 'link-dark' }}">
                Modelo de Excel
            </a>
        </li>
        <li>
            <a href="{{ url('/test_results') }}" class="nav-link {{ request()->is('test_results') ? 'active' : 'link-dark' }}">
                Calificar y Corregir
            </a>
        </li>
    </ul>
</div>
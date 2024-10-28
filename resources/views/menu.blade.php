<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px;">
    <a href="/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
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
        @if(auth()->check() && in_array(auth()->user()->email, ['alexis@importare.mx', 'jvalle0514@gmail.com']))
        <li>
            <a href="{{ url('/chatbot/settings') }}" class="nav-link {{ request()->is('chatbot/settings') ? 'active' : 'link-dark' }}">
                Configurar Chat
            </a>
        </li>
        @endif
        <li>
            <a href="{{ url('/upload-data') }}" class="nav-link {{ request()->is('upload-data') ? 'active' : 'link-dark' }}">
                Cargar data (Chat)
            </a>
        </li>
        <li>
            <a href="{{ url('/chat') }}" class="nav-link {{ request()->is('chat') ? 'active' : 'link-dark' }}">
                ImporChat
            </a>
        </li>
        <br><br><br><br>
        <li>
            <a href="{{ route('logout') }}" class="nav-link link-dark" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar Sesión
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</div>
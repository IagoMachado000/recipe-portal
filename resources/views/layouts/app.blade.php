<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm py-3">
            <div class="container align-items-center">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Portal de Receitas
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse gap-3" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav ms-auto d-flex align-items-center gap-3">
                        <!-- Authentication Links -->
                        <li>
                            <a href="{{ route('recipes.index') }}" class="link-offset-2 link-underline link-underline-opacity-0 text-muted">
                                Home
                            </a>
                        </li>
                        @auth
                            <li>
                                <a href="{{ route('recipes.dashboard') }}" class="link-offset-2 link-underline link-underline-opacity-0 text-muted">
                                    Minhas Receitas
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav">
                        @auth
                            <!-- Notificações -->
                            <li class="nav-item dropdown position-relative">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                                    </svg>
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ Auth::user()->unreadNotifications->count() > 99 ? '99+' : Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                                        <span>Notificações</span>
                                        @if(Auth::user()->unreadNotifications->count() > 0)
                                            <small>
                                                <a href="#" class="text-decoration-none" onclick="markAllAsRead()">
                                                    Marcar todas como lidas
                                                </a>
                                            </small>
                                        @endif
                                    </div>

                                    <hr class="my-1">

                                    @php
                                        $notifications = Auth::user()->notifications()->latest()->limit(5)->get();
                                    @endphp

                                    @forelse($notifications as $notification)
                                        <div class="dropdown-item {{ $notification->read_at ? 'text-muted' : '' }} p-2"
                                            style="white-space: normal; line-height: 1.4;">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <small class="fw-bold">{{ $notification->data['title'] ?? 'Notificação' }}</small>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <small class="d-block">{{ $notification->data['message'] ?? '' }}</small>

                                            @if(!$notification->read_at)
                                                <div class="mt-1">
                                                    <a href="{{ $notification->data['url'] ?? '#' }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="markAsRead({{ $notification->id }}, this)">
                                                        Ver
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="dropdown-item text-muted p-3 text-center">
                                            <small>Nenhuma notificação</small>
                                        </div>
                                    @endforelse

                                    @if(Auth::user()->notifications()->count() > 5)
                                        <div class="text-center p-2">
                                            <small>
                                                <a href="{{ route('notifications.index') }}" class="text-decoration-none">
                                                    Ver todas
                                                </a>
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </li>

                            <!-- User Menu -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Sair
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <script>
        function markAsRead(notificationId, element) {
            fetch(`/dashboard/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover destaque de não lido
                    element.closest('.dropdown-item').classList.add('text-muted');
                    element.remove();

                    // Atualizar badge
                    updateNotificationBadge();
                }
            })
            .catch(error => console.error('Error:', error));
        }
        function markAllAsRead() {
            fetch('/dashboard/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload para atualizar UI
                    location.reload();
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>

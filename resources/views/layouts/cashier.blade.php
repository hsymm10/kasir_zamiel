<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir POS') - {{ $settings['app_name'] ?? 'Kasir Zamiel' }}</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Dynamic Theme Color Overrides from Settings --}}
    <style>
        :root {
            --olivenite: {{ $settings['color_primary'] ?? '#313118' }};
            --bancha: {{ $settings['color_secondary'] ?? '#6D6844' }};
            --hillside: {{ $settings['color_accent'] ?? '#CCC6A9' }};
            --terracotta: {{ $settings['color_danger'] ?? '#83482B' }};
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Top Header -->
    <header class="app-header">
        <div class="app-brand">
            <i class="fa-solid fa-cash-register"></i>
            <span>{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</span>
        </div>
        <div class="user-nav">
            <span class="user-badge" style="background-color: var(--success); color: var(--text-light);">
                <i class="fa-solid fa-circle"></i> Kasir Aktif: {{ Auth::user()->name }}
            </span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.75rem;">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </header>

    <!-- Content area -->
    @yield('content')

    <!-- Scripts -->
    @yield('scripts')
</body>
</html>

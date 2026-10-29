<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $settings['app_name'] ?? 'Kasir Zamiel' }}</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
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
    
    <style>
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--olivenite) 0%, var(--bancha) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Abstract decorative shapes */
        .login-wrapper::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background-color: var(--terracotta);
            opacity: 0.15;
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .login-wrapper::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background-color: var(--hillside);
            opacity: 0.1;
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--bg-cream);
            border-radius: var(--radius-lg);
            border: 1px solid var(--hillside);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            z-index: 10;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo i {
            font-size: 3rem;
            color: var(--olivenite);
            margin-bottom: 0.5rem;
        }

        .login-logo h2 {
            font-size: 1.8rem;
            color: var(--olivenite);
            margin-bottom: 0.25rem;
        }

        .login-logo p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <i class="fa-solid fa-store"></i>
                <h2>{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</h2>
                <p>{{ $settings['store_name'] ?? 'Warung Sembako Mbah' }}</p>
            </div>

            <!-- Flash success message (e.g., after logout) -->
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; font-size: 0.85rem;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus style="padding-left: 2.5rem;">
                    </div>
                    @error('username')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required style="padding-left: 2.5rem;">
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <input type="checkbox" name="remember" id="remember" style="accent-color: var(--bancha); width: 16px; height: 16px; cursor: pointer;">
                    <label for="remember" style="font-size: 0.85rem; color: var(--text-muted); cursor: pointer; user-select: none;">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Masuk <i class="fa-solid fa-right-to-bracket"></i>
                </button>
            </form>

            <!-- Quick Demo Login Selection -->
            <div style="margin-top: 1.5rem; text-align: center; border-top: 1px dashed var(--border-color); padding-top: 1.25rem;">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem; font-weight: 600; letter-spacing: 0.05em;">LOGIN CEPAT DEMO:</div>
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                    <button type="button" onclick="fillDemo('admin', 'admin123')" class="btn btn-secondary btn-sm" style="flex: 1; font-size: 0.75rem; padding: 0.5rem 0.25rem; border: 1.5px solid var(--border-color);">
                        <i class="fa-solid fa-user-tie"></i> Super Admin
                    </button>
                    <button type="button" onclick="fillDemo('kasir1', 'kasir123')" class="btn btn-primary btn-sm" style="flex: 1; font-size: 0.75rem; padding: 0.5rem 0.25rem;">
                        <i class="fa-solid fa-cash-register"></i> Kasir POS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillDemo(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>

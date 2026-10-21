<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión — {{ config('app.name', 'DTG Inventario') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-card">

        <div class="login-header">
            <div class="icon-wrapper">
                <i class="fas fa-boxes-stacked fa-2x"></i>
            </div>
            <h4 class="fw-bold mb-1">{{ config('app.name', 'DTG Inventario') }}</h4>
            <p class="mb-0 opacity-75 small">Sistema de gestión de equipos</p>
        </div>

        <div class="login-body">
            <h5 class="fw-bold mb-4 text-center text-secondary">Iniciar sesión</h5>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="correo@ejemplo.cl"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="email">
                    <label for="email">
                        <i class="fas fa-envelope me-1 text-secondary"></i>Correo electrónico
                    </label>
                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Contraseña"
                           required autocomplete="current-password">
                    <label for="password">
                        <i class="fas fa-lock me-1 text-secondary"></i>Contraseña
                    </label>
                    <button type="button" id="toggle-password"
                            class="btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-secondary"
                            tabindex="-1" style="z-index:10;">
                        <i class="fas fa-eye" id="icon-password"></i>
                    </button>
                    @error('password')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small text-secondary" for="remember">
                            Recordarme
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 text-white fw-bold">
                    <i class="fas fa-right-to-bracket me-2"></i>Ingresar
                </button>
            </form>

            <p class="footer-text">
                {{ config('app.name') }} · Servicio de Evaluación Ambiental
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
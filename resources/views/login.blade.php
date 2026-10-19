
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="google" content="notranslate">
    <title>VMM-School | Iniciar sesión</title>
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ @filemtime(public_path('css/login.css')) }}">
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <main id="app">
        <section id="login-screen" class="login-screen">
            
            <!-- Contenedor maestro tipo tarjeta dividida (Split Card) -->
            <div class="split-card-container">
                
                <!-- BLOQUE IZQUIERDO: Imagen de la docente / Arte -->
                <div class="split-art-side hero-bg-side">
                    <!-- La imagen ahora cuenta con una clase para heredar las dimensiones completas -->
                    <img src="{{ asset('img/hero-bg.jpg') }}" alt="VMM-School Arte" class="art-img-full">
                </div>

                <!-- BLOQUE DERECHO: Panel Oscuro con el Formulario -->
                <div class="split-form-side">
                    <div class="form-content-wrap">
                        
                        <!-- ÁREA DEL LOGO -->
                        <div class="brand">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo VMM-School" style="height: 35px; width: auto; object-fit: contain;">
                            <span class="logo-text">VMM School</span>
                        </div>

                        <h2>Ingresa a tu cuenta</h2>

                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf
                            
                            <!-- Campo Identificador -->
                            <label for="identificador">DNI o Correo institucional</label>
                            <div class="input-block">
                                <input id="identificador" name="identificador" type="text" value="{{ old('identificador') }}" placeholder="DNI o Correo institucional" required>
                            </div>

                            <!-- Campo Contraseña -->
                            <label for="clave">Contraseña</label>
                            <div class="input-block password-container" style="margin-bottom: 12px;">
                                <input id="clave" name="clave" type="password" placeholder="Contraseña" required>
                                <button type="button" class="show-password" id="toggle-password" aria-label="Mostrar contraseña">
                                    <svg xmlns="http://w3.org" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- ZONA DE ERRORES (Aparecerá estrictamente debajo de la contraseña) -->
                            @error('identificador')
                                <div class="toast-error" style="margin-bottom: 15px;" role="alert">{{ $message }}</div>
                            @enderror
                            @error('clave')
                                <div class="toast-error" style="margin-bottom: 15px;" role="alert">{{ $message }}</div>
                            @enderror

                            <!-- Casilla de verificación: Recordarme -->
                            <div class="remember-block">
                                <label class="check-container">
                                    <input type="checkbox" name="remember" id="remember">
                                    <span class="checkmark"></span>
                                    Recordarme
                                </label>
                            </div>

                            <button class="btn-submit" type="submit">INICIAR SESIÓN</button>
                        </form>

                        <!-- Enlaces e Información de Soporte -->
                        <div class="login-footer-links">
                            <a href="#" class="link-forgot">¿Olvidaste tu contraseña?</a>
                            <p class="help-text">¿Necesitas ayuda? <a href="#">Contacta a soporte</a></p>
                        </div>

                        <!-- Términos Legales del Pie de Página de la Tarjeta -->
                        <div class="legal-footer">
                            <a href="#">Términos de uso</a>
                            <a href="#">Política de privacidad</a>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </main>
    <script src="{{ asset('js/login.js') }}?v={{ @filemtime(public_path('js/login.js')) }}" defer></script>
</body>
</html>
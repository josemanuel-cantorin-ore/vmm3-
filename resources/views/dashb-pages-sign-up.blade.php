<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="VMM-School - Sign Up">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />

    <title>Sign Up | VMM-School</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">Comenzar en VMM-School</h1>
                            <p class="lead">
                                Crea tu cuenta de docente o administrador para continuar.
                            </p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-4">
                                    <form method="POST" action="#">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Nombre completo</label>
                                            <input class="form-control form-control-lg" type="text" name="name" placeholder="Ingresa tu nombre" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Institución o Colegio</label>
                                            <input class="form-control form-control-lg" type="text" name="company" placeholder="Ingresa el nombre de la institución" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Correo electrónico</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Ingresa tu correo" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Ingresa una contraseña" required />
                                        </div>
                                        <div class="text-center mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary w-100">Registrarse</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>
@extends('layouts.admin')

@section('title', 'Control de Aula')

@section('contenido')

<body>
	@php
		$profesor = auth()->user();
		$asignaciones = $profesor->asignacionesDocente;
		$cursos = $asignaciones
			->map(fn ($asignacion) => $asignacion->curso?->nombre_curso)
			->filter()
			->unique()
			->implode(', ');
		$aulas = $asignaciones
			->map(fn ($asignacion) => $asignacion->gradoSeccion?->nombre_completo)
			->filter()
			->unique()
			->implode(', ');
		$nombreCompleto = trim(($profesor->nombres ?? '') . ' ' . ($profesor->apellidos ?? ''));
	@endphp

	<main class="content pt-0">
		<div class="container-fluid pt-0">
			<header class="text-center pt-0 pb-2 mb-2">
				<img src="{{ asset('img/avatars/avatar.jpg') }}" alt="Foto de perfil genérica" class="img-fluid rounded-circle mb-3" width="128" height="128">
				<h1 class="h3 text-uppercase mb-1">{{ $nombreCompleto ?: 'Nombre no registrado' }}</h1>
				<p class="text-muted mb-0">{{ $profesor->correo ?? 'Correo no registrado' }}</p>
				
			</header>

	

			<div class="row">
				<div class="col-md-6">
					<section class="mb-4">
						<h2 class="h5 text-dark mb-2">Información básica</h2>
						<div class="card">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center py-3 border-bottom">
									<span class="fw-semibold text-dark">Nombre completo</span>
									<span class="text-end ms-3">{{ $nombreCompleto ?: 'No registrado' }}</span>
								</div>
								<div class="d-flex justify-content-between align-items-center py-3 border-bottom">
									<span class="fw-semibold text-dark">Dirección de correo electrónico</span>
									<span class="text-end ms-3">{{ $profesor->correo ?? 'No registrado' }}</span>
								</div>
								<div class="d-flex justify-content-between align-items-center py-3 border-bottom">
									<span class="fw-semibold text-dark">Sexo</span>
									@if ($profesor->sexo ?? null)
										<span class="text-end ms-3">{{ auth()->user()->sexo ?? 'Agregar género' }}</span>
									@else
										<a href="#" class="text-primary text-decoration-none ms-3">{{ auth()->user()->sexo ?? 'Agregar género' }}</a>
									@endif
								</div>
								<div class="d-flex justify-content-between align-items-center py-3 border-0">
									<span class="fw-semibold text-dark">Contraseña</span>
									<a href="#" class="text-primary text-decoration-none ms-3">Cambiar contraseña</a>
								</div>
							</div>
						</div>
					</section>

					<section class="mb-4">
						<h2 class="h5 text-dark mb-2">Información de contacto</h2>
						<div class="card">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center py-3 border-bottom">
									<span class="fw-semibold text-dark">Número de teléfono</span>
									@if (auth()->user()->telefono ?? null)
										<span class="text-end ms-3">{{ auth()->user()->telefono ?? 'Agregar número de teléfono' }}</span>
									@else
										<a href="#" class="text-primary text-decoration-none ms-3">{{ auth()->user()->telefono ?? 'Agregar número de teléfono' }}</a>
									@endif
								</div>
								<div class="d-flex justify-content-between align-items-center py-3 border-0">
									<span class="fw-semibold text-dark">Dirección postal</span>
									<span class="text-end ms-3">{{ $profesor->direccion ?? 'No registrado' }}</span>
								</div>
							</div>
						</div>
					</section>
				</div>

				<div class="col-md-6">
					<section class="mb-4">
						<h2 class="h5 text-dark mb-2">Información académica</h2>
						<div class="card">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center py-3 border-bottom">
									<span class="fw-semibold text-dark">Área / Curso que enseña</span>
									<span class="text-end ms-3">{{ $cursos ?: 'No registrado' }}</span>
								</div>
								<div class="d-flex justify-content-between align-items-center py-3 border-0">
									<span class="fw-semibold text-dark">Aulas asignadas</span>
									<span class="text-end ms-3">{{ $aulas ?: 'No registrado' }}</span>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</main>
	<script>
		feather.replace();
	</script>
</body>
@endsection

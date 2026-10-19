<div class="report-page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <a class="btn btn-link p-0 mb-2 text-decoration-none" href="{{ route('admin.control-aulas', $aula->id_asignacion) }}#reportes">
            <i data-feather="arrow-left" class="me-1" style="width: 16px; height: 16px;"></i>
            Volver a Reportes
        </a>
        <h1 class="h3 mb-1">{{ $titulo }}</h1>
        <p class="text-muted mb-0">{{ $aula->gradoSeccion->grado }} - {{ $aula->gradoSeccion->seccion }} <span class="mx-1">·</span> {{ $aula->gradoSeccion->nivel }}</p>
    </div>
    <div class="text-md-end small text-muted">
        <div>Profesor: {{ $aula->profesor->apellidos }}, {{ $aula->profesor->nombres }}</div>
        <div>Fecha de consulta: {{ now('America/Lima')->format('d/m/Y') }}</div>
    </div>
</div>

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="status">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="fw-semibold mb-1">Revisa la información ingresada:</p>
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

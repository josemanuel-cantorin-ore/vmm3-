@extends('layouts.admin')

@section('title', 'Reporte de tareas')

@section('contenido')
<div class="container-fluid p-0">
    @include('reportes.partials.cabecera', ['titulo' => 'Reporte de tareas'])

    <div class="report-step-label"><span>1</span> Elige las tareas y busca estudiantes</div>
    <section class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.tareas', $aula->id_asignacion) }}" class="row g-3">
                <fieldset class="col-12">
                    <legend class="form-label">¿Qué tareas quieres incluir?</legend>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="alcance" id="alcance_todas" value="todas" @checked(($filtros['alcance'] ?? 'todas') === 'todas')>
                            <label class="form-check-label" for="alcance_todas">Todas las tareas del aula</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="alcance" id="alcance_seleccionadas" value="seleccionadas" @checked(($filtros['alcance'] ?? '') === 'seleccionadas')>
                            <label class="form-check-label" for="alcance_seleccionadas">Solo las tareas seleccionadas</label>
                        </div>
                    </div>
                </fieldset>
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="buscar_tarea">Buscar tareas</label>
                    <input class="form-control" type="search" id="buscar_tarea" name="buscar_tarea" value="{{ $filtros['buscar_tarea'] ?? '' }}" placeholder="Escribe el nombre de una tarea" data-live-search="#lista-tareas">
                    <div class="report-task-picker border rounded mt-2 p-2" id="lista-tareas" data-search-results>
                        @forelse($tareas as $tarea)
                            <div class="form-check report-task-option py-1" data-search-item>
                                <input class="form-check-input" type="checkbox" name="tareas[]" id="tarea_{{ $tarea->id_tarea }}" value="{{ $tarea->id_tarea }}"
                                    @checked(in_array((string) $tarea->id_tarea, array_map('strval', $filtros['tareas'] ?? []), true))>
                                <label class="form-check-label" for="tarea_{{ $tarea->id_tarea }}">
                                    {{ $tarea->titulo }} <span class="text-muted small">· {{ $tarea->fecha_entrega->format('d/m/Y') }}</span>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small text-center mb-0 py-2">No hay tareas que coincidan con la búsqueda.</p>
                        @endforelse
                        <p class="text-muted small text-center mb-0 py-2 d-none" data-search-empty>No encontramos tareas con ese nombre.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="buscar_estudiante">Buscar estudiante</label>
                    <input class="form-control" type="search" id="buscar_estudiante" name="buscar_estudiante" value="{{ $filtros['buscar_estudiante'] ?? '' }}" placeholder="Escribe un apellido o nombre" data-live-search="#tareas-resultados">
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-lg w-100" type="submit">
                        <i data-feather="search" class="me-1"></i> Ver reporte
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="report-step-label"><span>2</span> Revisa las entregas y las notas</div>
    <section class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div><h2 class="h5 mb-1">Avance de tareas</h2><p class="text-muted small mb-0">Las notas mantienen la escala con la que se calificó cada tarea.</p></div>
            <span class="badge bg-light text-dark">{{ $registros->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Estudiante</th><th>Tarea</th><th>Entrega</th><th>Nota</th></tr></thead>
                <tbody id="tareas-resultados" data-search-results>
                    @forelse($registros as $registro)
                        <tr data-search-item>
                            <td>{{ $registro->nombre_estudiante }}</td>
                            <td>{{ $registro->titulo }}</td>
                            <td>
                                <span class="badge {{ $registro->cumplio === null ? 'bg-secondary' : ($registro->entrego ? 'bg-success' : 'bg-warning text-dark') }}">
                                    {{ $registro->estado_entrega }}
                                </span>
                            </td>
                            <td>{{ $registro->nota_reporte }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay estudiantes o tareas que coincidan con estos filtros.</td></tr>
                    @endforelse
                    <tr class="d-none" data-search-empty><td colspan="4" class="text-center py-4 text-muted">No encontramos resultados con ese nombre.</td></tr>
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
            <div class="card-footer bg-white">{{ $registros->links() }}</div>
        @endif
    </section>

    <div class="report-step-label"><span>3</span> Elige el formato</div>
    @include('reportes.partials.exportar', [
        'action' => route('admin.reportes.tareas.exportar', $aula->id_asignacion),
        'exportValues' => [
            'alcance' => $filtros['alcance'] ?? 'todas',
            'tareas' => $filtros['tareas'] ?? [],
            'buscar_tarea' => $filtros['buscar_tarea'] ?? '',
            'buscar_estudiante' => $filtros['buscar_estudiante'] ?? '',
        ],
    ])
</div>
@endsection

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/reportes.js') }}" defer></script>
@endpush

@extends('layouts.admin')

@section('title', 'Reporte de asistencias')

@section('contenido')
<div class="container-fluid p-0">
    @include('reportes.partials.cabecera', ['titulo' => 'Reporte de asistencias'])

    <div class="report-step-label"><span>1</span> Elige las fechas y busca estudiantes</div>
    <section class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.asistencia', $aula->id_asignacion) }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="modo_periodo">¿Qué fechas necesitas?</label>
                    <select class="form-select" id="modo_periodo" name="modo_periodo">
                        <option value="fecha" @selected(($filtros['modo_periodo'] ?? 'fecha') === 'fecha')>Un día</option>
                        <option value="rango" @selected(($filtros['modo_periodo'] ?? '') === 'rango')>Un rango de fechas</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 report-date-single">
                    <label class="form-label" for="fecha">Fecha</label>
                    <input class="form-control" type="date" id="fecha" name="fecha" value="{{ $filtros['fecha'] ?? '' }}">
                </div>
                <div class="col-12 col-md-3 report-date-range">
                    <label class="form-label" for="fecha_inicio">Desde</label>
                    <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-12 col-md-3 report-date-range">
                    <label class="form-label" for="fecha_fin">Hasta</label>
                    <input class="form-control" type="date" id="fecha_fin" name="fecha_fin" value="{{ $filtros['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="buscar_estudiante">Buscar estudiante</label>
                    <input class="form-control" type="search" id="buscar_estudiante" name="buscar_estudiante" value="{{ $filtros['buscar_estudiante'] ?? '' }}" placeholder="Escribe un apellido o nombre" data-live-search="#asistencia-resultados">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i data-feather="search" class="me-1"></i> Ver reporte
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="report-step-label"><span>2</span> Revisa la información</div>
    <section class="card mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h2 class="h5 mb-1">Asistencia registrada</h2>
                <p class="text-muted small mb-0">
                    @if(($filtros['modo_periodo'] ?? 'fecha') === 'fecha')
                        Fecha: {{ \Carbon\Carbon::parse($filtros['fecha'])->format('d/m/Y') }}
                    @else
                        Del {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}
                    @endif
                </p>
            </div>
            <span class="badge bg-light text-dark">{{ $registros->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Estudiante</th><th>Estado</th></tr>
                </thead>
                <tbody id="asistencia-resultados" data-search-results>
                    @forelse($registros as $registro)
                        <tr data-search-item>
                            <td>{{ $registro->fecha->format('d/m/Y') }}</td>
                            <td>{{ $registro->nombre_estudiante }}</td>
                            <td><span class="badge {{ $registro->clase_estado_reporte }}">{{ $registro->estado_reporte }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No hay registros de asistencia en estas fechas.</td></tr>
                    @endforelse
                    <tr class="d-none" data-search-empty><td colspan="3" class="text-center py-4 text-muted">No encontramos estudiantes con ese nombre.</td></tr>
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
            <div class="card-footer bg-white">{{ $registros->links() }}</div>
        @endif
    </section>

    <div class="report-step-label"><span>3</span> Elige el formato</div>
    @include('reportes.partials.exportar', [
        'action' => route('admin.reportes.asistencia.exportar', $aula->id_asignacion),
        'exportValues' => [
            'modo_periodo' => $filtros['modo_periodo'] ?? 'fecha',
            'fecha' => $filtros['fecha'] ?? '',
            'fecha_inicio' => $filtros['fecha_inicio'] ?? '',
            'fecha_fin' => $filtros['fecha_fin'] ?? '',
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

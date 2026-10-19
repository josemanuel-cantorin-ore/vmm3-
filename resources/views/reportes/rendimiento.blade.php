@extends('layouts.admin')

@section('title', 'Rendimiento del aula')

@section('contenido')
<div class="container-fluid p-0">
    @include('reportes.partials.cabecera', ['titulo' => 'Rendimiento del aula'])

    <div class="report-step-label"><span>1</span> Elige qué quieres revisar</div>
    <section class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.rendimiento', $aula->id_asignacion) }}" class="row g-3 align-items-end" data-period-filter>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="tipo_reporte">Ver rendimiento</label>
                    <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                        <option value="aula">Del salón</option>
                        <option value="estudiante" @selected(!empty($filtros['id_estudiante']))>De un estudiante</option>
                    </select>
                </div>
                <div class="col-12 col-md-4" data-student-filter>
                    <label class="form-label" for="buscar_estudiante">Buscar estudiante</label>
                    <input class="form-control mb-2" type="search" id="buscar_estudiante" placeholder="Escribe su nombre">
                    <select class="form-select" id="id_estudiante" name="id_estudiante">
                        <option value="">Selecciona un estudiante</option>
                        @foreach($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id_estudiante }}" @selected((string) ($filtros['id_estudiante'] ?? '') === (string) $estudiante->id_estudiante)>
                                {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Al elegir estudiante, se mostrarán solo sus datos.</small>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="filtro">Periodo de tiempo</label>
                    <select class="form-select" id="filtro" name="filtro">
                        <option value="semana" @selected(($filtros['filtro'] ?? 'semana') === 'semana')>Esta semana</option>
                        <option value="mes" @selected(($filtros['filtro'] ?? '') === 'mes')>Este mes</option>
                        <option value="periodo" @selected(($filtros['filtro'] ?? '') === 'periodo')>Bimestre o trimestre</option>
                        <option value="personalizado" @selected(($filtros['filtro'] ?? '') === 'personalizado')>Fechas elegidas</option>
                    </select>
                </div>
                <div class="col-12 col-md-2" data-period-type>
                    <label class="form-label" for="tipo_periodo">Tipo</label>
                    <select class="form-select" id="tipo_periodo" name="tipo_periodo">
                        <option value="bimestre" @selected(($filtros['tipo_periodo'] ?? 'bimestre') === 'bimestre')>Bimestre</option>
                        <option value="trimestre" @selected(($filtros['tipo_periodo'] ?? '') === 'trimestre')>Trimestre</option>
                    </select>
                </div>
                <div class="col-12 col-md-4" data-period-select>
                    <label class="form-label" for="id_periodo">Periodo</label>
                    <select class="form-select" id="id_periodo" name="id_periodo">
                        <option value="">Selecciona un periodo</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id_periodo }}" data-period-type="{{ $periodo->tipo_periodo }}" @selected((string) ($filtros['id_periodo'] ?? '') === (string) $periodo->id_periodo)>
                                {{ $periodo->nombre_periodo }} ({{ $periodo->fecha_inicio->format('d/m/Y') }} - {{ $periodo->fecha_fin->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @if($periodos->isEmpty())
                        <small class="text-muted">Aún no hay periodos de este tipo configurados.</small>
                    @endif
                </div>
                <div class="col-12 col-md-3" data-date-start>
                    <label class="form-label" for="fecha_inicio">Desde</label>
                    <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-12 col-md-3" data-date-end>
                    <label class="form-label" for="fecha_fin">Hasta</label>
                    <input class="form-control" type="date" id="fecha_fin" name="fecha_fin" value="{{ $filtros['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-lg" type="submit"><i data-feather="bar-chart-2" class="me-1"></i> Ver rendimiento</button>
                </div>
            </form>
        </div>
    </section>

    <div class="report-step-label"><span>2</span> Revisa la evolución</div>
    <div class="alert alert-light border d-flex flex-column flex-sm-row justify-content-between gap-2" role="status">
        <span><strong>{{ !empty($filtros['id_estudiante']) ? optional($estudiantes->firstWhere('id_estudiante', (int) $filtros['id_estudiante']))->nombre_completo ?? 'Estudiante' : 'Salón' }}</strong> · {{ $estadisticas['etiqueta_periodo'] }}</span>
        <span class="text-muted">Del {{ $estadisticas['inicio'] }} al {{ $estadisticas['fin'] }}</span>
    </div>

    <div class="row g-3 mb-4" data-report-chart="{{ json_encode($estadisticas, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}">
        <div class="col-12 col-xl-6">
            @include('reportes.partials.grafico', ['id' => 'grafico-asistencia', 'titulo' => 'Asistencia', 'descripcion' => 'Cantidad de registros por estado en cada fecha.'])
        </div>
        <div class="col-12 col-xl-6">
            @include('reportes.partials.grafico', ['id' => 'grafico-tareas', 'titulo' => 'Entrega de tareas', 'descripcion' => 'Porcentaje de entregas registradas como cumplidas.'])
        </div>
        <div class="col-12 col-xl-6">
            @include('reportes.partials.grafico', ['id' => 'grafico-notas-cuantitativas', 'titulo' => 'Notas cuantitativas · 0 a 20', 'descripcion' => 'Promedio de notas numéricas; se mantiene separado de la escala literal.'])
        </div>
        <div class="col-12 col-xl-6">
            @include('reportes.partials.grafico', ['id' => 'grafico-notas-cualitativas', 'titulo' => 'Notas cualitativas · AD, A, B y C', 'descripcion' => 'Cantidad de calificaciones por nivel, sin convertirlas a números.'])
        </div>
    </div>

    <div class="report-step-label"><span>3</span> Elige el formato</div>
    @include('reportes.partials.exportar', [
        'action' => route('admin.reportes.rendimiento.exportar', $aula->id_asignacion),
        'exportValues' => [
            'filtro' => $filtros['filtro'] ?? 'semana',
            'tipo_periodo' => $filtros['tipo_periodo'] ?? 'bimestre',
            'id_periodo' => $filtros['id_periodo'] ?? '',
            'fecha_inicio' => $filtros['fecha_inicio'] ?? '',
            'fecha_fin' => $filtros['fecha_fin'] ?? '',
            'id_estudiante' => $filtros['id_estudiante'] ?? '',
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

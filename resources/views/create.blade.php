@extends('layouts.admin')

@section('title', $isEdit ? 'Editar Tarea' : 'Crear Tarea')

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">{{ $isEdit ? 'Editar Tarea' : 'Crear Nueva Tarea' }}</h1>
            <p class="text-muted mb-0">{{ $datosAula->grado }} - {{ $datosAula->seccion }}</p>
        </div>
        <a href="{{ route('admin.control-aulas', $id) }}#tareas" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" class="me-1"></i> Volver
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ $isEdit ? route('admin.tareas.update', $tarea->id_tarea) : route('admin.tareas.store', $id) }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
            <input type="hidden" name="alcance" value="{{ $alcance }}">
        @endif

        <div class="row g-4">
            <div class="{{ $isEdit ? 'col-lg-8' : 'col-lg-7' }}">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="card-title mb-0">Información de la Tarea</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="titulo">Título <span class="text-danger">*</span></label>
                            <input id="titulo" type="text" class="form-control @error('titulo') is-invalid @enderror" name="titulo" value="{{ old('titulo', $tarea->titulo ?? '') }}" maxlength="150" required>
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="descripcion">Descripción</label>
                            <textarea id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" rows="4">{{ old('descripcion', $tarea->descripcion ?? '') }}</textarea>
                            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="tipo_evaluacion">Tipo de Evaluación <span class="text-danger">*</span></label>
                                <select id="tipo_evaluacion" class="form-select @error('tipo_evaluacion') is-invalid @enderror" name="tipo_evaluacion" required>
                                    <option value="Cualitativa" {{ old('tipo_evaluacion', $tarea->tipo_evaluacion ?? 'Cualitativa') === 'Cualitativa' ? 'selected' : '' }}>Cualitativa</option>
                                    <option value="Cuantitativa" {{ old('tipo_evaluacion', $tarea->tipo_evaluacion ?? '') === 'Cuantitativa' ? 'selected' : '' }}>Cuantitativa</option>
                                </select>
                                @error('tipo_evaluacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="id_periodo">Periodo <span class="text-danger">*</span></label>
                                <select id="id_periodo" class="form-select @error('id_periodo') is-invalid @enderror" name="id_periodo" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($periodos as $periodo)
                                        <option value="{{ $periodo->id_periodo }}" {{ (string) old('id_periodo', $tarea->id_periodo ?? '') === (string) $periodo->id_periodo ? 'selected' : '' }}>{{ $periodo->nombre_periodo }}</option>
                                    @endforeach
                                </select>
                                @error('id_periodo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isEdit)
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h5 class="card-title mb-0">Fechas de esta aula</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="fecha_asignacion" class="form-label">Fecha de inicio</label>
                                <input id="fecha_asignacion" type="date" class="form-control" name="fecha_asignacion" value="{{ old('fecha_asignacion', $tarea->fecha_asignacion->format('Y-m-d')) }}" required>
                            </div>
                            <div>
                                <label for="fecha_entrega" class="form-label">Fecha de cierre</label>
                                <input id="fecha_entrega" type="date" class="form-control" name="fecha_entrega" value="{{ old('fecha_entrega', $tarea->fecha_entrega->format('Y-m-d')) }}" required>
                            </div>
                            @if($alcance === 'todas')
                                <p class="small text-muted mt-3 mb-0">El contenido se actualizará en todas las aulas del grupo; estas fechas solo se actualizarán para esta aula.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h5 class="card-title mb-0">Asignar a Aulas</h5></div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Selecciona una o más aulas. Cada aula puede tener sus propias fechas.</p>
                            <div id="aulas-container">
                                @forelse($aulasAsignadas as $index => $aula)
                                    @php
                                        $checked = (string) old("aulas.$index.id_asignacion", $aula->id_asignacion == $datosAula->id_asignacion ? $aula->id_asignacion : '') === (string) $aula->id_asignacion;
                                    @endphp
                                    <div class="aula-item mb-3 p-3 border rounded {{ $checked ? 'bg-light border-primary' : '' }}">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input aula-checkbox" type="checkbox" value="{{ $aula->id_asignacion }}" id="aula_{{ $aula->id_asignacion }}" name="aulas[{{ $index }}][id_asignacion]" {{ $checked ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="aula_{{ $aula->id_asignacion }}">{{ $aula->grado }} - {{ $aula->seccion }} <span class="text-muted fw-normal">({{ $aula->nivel }})</span></label>
                                        </div>
                                        <div class="aula-fechas {{ $checked ? '' : 'd-none' }}">
                                            <div class="mb-2">
                                                <label class="form-label small text-muted" for="inicio_{{ $aula->id_asignacion }}">Fecha de inicio</label>
                                                <input id="inicio_{{ $aula->id_asignacion }}" type="date" class="form-control form-control-sm fecha-asignacion" name="aulas[{{ $index }}][fecha_asignacion]" value="{{ old("aulas.$index.fecha_asignacion", now()->format('Y-m-d')) }}" {{ $checked ? 'required' : 'disabled' }}>
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted" for="cierre_{{ $aula->id_asignacion }}">Fecha de cierre</label>
                                                <input id="cierre_{{ $aula->id_asignacion }}" type="date" class="form-control form-control-sm fecha-entrega" name="aulas[{{ $index }}][fecha_entrega]" value="{{ old("aulas.$index.fecha_entrega") }}" {{ $checked ? 'required' : 'disabled' }}>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No hay aulas asignadas para el año escolar actual.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">
                <i data-feather="save" class="me-1"></i> {{ $isEdit ? 'Guardar Cambios' : 'Guardar Tarea' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.aula-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const item = this.closest('.aula-item');
            const fechas = item.querySelector('.aula-fechas');
            item.classList.toggle('bg-light', this.checked);
            item.classList.toggle('border-primary', this.checked);
            fechas.classList.toggle('d-none', !this.checked);
            fechas.querySelectorAll('input').forEach(function (input) {
                input.disabled = !checkbox.checked;
                input.required = checkbox.checked;
            });
        });
    });
});
</script>
@endpush

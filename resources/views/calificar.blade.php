@extends('layouts.admin')

@section('title', 'Calificar Tarea')

@section('contenido')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center mb-4">
        
        <div>
            <h1 class="h4 mb-1">Calificando: {{ $tarea->titulo }}</h1>
            <small class="text-muted">{{ $datosAula->grado }} - {{ $datosAula->seccion }} | {{ $tarea->tipo_evaluacion }} | Cierre: {{ $tarea->fecha_entrega->format('d/m/Y') }}</small>
        </div>
        <a href="{{ route('admin.control-aulas', $tarea->id_asignacion) }}#tareas" class="btn-return-hover d-inline-flex align-items-center my-3 ms-auto text-dark text-decoration-none">
            <i data-feather="chevron-left" class="me-1" style="width: 20px; height: 20px;"></i>
            <span class="o_stat_text fw-medium">Volver</span>
        </a>   
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="form-calificaciones" action="{{ route('admin.tareas.guardarCalificacion', $tarea->id_tarea) }}" method="POST">
        @csrf
        <div class="card flex-fill">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Registro de Calificaciones</h5>
                <button type="button" id="btn-marcar-todos" class="btn btn-sm btn-outline-success">
                    <i data-feather="check-square" class="me-1" style="width:14px;height:14px;"></i> Marcar todos como Cumplió
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered my-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:5%;">N°</th>
                            <th style="width:30%;">Apellidos y Nombres</th>
                            <th class="text-center" style="width:12%;">Cumplió</th>
                            <th class="text-center" style="width:14%;">No Cumplió</th>
                            <th class="text-center" style="width:16%;">Nota</th>
                            <th style="width:23%;">Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estudiantes as $indice => $estudiante)
                            @php
                                $claveCalificacion = 'calificaciones.' . $estudiante->id_matricula;
                                $cumplio = old($claveCalificacion . '.cumplio', $estudiante->cumplio);
                                $notaHabilitada = (string) $cumplio === '1';
                            @endphp
                            <tr class="student-row">
                                <td class="text-center fw-bold text-muted">{{ $indice + 1 }}</td>
                                <td class="fw-semibold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                <td class="text-center">
                                    <input type="radio" class="btn-check estado-cumplio" name="calificaciones[{{ $estudiante->id_matricula }}][cumplio]" id="cumplio_{{ $estudiante->id_matricula }}" value="1" required aria-label="Cumplió: {{ $estudiante->nombres }}" {{ $notaHabilitada ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-success rounded-pill px-3" for="cumplio_{{ $estudiante->id_matricula }}"><i data-feather="check" aria-hidden="true"></i></label>
                                </td>
                                <td class="text-center">
                                    <input type="radio" class="btn-check estado-no-cumplio" name="calificaciones[{{ $estudiante->id_matricula }}][cumplio]" id="no_cumplio_{{ $estudiante->id_matricula }}" value="0" aria-label="No cumplió: {{ $estudiante->nombres }}" {{ !is_null($cumplio) && (string) $cumplio === '0' ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-danger rounded-pill px-3" for="no_cumplio_{{ $estudiante->id_matricula }}"><i data-feather="x" aria-hidden="true"></i></label>
                                </td>
                                <td>
                                    @if($tarea->tipo_evaluacion === 'Cuantitativa')
                                        <input type="number" class="form-control form-control-sm input-nota text-center" name="calificaciones[{{ $estudiante->id_matricula }}][nota_cuantitativa]" min="0" max="20" step="0.1" value="{{ old($claveCalificacion . '.nota_cuantitativa', $estudiante->nota_cuantitativa) }}" {{ $notaHabilitada ? '' : 'disabled' }} aria-label="Nota de {{ $estudiante->nombres }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm input-nota input-nota-cualitativa text-center" name="calificaciones[{{ $estudiante->id_matricula }}][nota_cualitativa]" maxlength="2" oninput="this.value = this.value.toUpperCase()" value="{{ old($claveCalificacion . '.nota_cualitativa', $estudiante->nota_cualitativa) }}" aria-label="Nota de {{ $estudiante->nombres }}">
                                    @endif
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="calificaciones[{{ $estudiante->id_matricula }}][observacion]" value="{{ old($claveCalificacion . '.observacion', $estudiante->observacion) }}" maxlength="1000" aria-label="Comentario de {{ $estudiante->nombres }}" placeholder="Opcional">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay estudiantes matriculados en esta sección.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i data-feather="save" class="me-2" style="width:16px;height:16px;"></i> Guardar Calificaciones
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formCalificaciones = document.getElementById('form-calificaciones');
    if (formCalificaciones) {
        formCalificaciones.addEventListener('submit', function (event) {
            if (formCalificaciones.dataset.confirmed === 'true') return;

            event.preventDefault();

            if (window.Swal) {
                window.Swal.fire({
                    title: '¿Está seguro de estos cambios?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        formCalificaciones.dataset.confirmed = 'true';
                        formCalificaciones.requestSubmit();
                    }
                });
            } else if (window.confirm('¿Está seguro de estos cambios?')) {
                formCalificaciones.dataset.confirmed = 'true';
                formCalificaciones.requestSubmit();
            }
        });
    }

    function actualizarNota(row) {
        const cumplio = row.querySelector('.estado-cumplio').checked;
        const nota = row.querySelector('.input-nota');
        if (nota.classList.contains('input-nota-cualitativa')) return;

        nota.disabled = !cumplio;
        if (!cumplio) nota.value = '';
    }

    document.querySelectorAll('.student-row').forEach(function (row) {
        row.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                actualizarNota(row);
            });
        });
    });

    const marcarTodos = document.getElementById('btn-marcar-todos');
    if (marcarTodos) {
        marcarTodos.addEventListener('click', function () {
            document.querySelectorAll('.student-row').forEach(function (row) {
                row.querySelector('.estado-cumplio').checked = true;
                actualizarNota(row);
            });
        });
    }
});
</script>
@endpush

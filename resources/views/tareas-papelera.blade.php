@extends('layouts.admin')

@section('title', 'Papelera de Tareas')

@section('contenido')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Papelera de Tareas</h1>
            <p class="text-muted mb-0">{{ $datosAula->grado }} - {{ $datosAula->seccion }}</p>
        </div>
        <a href="{{ route('admin.control-aulas', $datosAula->id_asignacion) }}#tareas" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" class="me-1"></i> Volver a Tareas
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        @forelse($tareas as $tarea)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $tarea->titulo }}</h5>
                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($tarea->descripcion ?: 'Sin descripción.', 140) }}</p>
                        <small class="text-muted">Eliminada: {{ $tarea->deleted_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-end">
                        <form action="{{ route('admin.tareas.restore', $tarea->id_tarea) }}" method="POST" data-alcance-global="{{ $tarea->grupo_tarea ? 'true' : 'false' }}" class="js-scope-restore">
                            @csrf
                            <input type="hidden" name="id_asignacion" value="{{ $datosAula->id_asignacion }}">
                            <input type="hidden" name="alcance" value="">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i data-feather="rotate-ccw" class="me-1" style="width:14px;height:14px;"></i> Restaurar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i data-feather="trash-2" class="text-muted mb-3" style="width:40px;height:40px;"></i>
                        <h5 class="text-muted">La papelera está vacía</h5>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.js-scope-restore').forEach(function (form) {
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        let alcance = null;

        if (window.Swal) {
            if (form.dataset.alcanceGlobal !== 'true') {
                const resultado = await Swal.fire({
                    title: 'Esta tarea solo pertenece a esta aula',
                    text: 'No hay información para restaurarla en otras aulas.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Restaurar en esta aula',
                    cancelButtonText: 'Cancelar'
                });
                if (resultado.isConfirmed) alcance = 'aula';
            } else {
                const resultado = await Swal.fire({
                    title: '¿Qué tareas restaurar?',
                    input: 'radio',
                    inputOptions: {
                        aula: 'Solo esta aula',
                        todas: 'Todas las aulas donde se asignó esta tarea'
                    },
                    inputValidator: (value) => value ? undefined : 'Selecciona el alcance.',
                    showCancelButton: true,
                    confirmButtonText: 'Restaurar',
                    cancelButtonText: 'Cancelar'
                });
                if (resultado.isConfirmed) alcance = resultado.value;
            }
        } else {
            if (form.dataset.alcanceGlobal !== 'true') {
                alcance = window.confirm('Esta tarea solo pertenece a esta aula. ¿Deseas restaurarla?') ? 'aula' : null;
            } else {
                const eleccion = window.prompt('Escribe 1 para solo esta aula o 2 para todas las aulas.');
                alcance = eleccion === '1' ? 'aula' : eleccion === '2' ? 'todas' : null;
            }
        }

        if (alcance) {
            form.querySelector('[name="alcance"]').value = alcance;
            form.submit();
        }
    });
});
</script>
@endpush

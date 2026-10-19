@extends('layouts.admin')

@section('title', 'Control de Aula')

@section('contenido')
<div class="container-fluid p-0">
    <!-- ENCABEZADO (Intacto) -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 d-inline align-middle">
                Control de Aula: {{ $datosAula->grado }} - {{ $datosAula->seccion }} 
            </h1>
        </div>
        <a href="{{ route('admin.profile', $datosAula->id_asignacion) }}" class="btn-return-hover d-inline-flex align-items-center my-3 text-dark text-decoration-none">
            <i data-feather="chevron-left" class="text-dark me-1" style="width: 20px; height: 20px;"></i>
            <span class="o_stat_text fw-medium">Volver</span>
        </a>
    </div>

    <!-- NAVEGACIÓN PRINCIPAL DEL AULA (TABS) -->
    <ul class="nav nav-tabs mb-3" id="aulaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="asistencia-tab" data-bs-toggle="tab" data-bs-target="#asistencia" type="button" role="tab" aria-controls="asistencia" aria-selected="true">
                <i data-feather="check-square" class="me-1" style="width: 16px; height: 16px;"></i> Asistencia
            </button>
        </li>
        <!--
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="datos-estudiantes-tab" data-bs-toggle="tab" data-bs-target="#datos-estudiantes" type="button" role="tab" aria-controls="datos-estudiantes" aria-selected="false">
                <i data-feather="users" class="me-1" style="width: 16px; height: 16px;"></i> Datos de Estudiantes
            </button>
        </li>
        -->
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tareas-tab" data-bs-toggle="tab" data-bs-target="#tareas" type="button" role="tab" aria-controls="tareas" aria-selected="false">
                <i data-feather="book" class="me-1" style="width: 16px; height: 16px;"></i> Tareas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="reportes-tab" data-bs-toggle="tab" data-bs-target="#reportes" type="button" role="tab" aria-controls="reportes" aria-selected="false">
                <i data-feather="file-text" class="me-1" style="width: 16px; height: 16px;"></i> Reportes
            </button>
        </li>
        <!--
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="estadisticas-tab" data-bs-toggle="tab" data-bs-target="#estadisticas" type="button" role="tab" aria-controls="estadisticas" aria-selected="false">
                <i data-feather="bar-chart-2" class="me-1" style="width: 16px; height: 16px;"></i> Estadísticas
            </button>
        </li>
        -->
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content" id="aulaTabsContent">
        
        <!-- PESTAÑA 1: ASISTENCIA (Activa por defecto) -->
        <div class="tab-pane fade show active" id="asistencia" role="tabpanel" aria-labelledby="asistencia-tab">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Registro de Asistencia Diaria</h5>
                    <span class="badge bg-primary">Fecha: {{ date('d/m/Y') }}</span>
                </div>
                
                <form action="{{ route('admin.guardar.asistencia') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_asignacion" value="{{ $datosAula->id_asignacion }}">
                    <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered my-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 5%;">#</th>
                                    <th style="width: 35%;">Apellidos y Nombres</th>
                                    <!-- <th class="d-none d-md-table-cell" style="width: 15%;">Estado Matrícula</th> -->
                                    <th class="text-center" style="width: 10%;">Asistió</th>
                                    <th class="text-center" style="width: 10%;">Tardanza</th>
                                    <th class="text-center" style="width: 10%;">Falta</th>
                                    <th class="text-center" style="width: 15%;">Justificado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($estudiantes->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i data-feather="inbox" class="me-2"></i>No hay estudiantes matriculados en esta sección.
                                        </td>
                                    </tr>
                                @else
                                    @foreach($estudiantes as $indice => $estudiante)
                                        <tr>
                                            <td class="text-center fw-bold text-muted">{{ $indice + 1 }}</td>
                                            <td class="fw-semibold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                            <!-- <td class="d-none d-md-table-cell">
                                                @php
                                                    $badgeClass = 'bg-success';
                                                    if($estudiante->estado_matricula == 'Suspendido') $badgeClass = 'bg-danger';
                                                    if($estudiante->estado_matricula == 'Retirado' || $estudiante->estado_matricula == 'Trasladado') $badgeClass = 'bg-warning text-dark';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $estudiante->estado_matricula }}</span>
                                            </td> -->
                                            
                                            <!-- UI DE MARCADO: Radio Buttons estilizados como botones con iconos -->
                                            <td class="text-center">
                                                <input type="radio" class="btn-check" name="asistencia[{{ $estudiante->id_estudiante }}]" id="asistio_{{ $estudiante->id_estudiante }}" value="Asistió" autocomplete="off" checked>
                                                <label class="btn btn-sm btn-outline-success rounded-pill px-3" for="asistio_{{ $estudiante->id_estudiante }}" title="Marcar como Asistió">
                                                    <i data-feather="check" style="width: 16px; height: 16px;"></i>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" class="btn-check" name="asistencia[{{ $estudiante->id_estudiante }}]" id="tardanza_{{ $estudiante->id_estudiante }}" value="Tardanza" autocomplete="off">
                                                <label class="btn btn-sm btn-outline-warning rounded-pill px-3" for="tardanza_{{ $estudiante->id_estudiante }}" title="Marcar como Tardanza">
                                                    <i data-feather="clock" style="width: 16px; height: 16px;"></i>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" class="btn-check" name="asistencia[{{ $estudiante->id_estudiante }}]" id="falto_{{ $estudiante->id_estudiante }}" value="Faltó" autocomplete="off">
                                                <label class="btn btn-sm btn-outline-danger rounded-pill px-3" for="falto_{{ $estudiante->id_estudiante }}" title="Marcar como Faltó">
                                                    <i data-feather="x" style="width: 16px; height: 16px;"></i>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" class="btn-check" name="asistencia[{{ $estudiante->id_estudiante }}]" id="justifico_{{ $estudiante->id_estudiante }}" value="Justificado" autocomplete="off">
                                                <label class="btn btn-sm btn-outline-info rounded-pill px-3" for="justifico_{{ $estudiante->id_estudiante }}" title="Marcar como Justificado">
                                                    <i data-feather="file-text" style="width: 16px; height: 16px;"></i>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Botón de Guardado en la parte inferior del card -->
                    <div class="card-footer bg-light d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i data-feather="save" class="me-2" style="width: 18px; height: 18px;"></i> Guardar Asistencia
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PESTAÑA 2: DATOS DE ESTUDIANTES (NUEVA) - contenido temporalmente oculto
        <div class="tab-pane fade" id="datos-estudiantes" role="tabpanel" aria-labelledby="datos-estudiantes-tab">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información Detallada de Estudiantes</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover my-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <!-- <th style="width: 15%;">N° Matrícula</th> -->
                                <th style="width: 10%;">DNI</th>
                                <th style="width: 35%;">Apellidos y Nombres</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Estado</th>
                                <th class="d-none d-lg-table-cell" style="width: 20%;">Apoderado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($estudiantes->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i data-feather="inbox" class="me-2"></i>No hay estudiantes matriculados en esta sección.
                                    </td>
                                </tr>
                            @else
                                @foreach($estudiantes as $indice => $estudiante)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $indice + 1 }}</td>
                                        <!-- <td>
                                            <span class="badge bg-secondary">{{ $estudiante->id_matricula ?? 'N/A' }}</span>
                                        </td> -->
                                        <td>{{ $estudiante->dni }}</td>
                                        <td class="fw-semibold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                        <td class="d-none d-md-table-cell">
                                            @php
                                                $badgeClass = 'bg-success';
                                                if($estudiante->estado_matricula == 'Suspendido') $badgeClass = 'bg-danger';
                                                if($estudiante->estado_matricula == 'Retirado' || $estudiante->estado_matricula == 'Trasladado') $badgeClass = 'bg-warning text-dark';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $estudiante->estado_matricula }}</span>
                                        </td>
                                        <td class="d-none d-lg-table-cell text-muted">
                                            <small>
                                                <i data-feather="user" style="width: 14px; height: 14px;"></i>
                                                Por definir
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        --}}

        <!-- PESTAÑA 3: TAREAS -->
        <div class="tab-pane fade" id="tareas" role="tabpanel" aria-labelledby="tareas-tab">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Tareas de {{ $datosAula->grado }} - {{ $datosAula->seccion }}</h5>
                    <p class="text-muted small mb-0">Selecciona una tarea para registrar el avance de tus estudiantes.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.tareas.papelera', $datosAula->id_asignacion) }}" class="btn btn-outline-secondary">
                        <i data-feather="trash-2" class="me-1" style="width:16px;height:16px;"></i> Papelera
                    </a>
                    <a href="{{ route('admin.tareas.create', $datosAula->id_asignacion) }}" class="btn btn-primary">
                        <i data-feather="plus" class="me-1" style="width:16px;height:16px;"></i> Nueva Tarea
                    </a>
                </div>
            </div>

            <div class="row g-3">
                @forelse($tareas as $tarea)
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 shadow-sm task-card">
                            <div class="card-body position-relative">
                                <h5 class="card-title mb-2">{{ $tarea->titulo }}</h5>
                                <p class="card-text text-muted mb-3">{{ \Illuminate\Support\Str::limit($tarea->descripcion ?: 'Sin descripción.', 140) }}</p>
                                <div class="small text-muted">
                                    <div class="mb-1"><strong>Creada:</strong> {{ $tarea->created_at ? \Carbon\Carbon::parse($tarea->created_at)->format('d/m/Y') : '—' }}</div>
                                    <div><strong>Cierre:</strong> {{ $tarea->fecha_entrega ? \Carbon\Carbon::parse($tarea->fecha_entrega)->format('d/m/Y') : '—' }}</div>
                                </div>
                                <a href="{{ route('admin.tareas.calificar', $tarea->id_tarea) }}" class="stretched-link" aria-label="Calificar {{ $tarea->titulo }}"></a>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-end gap-2 position-relative" style="z-index:2;">
                                <a href="{{ route('admin.tareas.edit', ['tarea' => $tarea->id_tarea, 'alcance' => 'aula']) }}" data-alcance-global="{{ $tarea->grupo_tarea ? 'true' : 'false' }}" class="btn btn-sm btn-outline-primary js-scope-edit">
                                    <i data-feather="edit-2" class="me-1" style="width:14px;height:14px;"></i> Editar
                                </a>
                                <form action="{{ route('admin.tareas.destroy', $tarea->id_tarea) }}" method="POST" data-alcance-global="{{ $tarea->grupo_tarea ? 'true' : 'false' }}" class="js-scope-delete">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="alcance" value="">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i data-feather="trash-2" class="me-1" style="width:14px;height:14px;"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i data-feather="inbox" class="text-muted mb-3" style="width:40px;height:40px;"></i>
                                <h5 class="text-muted">Aún no hay tareas</h5>
                                <p class="text-muted mb-0">Crea una tarea para esta aula o asígnala a varias aulas a la vez.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PESTAÑA 4: REPORTES -->
        <div class="tab-pane fade" id="reportes" role="tabpanel" aria-labelledby="reportes-tab">
            <div class="mb-4">
                <h5 class="mb-1">Reportes de {{ $datosAula->grado }} - {{ $datosAula->seccion }}</h5>
                <p class="text-muted mb-0">Elige qué información necesitas consultar.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-4">
                    <a class="card h-100 shadow-sm report-card text-decoration-none" href="{{ route('admin.reportes.asistencia', $datosAula->id_asignacion) }}">
                        <div class="card-body">
                            <i data-feather="check-square" class="text-success mb-3 report-card-icon"></i>
                            <h5 class="card-title text-dark">Reporte de asistencias</h5>
                            <p class="card-text text-muted mb-0">Revisa la asistencia por fecha o por un rango de días.</p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 text-primary fw-semibold">Abrir reporte <i data-feather="arrow-right" class="ms-1 report-card-arrow"></i></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a class="card h-100 shadow-sm report-card text-decoration-none" href="{{ route('admin.reportes.tareas', $datosAula->id_asignacion) }}">
                        <div class="card-body">
                            <i data-feather="book-open" class="text-primary mb-3 report-card-icon"></i>
                            <h5 class="card-title text-dark">Reporte de tareas</h5>
                            <p class="card-text text-muted mb-0">Consulta entregas y notas de todas las tareas o de algunas.</p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 text-primary fw-semibold">Abrir reporte <i data-feather="arrow-right" class="ms-1 report-card-arrow"></i></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a class="card h-100 shadow-sm report-card text-decoration-none" href="{{ route('admin.reportes.rendimiento', $datosAula->id_asignacion) }}">
                        <div class="card-body">
                            <i data-feather="bar-chart-2" class="text-warning mb-3 report-card-icon"></i>
                            <h5 class="card-title text-dark">Rendimiento</h5>
                            <p class="card-text text-muted mb-0">Observa cómo avanzan el salón o un estudiante.</p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 text-primary fw-semibold">Ver gráficos <i data-feather="arrow-right" class="ms-1 report-card-arrow"></i></div>
                    </a>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 5: ESTADÍSTICAS (Placeholder) - contenido temporalmente oculto
        <div class="tab-pane fade" id="estadisticas" role="tabpanel" aria-labelledby="estadisticas-tab">
            <div class="card flex-fill">
                <div class="card-body text-center py-5">
                    <i data-feather="bar-chart-2" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                    <h5 class="text-muted">Estadísticas y Métricas</h5>
                    <p class="text-muted small">Contenido en desarrollo. Aquí se visualizarán gráficos de rendimiento y asistencia.</p>
                </div>
            </div>
        </div>
        -->

    </div> <!-- Fin tab-content -->
</div>
@endsection

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/reportes.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function elegirAlcance(alcanceGlobal) {
        if (window.Swal) {
            if (!alcanceGlobal) {
                const resultado = await Swal.fire({
                    title: 'Esta tarea solo pertenece a esta aula',
                    text: 'No hay información para aplicar este cambio a otras aulas.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar solo en esta aula',
                    cancelButtonText: 'Cancelar'
                });
                return resultado.isConfirmed ? 'aula' : null;
            }

            const resultado = await Swal.fire({
                title: '¿Dónde aplicar el cambio?',
                input: 'radio',
                inputOptions: {
                    aula: 'Solo a esta aula',
                    todas: 'A todas las aulas donde se asignó esta tarea'
                },
                inputValidator: (value) => value ? undefined : 'Selecciona el alcance.',
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            });
            return resultado.isConfirmed ? resultado.value : null;
        }

        if (!alcanceGlobal) {
            return window.confirm('Esta tarea solo pertenece a esta aula. ¿Deseas continuar?') ? 'aula' : null;
        }
        const eleccion = window.prompt('Escribe 1 para solo esta aula o 2 para todas las aulas. Cancela para no continuar.');
        return eleccion === '1' ? 'aula' : eleccion === '2' ? 'todas' : null;
    }

    document.querySelectorAll('.js-scope-edit').forEach(function (link) {
        link.addEventListener('click', async function (event) {
            event.preventDefault();
            const alcance = await elegirAlcance(this.dataset.alcanceGlobal === 'true');
            if (alcance) {
                const url = new URL(this.href);
                url.searchParams.set('alcance', alcance);
                window.location.assign(url.toString());
            }
        });
    });

    document.querySelectorAll('.js-scope-delete').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const alcance = await elegirAlcance(form.dataset.alcanceGlobal === 'true');
            if (!alcance) return;

            const confirmado = window.Swal
                ? await Swal.fire({
                    title: '¿Enviar la tarea a la papelera?',
                    text: 'La tarea podrá restaurarse después.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((resultado) => resultado.isConfirmed)
                : window.confirm('¿Enviar la tarea a la papelera?');

            if (confirmado) {
                form.querySelector('[name="alcance"]').value = alcance;
                form.submit();
            }
        });
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            feather.replace();
        });
    });

</script>
@endpush
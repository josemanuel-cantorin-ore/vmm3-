@extends('layouts.admin')

@section('title', 'Mis Aulas')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/aulas.css') }}?v={{ filemtime(public_path('css/aulas.css')) }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 d-inline align-middle">Aulas Asignadas</h1>
            <p class="text-muted mb-0 mt-1">Selecciona un aula para gestionar notas y asistencias.</p>
        </div>
    </div>

    <div class="row">
        <!-- Verificación de Aulas Vacías -->
        @if($aulas->isEmpty())
            <div class="col-12 text-center py-5">
                <i data-feather="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                <h4 class="text-muted">No tienes aulas asignadas actualmente.</h4>
            </div>
        @else
            @php
                $colores = ['aula-azul', 'aula-verde', 'aula-naranja', 'aula-purpura', 'aula-rojo', 'aula-teal'];
            @endphp

            <!-- Iteración de Aulas conectadas a la BD -->
            @foreach($aulas as $index => $aula)
                @php
                    $colorClase = $colores[$index % count($colores)];
                @endphp

                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('admin.control-aulas', ['id' => $aula->id_asignacion]) }}" class="text-decoration-none d-block h-100">
                        <div class="card h-100 aula-card mb-0 aula-theme-card {{ $colorClase }} border-0 shadow-sm">
                            <div class="aula-card-top-bar"></div>
                            
                            <div class="card-body d-flex flex-column p-4">
                                
                                <!-- 1. ENCABEZADO: Icono + Etiqueta de Nivel (Esquina superior derecha) -->
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="p-2 bg-light rounded-3 text-secondary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i data-feather="users" style="width: 22px; height: 22px;"></i>
                                    </div>
                                    <span class="badge bg-light text-secondary border px-2 py-1 fw-normal">
                                        {{ $aula->nivel }}
                                    </span>
                                </div>

                                <!-- 2. TÍTULO PRINCIPAL: Grado y Sección (Jerarquía dominante) -->
                                <h3 class="h3 fw-extrabold text-dark mb-3">
                                    {{ $aula->grado }} - {{ $aula->seccion }}
                                </h3>

                                <!-- 3. CAJA DE ESTADÍSTICAS UNIFICADA (Fondo gris inferior) -->
                                <div class="bg-light p-3 rounded-3 mt-auto d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted fw-bold mb-1 small" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            Total Alumnos
                                        </p>
                                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                            <i data-feather="user" class="me-2 text-secondary" style="width: 20px; height: 20px;"></i>
                                            {{ $aula->total_alumnos }}
                                        </h4>
                                    </div>
                                    
                                    <!-- Flecha sutil que invita al clic -->
                                    <i data-feather="arrow-right" class="text-muted" style="width: 22px; height: 22px;"></i>
                                </div>

                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/aulas.js') }}?v={{ filemtime(public_path('js/aulas.js')) }}"></script>
@endpush
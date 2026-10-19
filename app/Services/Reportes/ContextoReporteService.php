<?php

namespace App\Services\Reportes;

use App\Models\AsignacionDocente;
use App\Models\Matricula;
use Illuminate\Database\Eloquent\Collection;

class ContextoReporteService
{
    public function asignacionPropia(int $idAsignacion): AsignacionDocente
    {
        return AsignacionDocente::query()
            ->with(['gradoSeccion', 'profesor'])
            ->where('id_usuario', auth()->user()->id_usuario)
            ->findOrFail($idAsignacion);
    }

    public function matriculas(AsignacionDocente $asignacion): Collection
    {
        return Matricula::query()
            ->with('estudiante:id_estudiante,nombres,apellidos')
            ->where('id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('anio_escolar', $asignacion->anio_escolar)
            ->orderBy('id_matricula')
            ->get(['id_matricula', 'id_estudiante', 'id_grado_seccion', 'anio_escolar']);
    }
}

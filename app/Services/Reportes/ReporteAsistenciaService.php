<?php

namespace App\Services\Reportes;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaAula;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReporteAsistenciaService
{
    public function vistaPrevia(AsignacionDocente $asignacion, array $filtros): LengthAwarePaginator
    {
        $consulta = AsistenciaAula::query()
            ->with(['matricula.estudiante:id_estudiante,nombres,apellidos'])
            ->where('id_asignacion', $asignacion->id_asignacion);

        if ($filtros['modo_periodo'] === 'fecha') {
            $consulta->whereDate('fecha', $filtros['fecha']);
        } else {
            $consulta->whereDate('fecha', '>=', $filtros['fecha_inicio'])
                ->whereDate('fecha', '<=', $filtros['fecha_fin']);
        }

        if (! empty($filtros['buscar_estudiante'])) {
            $termino = trim($filtros['buscar_estudiante']);
            $consulta->whereHas('matricula.estudiante', function ($estudiantes) use ($termino) {
                $estudiantes->where(function ($nombres) use ($termino) {
                    $nombres->where('nombres', 'ilike', "%{$termino}%")
                        ->orWhere('apellidos', 'ilike', "%{$termino}%");
                });
            });
        }

        $registros = $consulta
            ->orderByDesc('fecha')
            ->orderBy('id_matricula')
            ->paginate(30)
            ->withQueryString();

        $registros->getCollection()->transform(function (AsistenciaAula $registro) {
            $registro->nombre_estudiante = trim(
                ($registro->matricula->estudiante->apellidos ?? '') . ', ' .
                ($registro->matricula->estudiante->nombres ?? '')
            );
            $registro->estado_reporte = $registro->estado === 'Faltó' ? 'Falta' : $registro->estado;
            $registro->clase_estado_reporte = match ($registro->estado) {
                'Asistió' => 'bg-success',
                'Tardanza' => 'bg-warning text-dark',
                'Faltó', 'Falta' => 'bg-danger',
                'Justificado' => 'bg-info text-dark',
                default => 'bg-secondary',
            };

            return $registro;
        });

        return $registros;
    }
}

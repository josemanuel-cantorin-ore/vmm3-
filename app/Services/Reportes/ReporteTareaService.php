<?php

namespace App\Services\Reportes;

use App\Models\AsignacionDocente;
use App\Models\Tarea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReporteTareaService
{
    public function tareasDisponibles(AsignacionDocente $asignacion, ?string $buscar = null): Collection
    {
        return Tarea::query()
            ->with('periodo:id_periodo,nombre_periodo')
            ->where('id_asignacion', $asignacion->id_asignacion)
            ->when($buscar, function ($consulta, $termino) {
                $consulta->where('titulo', 'ilike', '%' . trim($termino) . '%');
            })
            ->orderByDesc('fecha_entrega')
            ->get(['id_tarea', 'id_asignacion', 'id_periodo', 'titulo', 'tipo_evaluacion', 'fecha_entrega']);
    }

    public function vistaPrevia(AsignacionDocente $asignacion, array $filtros): LengthAwarePaginator
    {
        $tareas = Tarea::query()
            ->where('id_asignacion', $asignacion->id_asignacion)
            ->when($filtros['alcance'] === 'seleccionadas', function ($consulta) use ($filtros) {
                $consulta->whereIn('id_tarea', $filtros['tareas']);
            })
            ->when(! empty($filtros['buscar_tarea']), function ($consulta) use ($filtros) {
                $consulta->where('titulo', 'ilike', '%' . trim($filtros['buscar_tarea']) . '%');
            })
            ->select(['id_tarea', 'titulo', 'tipo_evaluacion', 'fecha_entrega']);

        $consulta = DB::table('matriculas as m')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 'm.id_estudiante')
            ->crossJoinSub($tareas, 't')
            ->leftJoin('registro_tareas as rt', function ($join) {
                $join->on('rt.id_tarea', '=', 't.id_tarea')
                    ->on('rt.id_matricula', '=', 'm.id_matricula');
            })
            ->where('m.id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('m.anio_escolar', $asignacion->anio_escolar)
            ->when(! empty($filtros['buscar_estudiante']), function ($query) use ($filtros) {
                $termino = '%' . trim($filtros['buscar_estudiante']) . '%';
                $query->where(function ($estudiantes) use ($termino) {
                    $estudiantes->where('e.nombres', 'ilike', $termino)
                        ->orWhere('e.apellidos', 'ilike', $termino);
                });
            })
            ->select([
                'm.id_matricula',
                'e.nombres',
                'e.apellidos',
                't.id_tarea',
                't.titulo',
                't.tipo_evaluacion',
                't.fecha_entrega',
                'rt.cumplio',
                'rt.nota_cualitativa',
                'rt.nota_cuantitativa',
            ])
            ->orderBy('e.apellidos')
            ->orderBy('e.nombres')
            ->orderBy('t.fecha_entrega');

        $registros = $consulta->paginate(30)->withQueryString();
        $registros->getCollection()->transform(function ($registro) {
            $entrego = in_array($registro->cumplio, [true, 1, '1', 't', 'true'], true);
            $registro->nombre_estudiante = trim($registro->apellidos . ', ' . $registro->nombres);
            $registro->entrego = $entrego;
            $registro->estado_entrega = $registro->cumplio === null
                ? 'Sin registro'
                : ($entrego ? 'Entregó' : 'No entregó');
            $registro->nota_reporte = $registro->tipo_evaluacion === 'Cualitativa'
                ? ($registro->nota_cualitativa ?: 'Sin nota')
                : ($registro->nota_cuantitativa !== null ? $registro->nota_cuantitativa . ' / 20' : 'Sin nota');

            return $registro;
        });

        return $registros;
    }
}

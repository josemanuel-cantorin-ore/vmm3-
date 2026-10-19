<?php

namespace App\Services\Reportes;

use App\Models\AsignacionDocente;
use App\Models\Periodo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RendimientoAulaService
{
    public function periodos(?string $tipo = null): Collection
    {
        return Periodo::query()
            ->when($tipo, fn ($consulta) => $consulta->where('tipo_periodo', $tipo))
            ->orderBy('fecha_inicio')
            ->get(['id_periodo', 'nombre_periodo', 'fecha_inicio', 'fecha_fin', 'tipo_periodo']);
    }

    public function rango(array $filtros): array
    {
        $zona = 'America/Lima';

        return match ($filtros['filtro']) {
            'mes' => [
                CarbonImmutable::now($zona)->startOfMonth(),
                CarbonImmutable::now($zona)->endOfMonth(),
                'Mes actual',
            ],
            'periodo' => $this->rangoPeriodo($filtros),
            'personalizado' => [
                CarbonImmutable::parse($filtros['fecha_inicio'], $zona)->startOfDay(),
                CarbonImmutable::parse($filtros['fecha_fin'], $zona)->endOfDay(),
                'Periodo personalizado',
            ],
            default => [
                CarbonImmutable::now($zona)->startOfWeek()->startOfDay(),
                CarbonImmutable::now($zona)->endOfWeek()->endOfDay(),
                'Semana actual',
            ],
        };
    }

    public function estadisticas(AsignacionDocente $asignacion, array $filtros): array
    {
        [$inicio, $fin, $etiqueta] = $this->rango($filtros);
        $idEstudiante = $filtros['id_estudiante'] ?? null;
        $baseAsistencia = DB::table('asistencia_aula as aa')
            ->join('matriculas as m', 'm.id_matricula', '=', 'aa.id_matricula')
            ->where('aa.id_asignacion', $asignacion->id_asignacion)
            ->where('m.id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('m.anio_escolar', $asignacion->anio_escolar)
            ->whereDate('aa.fecha', '>=', $inicio->toDateString())
            ->whereDate('aa.fecha', '<=', $fin->toDateString())
            ->when($idEstudiante, fn ($query) => $query->where('m.id_estudiante', $idEstudiante));

        $asistencia = (clone $baseAsistencia)
            ->selectRaw('aa.fecha, aa.estado, COUNT(*) as total')
            ->groupBy('aa.fecha', 'aa.estado')
            ->orderBy('aa.fecha')
            ->get()
            ->groupBy(fn ($registro) => CarbonImmutable::parse($registro->fecha, 'America/Lima')->toDateString());

        $baseTareas = DB::table('tareas as t')
            ->join('registro_tareas as rt', 'rt.id_tarea', '=', 't.id_tarea')
            ->join('matriculas as m', 'm.id_matricula', '=', 'rt.id_matricula')
            ->where('t.id_asignacion', $asignacion->id_asignacion)
            ->whereNull('t.deleted_at')
            ->where('m.id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('m.anio_escolar', $asignacion->anio_escolar)
            ->whereDate('t.fecha_entrega', '>=', $inicio->toDateString())
            ->whereDate('t.fecha_entrega', '<=', $fin->toDateString())
            ->whereDate('t.fecha_entrega', '<=', CarbonImmutable::now('America/Lima')->toDateString())
            ->when($idEstudiante, fn ($query) => $query->where('m.id_estudiante', $idEstudiante));

        $notas = (clone $baseTareas)
            ->selectRaw(
                "t.fecha_entrega as fecha, AVG(rt.nota_cuantitativa) as promedio_cuantitativo,
                SUM(CASE WHEN rt.nota_cualitativa = 'AD' THEN 1 ELSE 0 END) as nota_ad,
                SUM(CASE WHEN rt.nota_cualitativa = 'A' THEN 1 ELSE 0 END) as nota_a,
                SUM(CASE WHEN rt.nota_cualitativa = 'B' THEN 1 ELSE 0 END) as nota_b,
                SUM(CASE WHEN rt.nota_cualitativa = 'C' THEN 1 ELSE 0 END) as nota_c"
            )
            ->groupBy('t.fecha_entrega')
            ->orderBy('t.fecha_entrega')
            ->get()
            ->keyBy(fn ($registro) => CarbonImmutable::parse($registro->fecha, 'America/Lima')->toDateString());

        $totalEstudiantes = $idEstudiante
            ? 1
            : DB::table('matriculas')
                ->where('id_grado_seccion', $asignacion->id_grado_seccion)
                ->where('anio_escolar', $asignacion->anio_escolar)
                ->count();

        $tareasPorFecha = DB::table('tareas as t')
            ->where('t.id_asignacion', $asignacion->id_asignacion)
            ->whereNull('t.deleted_at')
            ->whereDate('t.fecha_entrega', '>=', $inicio->toDateString())
            ->whereDate('t.fecha_entrega', '<=', $fin->toDateString())
            ->whereDate('t.fecha_entrega', '<=', CarbonImmutable::now('America/Lima')->toDateString())
            ->selectRaw('t.fecha_entrega as fecha, COUNT(*) as total_tareas')
            ->groupBy('t.fecha_entrega')
            ->orderBy('t.fecha_entrega')
            ->get();

        $entregasPorFecha = DB::table('tareas as t')
            ->join('registro_tareas as rt', 'rt.id_tarea', '=', 't.id_tarea')
            ->join('matriculas as m', 'm.id_matricula', '=', 'rt.id_matricula')
            ->where('t.id_asignacion', $asignacion->id_asignacion)
            ->whereNull('t.deleted_at')
            ->where('m.id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('m.anio_escolar', $asignacion->anio_escolar)
            ->whereDate('t.fecha_entrega', '>=', $inicio->toDateString())
            ->whereDate('t.fecha_entrega', '<=', $fin->toDateString())
            ->whereDate('t.fecha_entrega', '<=', CarbonImmutable::now('America/Lima')->toDateString())
            ->when($idEstudiante, fn ($query) => $query->where('m.id_estudiante', $idEstudiante))
            ->selectRaw('t.fecha_entrega as fecha, SUM(CASE WHEN rt.cumplio IS TRUE THEN 1 ELSE 0 END) as cumplidas')
            ->groupBy('t.fecha_entrega')
            ->get()
            ->keyBy(fn ($registro) => CarbonImmutable::parse($registro->fecha, 'America/Lima')->toDateString());

        $fechasNotas = $notas->keys()->all();
        $fechasAsistencia = $asistencia->keys()->all();
        $fechasTareas = $tareasPorFecha
            ->map(fn ($registro) => CarbonImmutable::parse($registro->fecha, 'America/Lima')->toDateString())
            ->all();
        $fechas = collect(array_merge($fechasNotas, $fechasAsistencia, $fechasTareas))
            ->unique()
            ->sort()
            ->values();

        return [
            'inicio' => $inicio->format('d/m/Y'),
            'fin' => $fin->format('d/m/Y'),
            'etiqueta_periodo' => $etiqueta,
            'labels' => $fechas->map(fn ($fecha) => CarbonImmutable::parse($fecha, 'America/Lima')->format('d/m/Y')),
            'asistencia' => $fechas->map(function ($fecha) use ($asistencia) {
                $porEstado = $asistencia->get($fecha, collect())->keyBy('estado');

                return [
                    'Asistió' => (int) ($porEstado->get('Asistió')->total ?? 0),
                    'Tardanza' => (int) ($porEstado->get('Tardanza')->total ?? 0),
                    'Falta' => (int) (($porEstado->get('Faltó')->total ?? 0) + ($porEstado->get('Falta')->total ?? 0)),
                    'Justificado' => (int) ($porEstado->get('Justificado')->total ?? 0),
                ];
            }),
            'tareas' => $tareasPorFecha->map(function ($registro) use ($totalEstudiantes, $entregasPorFecha) {
                $fecha = CarbonImmutable::parse($registro->fecha, 'America/Lima')->toDateString();
                $etiquetaFecha = CarbonImmutable::parse($fecha, 'America/Lima')->format('d/m/Y');
                $esperados = (int) $registro->total_tareas * $totalEstudiantes;
                $entregas = (int) ($entregasPorFecha->get($fecha)->cumplidas ?? 0);

                return [
                    'fecha' => $etiquetaFecha,
                    'porcentaje' => $esperados > 0
                        ? round(($entregas / $esperados) * 100, 1)
                        : 0,
                ];
            }),
            'notas' => $fechas->map(function ($fecha) use ($notas) {
                $registro = $notas->get($fecha);

                return [
                    'promedio_cuantitativo' => $registro && $registro->promedio_cuantitativo !== null
                        ? round((float) $registro->promedio_cuantitativo, 2)
                        : null,
                    'AD' => (int) ($registro->nota_ad ?? 0),
                    'A' => (int) ($registro->nota_a ?? 0),
                    'B' => (int) ($registro->nota_b ?? 0),
                    'C' => (int) ($registro->nota_c ?? 0),
                ];
            }),
        ];
    }

    private function rangoPeriodo(array $filtros): array
    {
        $periodo = Periodo::query()
            ->where('tipo_periodo', $filtros['tipo_periodo'])
            ->findOrFail($filtros['id_periodo']);

        return [
            CarbonImmutable::parse($periodo->fecha_inicio, 'America/Lima')->startOfDay(),
            CarbonImmutable::parse($periodo->fecha_fin, 'America/Lima')->endOfDay(),
            $periodo->nombre_periodo,
        ];
    }
}

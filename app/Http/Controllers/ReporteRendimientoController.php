<?php

namespace App\Http\Controllers;

use App\Exports\ReporteRendimientoExport;
use App\Http\Requests\ReporteRendimientoRequest;
use App\Models\Estudiante;
use App\Services\Reportes\ContextoReporteService;
use App\Services\Reportes\RendimientoAulaService;
use Illuminate\Contracts\View\View;

class ReporteRendimientoController extends Controller
{
    public function __construct(
        private readonly ContextoReporteService $contexto,
        private readonly RendimientoAulaService $reportes,
        private readonly ReporteRendimientoExport $exportador,
    ) {}

    public function index(ReporteRendimientoRequest $request, int $asignacion): View
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();
        $matriculas = $this->contexto->matriculas($aula);
        $estudiantes = $matriculas->pluck('estudiante')->filter()->values();

        if (! empty($filtros['id_estudiante'])) {
            abort_unless(
                $matriculas->contains('id_estudiante', (int) $filtros['id_estudiante']),
                404
            );
        }

        $periodos = $this->reportes->periodos();

        return view('reportes.rendimiento', [
            'aula' => $aula,
            'estudiantes' => $estudiantes,
            'periodos' => $periodos,
            'filtros' => $filtros,
            'estadisticas' => $this->reportes->estadisticas($aula, $filtros),
        ]);
    }

    public function exportar(ReporteRendimientoRequest $request, int $asignacion)
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();

        if (! empty($filtros['id_estudiante'])) {
            $matriculas = $this->contexto->matriculas($aula);
            abort_unless(
                $matriculas->contains('id_estudiante', (int) $filtros['id_estudiante']),
                404
            );
        }

        return $this->exportador->exportar($aula, $filtros);
    }
}

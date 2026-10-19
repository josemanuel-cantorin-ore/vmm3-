<?php

namespace App\Http\Controllers;

use App\Exports\ReporteTareaExport;
use App\Http\Requests\ReporteTareaRequest;
use App\Services\Reportes\ContextoReporteService;
use App\Services\Reportes\ReporteTareaService;
use Illuminate\Contracts\View\View;

class ReporteTareaController extends Controller
{
    public function __construct(
        private readonly ContextoReporteService $contexto,
        private readonly ReporteTareaService $reportes,
        private readonly ReporteTareaExport $exportador,
    ) {}

    public function index(ReporteTareaRequest $request, int $asignacion): View
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();

        return view('reportes.tareas', [
            'aula' => $aula,
            'tareas' => $this->reportes->tareasDisponibles($aula, $filtros['buscar_tarea'] ?? null),
            'estudiantes' => $this->contexto->matriculas($aula),
            'registros' => $this->reportes->vistaPrevia($aula, $filtros),
            'filtros' => $filtros,
        ]);
    }

    public function exportar(ReporteTareaRequest $request, int $asignacion)
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();

        return $this->exportador->exportar($aula, $filtros);
    }
}

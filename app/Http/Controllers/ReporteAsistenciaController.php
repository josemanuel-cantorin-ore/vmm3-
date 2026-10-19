<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReporteAsistenciaRequest;
use App\Services\Reportes\ContextoReporteService;
use App\Services\Reportes\ReporteAsistenciaService;
use App\Exports\ReporteAsistenciaExport;
use Illuminate\Contracts\View\View;

class ReporteAsistenciaController extends Controller
{
    public function __construct(
        private readonly ContextoReporteService $contexto,
        private readonly ReporteAsistenciaService $reportes,
        private readonly ReporteAsistenciaExport $exportador,
    ) {}

    public function index(ReporteAsistenciaRequest $request, int $asignacion): View
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();

        return view('reportes.asistencia', [
            'aula' => $aula,
            'registros' => $this->reportes->vistaPrevia($aula, $filtros),
            'filtros' => $filtros,
        ]);
    }

    public function exportar(ReporteAsistenciaRequest $request, int $asignacion)
    {
        $aula = $this->contexto->asignacionPropia($asignacion);
        $filtros = $request->validated();

        return $this->exportador->exportar($aula, $filtros);
    }
}

<?php

namespace App\Exports;

use App\Models\AsignacionDocente;
use Illuminate\Http\RedirectResponse;

class ReporteRendimientoExport
{
    public function exportar(AsignacionDocente $aula, array $filtros): RedirectResponse
    {
        // TODO: Consultar las series filtradas y generar el archivo con aula, profesor, periodo y fecha de emisión.
        return back()->with('info', "La exportación en {$filtros['formato']} estará disponible próximamente.");
    }
}

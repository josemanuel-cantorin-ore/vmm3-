<?php

namespace App\Exports;

use App\Models\AsignacionDocente;
use Illuminate\Http\RedirectResponse;

class ReporteAsistenciaExport
{
    public function exportar(AsignacionDocente $aula, array $filtros): RedirectResponse
    {
        // TODO: Consultar los registros filtrados y generar el archivo con aula, profesor, periodo y fecha de emisión.
        return back()->with('info', "La exportación en {$filtros['formato']} estará disponible próximamente.");
    }
}

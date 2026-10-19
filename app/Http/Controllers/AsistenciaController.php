<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AsistenciaAula;
use App\Models\AsignacionDocente;
use App\Models\Matricula;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    public function guardarAsistencia(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|integer|exists:asignacion_docente,id_asignacion',
            'fecha'         => 'required|date',
            'asistencia'    => 'required|array',
            'asistencia.*'  => 'required|in:Asistió,Tardanza,Faltó,Justificado',
        ]);

        $idAsignacion = $request->id_asignacion;
        $fecha        = $request->fecha;
        $asistencias  = $request->asistencia;

        // Obtener la asignación con Eloquent
        $asignacion = AsignacionDocente::findOrFail($idAsignacion);

        // Obtener matrículas usando la relación
        $matriculas = Matricula::where('id_grado_seccion', $asignacion->id_grado_seccion)
            ->where('anio_escolar', now()->year)
            ->whereIn('id_estudiante', array_keys($asistencias))
            ->pluck('id_matricula', 'id_estudiante');

        DB::beginTransaction();
        try {
            foreach ($asistencias as $idEstudiante => $estado) {
                if (!isset($matriculas[$idEstudiante])) {
                    continue;
                }

                // updateOrCreate de Eloquent (más limpio que DB::table)
                AsistenciaAula::updateOrCreate(
                    [
                        'id_matricula'  => $matriculas[$idEstudiante],
                        'id_asignacion' => $idAsignacion,
                        'fecha'         => $fecha,
                    ],
                    [
                        'estado'      => $estado,
                        'observacion' => null,
                    ]
                );
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Asistencia guardada correctamente para la fecha ' . Carbon::parse($fecha)->format('d/m/Y')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
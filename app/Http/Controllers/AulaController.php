<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AulaController extends Controller
{
    /**
     * Muestra la lista de aulas asignadas (Vista de Tarjetas / Dashboard del Profesor)
     * Ruta: /admin/profile
     */
    public function index()
    {
        // Usamos id_usuario según tu esquema SQL (no auth()->id())
        $userId = Auth::user()->id_usuario; 
        $anioActual = now()->year;

        $aulas = DB::table('asignacion_docente as ad')
            ->join('grados_secciones as gs', 'ad.id_grado_seccion', '=', 'gs.id_grado_seccion')
            ->leftJoin('matriculas as m', function($join) use ($anioActual) {
                $join->on('ad.id_grado_seccion', '=', 'm.id_grado_seccion')
                     ->where('m.anio_escolar', '=', $anioActual);
            })
            ->where('ad.id_usuario', $userId)
            ->where('ad.anio_escolar', $anioActual)
            ->select(
                'ad.id_asignacion',
                'gs.grado',
                'gs.seccion',
                'gs.nivel',
                DB::raw('COUNT(DISTINCT m.id_estudiante) as total_alumnos')
            )
            ->groupBy('ad.id_asignacion', 'gs.grado', 'gs.seccion', 'gs.nivel')
            ->get();

        // Asegúrate de que este nombre coincida con tu archivo blade real
        return view('dashb-pages-profile', compact('aulas'));
    }

    /**
     * Muestra el Control de Aula detallado (Pestañas: Asistencia, Tareas, etc.)
     * Ruta: /admin/aula/{id}/control
     */
    public function control($id)
    {
        // 1. Datos del aula
        $datosAula = DB::table('asignacion_docente')
            ->join('grados_secciones', 'asignacion_docente.id_grado_seccion', '=', 'grados_secciones.id_grado_seccion')
            ->where('asignacion_docente.id_asignacion', $id)
            ->where('asignacion_docente.id_usuario', Auth::user()->id_usuario)
            ->select(
                'asignacion_docente.id_asignacion',
                'asignacion_docente.id_grado_seccion',
                'grados_secciones.grado',
                'grados_secciones.seccion',
                'grados_secciones.nivel'
            )
            ->first();

        if (!$datosAula) abort(404);

        // 2. Estudiantes ordenados alfabéticamente
        $anioActual = now()->year;
        $estudiantes = DB::table('matriculas')
            ->join('estudiantes', 'matriculas.id_estudiante', '=', 'estudiantes.id_estudiante')
            ->where('matriculas.id_grado_seccion', $datosAula->id_grado_seccion)
            ->where('matriculas.anio_escolar', $anioActual)
            ->select(
                'matriculas.id_matricula',
                'estudiantes.id_estudiante',
                'estudiantes.dni',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.estado_matricula'
            )
            ->orderBy('estudiantes.apellidos', 'asc')
            ->get();

        // 3. Tareas integradas (para la pestaña dentro del aula)
        $tareas = DB::table('tareas')
            ->join('periodos', 'tareas.id_periodo', '=', 'periodos.id_periodo')
            ->where('tareas.id_asignacion', $id)
            ->whereNull('tareas.deleted_at')
            ->select(
                'tareas.*',
                'periodos.nombre_periodo',
                DB::raw('(SELECT COUNT(*) FROM registro_tareas WHERE registro_tareas.id_tarea = tareas.id_tarea) as total_registros'),
                DB::raw('(SELECT COUNT(*) FROM registro_tareas WHERE registro_tareas.id_tarea = tareas.id_tarea AND registro_tareas.cumplio = TRUE) as total_cumplidos')
            )
            ->orderBy('tareas.fecha_entrega', 'desc')
            ->get();

        return view('dashb-control-aulas', compact('datosAula', 'estudiantes', 'tareas'));
    }
}
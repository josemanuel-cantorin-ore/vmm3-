<?php

namespace App\Http\Controllers;

use App\Models\RegistroTarea;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class TareaController extends Controller
{
    public function create($id)
    {
        $this->asignacionDocente($id);
        $datosAula = $this->datosAula($id);
        $aulasAsignadas = $this->aulasDocente();
        $periodos = DB::table('periodos')->orderBy('nombre_periodo')->get();
        $isEdit = false;

        return view('create', compact('id', 'datosAula', 'aulasAsignadas', 'periodos', 'isEdit'));
    }

    public function store(Request $request, $id)
    {
        $this->asignacionDocente($id);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo_evaluacion' => ['required', Rule::in(['Cualitativa', 'Cuantitativa'])],
            'id_periodo' => ['required', 'exists:periodos,id_periodo'],
            'aulas' => ['required', 'array', 'min:1'],
            'aulas.*.id_asignacion' => ['required', 'integer', 'distinct'],
            'aulas.*.fecha_asignacion' => ['required', 'date'],
            'aulas.*.fecha_entrega' => ['required', 'date'],
        ]);

        $idsAulas = collect($validated['aulas'])->pluck('id_asignacion')->map(fn ($value) => (int) $value);
        $aulasPermitidas = $this->aulasDocente()->pluck('id_asignacion')->map(fn ($value) => (int) $value);
        if ($idsAulas->diff($aulasPermitidas)->isNotEmpty()) {
            abort(403);
        }

        foreach ($validated['aulas'] as $aula) {
            if ($aula['fecha_entrega'] < $aula['fecha_asignacion']) {
                return back()->withErrors([
                    'aulas' => 'La fecha de cierre debe ser posterior o igual a la fecha de inicio en cada aula.',
                ])->withInput();
            }
        }

        try {
            DB::transaction(function () use ($validated) {
                $grupoTarea = (string) Str::uuid();

                foreach ($validated['aulas'] as $aula) {
                    Tarea::create([
                        'id_asignacion' => $aula['id_asignacion'],
                        'id_periodo' => $validated['id_periodo'],
                        'grupo_tarea' => $grupoTarea,
                        'titulo' => $validated['titulo'],
                        'descripcion' => $validated['descripcion'] ?? null,
                        'tipo_evaluacion' => $validated['tipo_evaluacion'],
                        'fecha_asignacion' => $aula['fecha_asignacion'],
                        'fecha_entrega' => $aula['fecha_entrega'],
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo crear la tarea.', ['exception' => $exception]);

            return back()->with('error', 'No fue posible crear la tarea. Inténtalo nuevamente.')->withInput();
        }

        return $this->volverATareas($id)->with('success', 'Tarea(s) creada(s) correctamente.');
    }

    public function papelera($id)
    {
        $this->asignacionDocente($id);
        $datosAula = $this->datosAula($id);
        $tareas = Tarea::onlyTrashed()
            ->where('id_asignacion', $id)
            ->orderByDesc('deleted_at')
            ->get();

        return view('tareas-papelera', compact('datosAula', 'tareas'));
    }

    public function edit(Request $request, $tarea)
    {
        $tarea = $this->tareaDocente($tarea);
        $alcance = $request->query('alcance', 'aula');
        abort_unless(in_array($alcance, ['aula', 'todas'], true), 422);
        abort_if($alcance === 'todas' && ! $tarea->grupo_tarea, 422, 'Esta tarea solo puede editarse en esta aula.');

        $datosAula = $this->datosAula($tarea->id_asignacion);
        $aulasAsignadas = collect();
        $periodos = DB::table('periodos')->orderBy('nombre_periodo')->get();
        $isEdit = true;

        return view('create', compact('tarea', 'datosAula', 'aulasAsignadas', 'periodos', 'isEdit', 'alcance'))
            ->with('id', $tarea->id_asignacion);
    }

    public function update(Request $request, $id)
    {
        $tarea = $this->tareaDocente($id);
        $validated = $request->validate([
            'alcance' => ['required', Rule::in(['aula', 'todas'])],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo_evaluacion' => ['required', Rule::in(['Cualitativa', 'Cuantitativa'])],
            'id_periodo' => ['required', 'exists:periodos,id_periodo'],
            'fecha_asignacion' => ['required', 'date'],
            'fecha_entrega' => ['required', 'date', 'after_or_equal:fecha_asignacion'],
        ]);

        try {
            DB::transaction(function () use ($tarea, $validated) {
                $query = $this->alcanceTareas($tarea, $validated['alcance']);
                $query->update([
                    'titulo' => $validated['titulo'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'tipo_evaluacion' => $validated['tipo_evaluacion'],
                    'id_periodo' => $validated['id_periodo'],
                    'updated_at' => now(),
                ]);

                $tarea->update([
                    'fecha_asignacion' => $validated['fecha_asignacion'],
                    'fecha_entrega' => $validated['fecha_entrega'],
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo actualizar la tarea.', ['exception' => $exception, 'tarea' => $id]);

            return back()->with('error', 'No fue posible actualizar la tarea. Inténtalo nuevamente.')->withInput();
        }

        return $this->volverATareas($tarea->id_asignacion)->with('success', 'Tarea actualizada correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $tarea = $this->tareaDocente($id);
        $validated = $request->validate([
            'alcance' => ['required', Rule::in(['aula', 'todas'])],
        ]);

        try {
            $this->alcanceTareas($tarea, $validated['alcance'])->delete();
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar la tarea a la papelera.', ['exception' => $exception, 'tarea' => $id]);

            return back()->with('error', 'No fue posible eliminar la tarea. Inténtalo nuevamente.');
        }

        return $this->volverATareas($tarea->id_asignacion)->with('success', 'Tarea enviada a la papelera.');
    }

    public function restore(Request $request, $id)
    {
        $validated = $request->validate([
            'alcance' => ['required', Rule::in(['aula', 'todas'])],
            'id_asignacion' => ['required', 'integer', 'exists:asignacion_docente,id_asignacion'],
        ]);
        $this->asignacionDocente($validated['id_asignacion']);
        $tarea = Tarea::onlyTrashed()
            ->whereKey($id)
            ->where('id_asignacion', $validated['id_asignacion'])
            ->firstOrFail();
        abort_if($validated['alcance'] === 'todas' && ! $tarea->grupo_tarea, 422, 'Esta tarea solo puede restaurarse en esta aula.');

        try {
            $query = Tarea::onlyTrashed();
            if ($validated['alcance'] === 'todas' && $tarea->grupo_tarea) {
                $query->where('grupo_tarea', $tarea->grupo_tarea)
                    ->whereHas('asignacion', fn ($asignaciones) => $asignaciones->where('id_usuario', Auth::user()->id_usuario));
            } else {
                $query->whereKey($tarea->id_tarea);
            }
            $query->restore();
        } catch (Throwable $exception) {
            Log::error('No se pudo restaurar la tarea.', ['exception' => $exception, 'tarea' => $id]);

            return back()->with('error', 'No fue posible restaurar la tarea. Inténtalo nuevamente.');
        }

        return $this->volverATareas($tarea->id_asignacion)->with('success', 'Tarea restaurada correctamente.');
    }

    public function calificar($id)
    {
        $tarea = $this->tareaDocente($id);
        $datosAula = $this->datosAula($tarea->id_asignacion);
        $anioEscolar = $tarea->asignacion->anio_escolar;

        $estudiantes = DB::table('matriculas')
            ->join('estudiantes', 'matriculas.id_estudiante', '=', 'estudiantes.id_estudiante')
            ->leftJoin('registro_tareas', function ($join) use ($id) {
                $join->on('registro_tareas.id_matricula', '=', 'matriculas.id_matricula')
                    ->where('registro_tareas.id_tarea', '=', $id);
            })
            ->where('matriculas.id_grado_seccion', $datosAula->id_grado_seccion)
            ->where('matriculas.anio_escolar', $anioEscolar)
            ->select(
                'matriculas.id_matricula',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'registro_tareas.cumplio',
                'registro_tareas.nota_cualitativa',
                'registro_tareas.nota_cuantitativa',
                'registro_tareas.observacion'
            )
            ->orderBy('estudiantes.apellidos')
            ->orderBy('estudiantes.nombres')
            ->get();

        return view('calificar', compact('tarea', 'datosAula', 'estudiantes'));
    }

    public function guardarCalificacion(Request $request, $id)
    {
        $tarea = $this->tareaDocente($id);
        $anioEscolar = $tarea->asignacion->anio_escolar;
        $validated = $request->validate([
            'calificaciones' => ['nullable', 'array'],
            'calificaciones.*.cumplio' => ['required', Rule::in(['0', '1'])],
            'calificaciones.*.nota_cualitativa' => ['nullable', Rule::in(['AD', 'A', 'B', 'C', 'ad', 'a', 'b', 'c'])],
            'calificaciones.*.nota_cuantitativa' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'calificaciones.*.observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $calificaciones = $validated['calificaciones'] ?? [];
        $idsMatriculas = DB::table('matriculas')
            ->join('asignacion_docente', 'matriculas.id_grado_seccion', '=', 'asignacion_docente.id_grado_seccion')
            ->where('asignacion_docente.id_asignacion', $tarea->id_asignacion)
            ->where('matriculas.anio_escolar', $anioEscolar)
            ->whereIn('matriculas.id_matricula', array_keys($calificaciones))
            ->pluck('matriculas.id_matricula')
            ->map(fn ($value) => (string) $value)
            ->all();

        if (count($idsMatriculas) !== count($calificaciones)) {
            abort(403, 'Una o más matrículas no corresponden a esta aula.');
        }

        try {
            DB::transaction(function () use ($calificaciones, $idsMatriculas, $tarea) {
                foreach ($calificaciones as $idMatricula => $calificacion) {
                    if (! in_array((string) $idMatricula, $idsMatriculas, true)) {
                        continue;
                    }

                    $cumplio = $calificacion['cumplio'] === '1';
                    $notaCualitativa = $cumplio && $tarea->tipo_evaluacion === 'Cualitativa'
                        ? (isset($calificacion['nota_cualitativa']) ? strtoupper($calificacion['nota_cualitativa']) : null)
                        : null;

                    RegistroTarea::updateOrCreate(
                        ['id_tarea' => $tarea->id_tarea, 'id_matricula' => $idMatricula],
                        [
                            'cumplio' => $cumplio,
                            'nota_cualitativa' => $notaCualitativa,
                            'nota_cuantitativa' => $cumplio && $tarea->tipo_evaluacion === 'Cuantitativa'
                                ? ($calificacion['nota_cuantitativa'] ?? null)
                                : null,
                            'observacion' => $calificacion['observacion'] ?? null,
                        ]
                    );
                }
            });
        } catch (Throwable $exception) {
            Log::error('No se pudieron guardar las calificaciones.', ['exception' => $exception, 'tarea' => $id]);

            return back()->with('error', 'No fue posible guardar las calificaciones. Inténtalo nuevamente.')->withInput();
        }

        return redirect()
            ->route('admin.tareas.calificar', $tarea->id_tarea)
            ->with('success', 'Calificaciones guardadas correctamente.');
    }

    private function asignacionDocente($id)
    {
        return DB::table('asignacion_docente')
            ->where('id_asignacion', $id)
            ->where('id_usuario', Auth::user()->id_usuario)
            ->firstOrFail();
    }

    private function tareaDocente($id): Tarea
    {
        return Tarea::whereKey($id)
            ->whereHas('asignacion', fn ($query) => $query->where('id_usuario', Auth::user()->id_usuario))
            ->firstOrFail();
    }

    private function aulasDocente()
    {
        return DB::table('asignacion_docente as ad')
            ->join('grados_secciones as gs', 'ad.id_grado_seccion', '=', 'gs.id_grado_seccion')
            ->where('ad.id_usuario', Auth::user()->id_usuario)
            ->where('ad.anio_escolar', now()->year)
            ->select('ad.id_asignacion', 'gs.grado', 'gs.seccion', 'gs.nivel')
            ->orderBy('gs.grado')
            ->orderBy('gs.seccion')
            ->get();
    }

    private function datosAula($id)
    {
        return DB::table('asignacion_docente')
            ->join('grados_secciones', 'asignacion_docente.id_grado_seccion', '=', 'grados_secciones.id_grado_seccion')
            ->where('asignacion_docente.id_asignacion', $id)
            ->select(
                'asignacion_docente.id_asignacion',
                'asignacion_docente.id_grado_seccion',
                'grados_secciones.grado',
                'grados_secciones.seccion',
                'grados_secciones.nivel'
            )
            ->firstOrFail();
    }

    private function alcanceTareas(Tarea $tarea, string $alcance)
    {
        abort_if($alcance === 'todas' && ! $tarea->grupo_tarea, 422, 'Esta tarea solo puede modificarse en esta aula.');

        $query = Tarea::query();
        if ($alcance === 'todas' && $tarea->grupo_tarea) {
            return $query->where('grupo_tarea', $tarea->grupo_tarea)
                ->whereHas('asignacion', fn ($asignaciones) => $asignaciones->where('id_usuario', Auth::user()->id_usuario));
        }

        return $query->whereKey($tarea->id_tarea);
    }

    private function volverATareas($idAsignacion)
    {
        return redirect()->to(route('admin.control-aulas', $idAsignacion).'#tareas');
    }
}

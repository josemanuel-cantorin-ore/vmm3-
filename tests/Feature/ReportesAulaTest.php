<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaAula;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\RegistroTarea;
use App\Models\Tarea;
use App\Models\Usuario;
use App\Services\Reportes\RendimientoAulaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportesAulaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'registro_tareas',
            'asistencia_aula',
            'matriculas',
            'tareas',
            'periodos',
            'asignacion_docente',
            'estudiantes',
            'grados_secciones',
            'cursos',
            'usuarios',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('dni')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo')->nullable();
            $table->string('clave');
            $table->unsignedInteger('id_rol')->nullable();
            $table->string('estado')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('cursos', function (Blueprint $table) {
            $table->id('id_curso');
            $table->string('nombre_curso');
        });
        Schema::create('grados_secciones', function (Blueprint $table) {
            $table->id('id_grado_seccion');
            $table->string('grado');
            $table->string('seccion');
            $table->string('nivel');
        });
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id('id_estudiante');
            $table->string('dni')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('estado_matricula')->nullable();
            $table->timestamps();
        });
        Schema::create('asignacion_docente', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_grado_seccion');
            $table->unsignedInteger('anio_escolar');
            $table->boolean('es_tutor')->default(false);
            $table->timestamps();
        });
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id('id_matricula');
            $table->unsignedBigInteger('id_estudiante');
            $table->unsignedBigInteger('id_grado_seccion');
            $table->unsignedInteger('anio_escolar');
            $table->timestamps();
        });
        Schema::create('periodos', function (Blueprint $table) {
            $table->id('id_periodo');
            $table->string('nombre_periodo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->nullable();
            $table->string('tipo_periodo')->default('bimestre');
        });
        Schema::create('tareas', function (Blueprint $table) {
            $table->id('id_tarea');
            $table->unsignedBigInteger('id_asignacion');
            $table->unsignedBigInteger('id_periodo')->nullable();
            $table->string('grupo_tarea', 36)->nullable();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('tipo_evaluacion');
            $table->date('fecha_asignacion');
            $table->date('fecha_entrega');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('asistencia_aula', function (Blueprint $table) {
            $table->id('id_asistencia_aula');
            $table->unsignedBigInteger('id_matricula');
            $table->unsignedBigInteger('id_asignacion');
            $table->date('fecha');
            $table->string('estado');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
        Schema::create('registro_tareas', function (Blueprint $table) {
            $table->id('id_registro_tarea');
            $table->unsignedBigInteger('id_tarea');
            $table->unsignedBigInteger('id_matricula');
            $table->boolean('cumplio');
            $table->string('nota_cualitativa')->nullable();
            $table->decimal('nota_cuantitativa', 5, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function test_teacher_cannot_open_another_teachers_report(): void
    {
        $profesor = $this->crearProfesor('Propietario');
        $otroProfesor = $this->crearProfesor('Otro');
        $this->crearAula($otroProfesor);

        $this->actingAs($profesor)
            ->get(route('admin.reportes.asistencia', 1))
            ->assertForbidden();
    }

    public function test_attendance_report_uses_the_existing_attendance_records(): void
    {
        [$profesor, $asignacion] = $this->crearAulaPropia();
        [$estudiante, $matricula] = $this->crearEstudiante($asignacion);
        $fecha = now('America/Lima')->toDateString();

        AsistenciaAula::query()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_asignacion' => $asignacion->id_asignacion,
            'fecha' => $fecha,
            'estado' => 'Faltó',
        ]);

        $this->actingAs($profesor)
            ->get(route('admin.reportes.asistencia', [
                'asignacion' => $asignacion->id_asignacion,
                'fecha' => $fecha,
            ]))
            ->assertOk()
            ->assertSee($estudiante->apellidos)
            ->assertSee('Falta');

        $this->post(route('admin.reportes.asistencia.exportar', $asignacion->id_asignacion), [
            'modo_periodo' => 'fecha',
            'fecha' => $fecha,
            'formato' => 'pdf',
        ])
            ->assertRedirect()
            ->assertSessionHas('info', 'La exportación en pdf estará disponible próximamente.');
    }

    public function test_task_preview_and_performance_report_load_real_data(): void
    {
        [$profesor, $asignacion] = $this->crearAulaPropia();
        [, $matricula] = $this->crearEstudiante($asignacion);
        $hoy = now('America/Lima')->toDateString();

        $periodo = Periodo::query()->create([
            'nombre_periodo' => 'I Bimestre',
            'fecha_inicio' => now('America/Lima')->startOfMonth()->toDateString(),
            'fecha_fin' => now('America/Lima')->endOfMonth()->toDateString(),
            'tipo_periodo' => 'bimestre',
        ]);
        $tarea = Tarea::query()->create([
            'id_asignacion' => $asignacion->id_asignacion,
            'id_periodo' => $periodo->id_periodo,
            'titulo' => 'Lectura',
            'tipo_evaluacion' => 'Cuantitativa',
            'fecha_asignacion' => $hoy,
            'fecha_entrega' => $hoy,
        ]);
        RegistroTarea::query()->create([
            'id_tarea' => $tarea->id_tarea,
            'id_matricula' => $matricula->id_matricula,
            'cumplio' => true,
            'nota_cuantitativa' => 18,
        ]);
        $this->actingAs($profesor)
            ->get(route('admin.reportes.tareas', $asignacion->id_asignacion))
            ->assertOk()
            ->assertSee('Lectura')
            ->assertSee('Entregó');

        $this->get(route('admin.reportes.rendimiento', $asignacion->id_asignacion))
            ->assertOk()
            ->assertSee('grafico-asistencia')
            ->assertSee('grafico-notas-cuantitativas')
            ->assertSee('0 a 20');

        $estadisticas = app(RendimientoAulaService::class)->estadisticas($asignacion, [
            'filtro' => 'personalizado',
            'fecha_inicio' => $hoy,
            'fecha_fin' => $hoy,
        ]);
        $this->assertSame(100.0, $estadisticas['tareas']->first()['porcentaje']);
        $this->assertSame(18.0, $estadisticas['notas']->first()['promedio_cuantitativo']);
    }

    private function crearProfesor(string $apellido): Usuario
    {
        return Usuario::query()->create([
            'nombres' => 'Ana',
            'apellidos' => $apellido,
            'correo' => strtolower($apellido) . '@escuela.test',
            'clave' => 'clave-segura',
            'id_rol' => 2,
            'estado' => 'Activo',
        ]);
    }

    private function crearAulaPropia(): array
    {
        $profesor = $this->crearProfesor('Docente');

        return [$profesor, $this->crearAula($profesor)];
    }

    private function crearAula(Usuario $profesor): AsignacionDocente
    {
        $cursoId = \Illuminate\Support\Facades\DB::table('cursos')->insertGetId(['nombre_curso' => 'Comunicación']);
        $gradoId = \Illuminate\Support\Facades\DB::table('grados_secciones')->insertGetId([
            'grado' => '1° Grado',
            'seccion' => 'D',
            'nivel' => 'Primaria',
        ]);

        return AsignacionDocente::query()->create([
            'id_usuario' => $profesor->id_usuario,
            'id_curso' => $cursoId,
            'id_grado_seccion' => $gradoId,
            'anio_escolar' => now('America/Lima')->year,
            'es_tutor' => true,
        ]);
    }

    private function crearEstudiante(AsignacionDocente $asignacion): array
    {
        $estudiante = Estudiante::query()->create([
            'nombres' => 'Lucía',
            'apellidos' => 'Pérez',
            'estado_matricula' => 'Activo',
        ]);
        $matricula = Matricula::query()->create([
            'id_estudiante' => $estudiante->id_estudiante,
            'id_grado_seccion' => $asignacion->id_grado_seccion,
            'anio_escolar' => $asignacion->anio_escolar,
        ]);

        return [$estudiante, $matricula];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionDocente extends Model
{
    protected $table = 'asignacion_docente';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_usuario', 'id_curso', 'id_grado_seccion',
        'anio_escolar', 'es_tutor',
    ];

    protected $casts = [
        'anio_escolar' => 'integer',
        'es_tutor' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function gradoSeccion()
    {
        return $this->belongsTo(GradoSeccion::class, 'id_grado_seccion', 'id_grado_seccion');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioClase::class, 'id_asignacion', 'id_asignacion');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'id_asignacion', 'id_asignacion');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaAula::class, 'id_asignacion', 'id_asignacion');
    }

    public function incidencias()
    {
        return $this->hasMany(IncidenciaAula::class, 'id_asignacion', 'id_asignacion');
    }

    public function promedios()
    {
        return $this->hasMany(PromedioPeriodo::class, 'id_asignacion', 'id_asignacion');
    }

    public function scopeTutores($query)
    {
        return $query->where('es_tutor', true);
    }
}
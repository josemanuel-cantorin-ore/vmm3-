<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'id_estudiante', 'id_grado_seccion', 'anio_escolar',
    ];

    protected $casts = [
        'anio_escolar' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function gradoSeccion()
    {
        return $this->belongsTo(GradoSeccion::class, 'id_grado_seccion', 'id_grado_seccion');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaAula::class, 'id_matricula', 'id_matricula');
    }

    public function registroTareas()
    {
        return $this->hasMany(RegistroTarea::class, 'id_matricula', 'id_matricula');
    }

    public function conductas()
    {
        return $this->hasMany(ConductaPeriodo::class, 'id_matricula', 'id_matricula');
    }

    public function incidencias()
    {
        return $this->hasMany(IncidenciaAula::class, 'id_matricula', 'id_matricula');
    }

    public function promedios()
    {
        return $this->hasMany(PromedioPeriodo::class, 'id_matricula', 'id_matricula');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradoSeccion extends Model
{
    protected $table = 'grados_secciones';
    protected $primaryKey = 'id_grado_seccion';
    public $timestamps = false;

    protected $fillable = ['grado', 'seccion', 'nivel'];

    public function asignaciones()
    {
        return $this->hasMany(AsignacionDocente::class, 'id_grado_seccion', 'id_grado_seccion');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_grado_seccion', 'id_grado_seccion');
    }

    // Scope para obtener nombre completo del aula
    public function getNombreCompletoAttribute()
    {
        return $this->grado . ' - ' . $this->seccion;
    }
}
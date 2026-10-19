<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = ['nombre_curso'];

    public function asignaciones()
    {
        return $this->hasMany(AsignacionDocente::class, 'id_curso', 'id_curso');
    }
}
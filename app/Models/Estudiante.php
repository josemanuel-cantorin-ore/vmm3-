<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $primaryKey = 'id_estudiante';

    protected $fillable = [
        'dni', 'nombres', 'apellidos', 'estado_matricula',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_estudiante', 'id_estudiante');
    }

    public function matriculaActual()
    {
        return $this->hasOne(Matricula::class, 'id_estudiante', 'id_estudiante')
                    ->where('anio_escolar', now()->year);
    }

    // Nombre completo
    public function getNombreCompletoAttribute()
    {
        return $this->apellidos . ', ' . $this->nombres;
    }
}
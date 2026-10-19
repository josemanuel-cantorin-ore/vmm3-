<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioClase extends Model
{
    protected $table = 'horario_clase';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'id_asignacion', 'dia_semana', 'hora_inicio', 'hora_fin',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignacionDocente::class, 'id_asignacion', 'id_asignacion');
    }

    public function getNombreDiaAttribute()
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
        ];
        return $dias[$this->dia_semana] ?? 'Día inválido';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromedioPeriodo extends Model
{
    protected $table = 'promedios_periodo';
    protected $primaryKey = 'id_promedio';

    protected $fillable = [
        'id_matricula', 'id_asignacion', 'id_periodo',
        'promedio_cuantitativo', 'promedio_cualitativo',
    ];

    protected $casts = [
        'promedio_cuantitativo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function asignacion()
    {
        return $this->belongsTo(AsignacionDocente::class, 'id_asignacion', 'id_asignacion');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }
}
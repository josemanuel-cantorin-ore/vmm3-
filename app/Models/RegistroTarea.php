<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroTarea extends Model
{
    protected $table = 'registro_tareas';
    protected $primaryKey = 'id_registro_tarea';

    protected $fillable = [
        'id_tarea', 'id_matricula', 'cumplio',
        'nota_cualitativa', 'nota_cuantitativa', 'observacion',
    ];

    protected $casts = [
        'cumplio' => 'boolean',
        'nota_cuantitativa' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'id_tarea', 'id_tarea');
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }
}
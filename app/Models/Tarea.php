<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarea extends Model
{
    use SoftDeletes;

    protected $table = 'tareas';

    protected $primaryKey = 'id_tarea';

    protected $fillable = [
        'id_asignacion', 'id_periodo', 'grupo_tarea', 'titulo', 'descripcion',
        'tipo_evaluacion', 'fecha_asignacion', 'fecha_entrega',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_entrega' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignacionDocente::class, 'id_asignacion', 'id_asignacion');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function registros()
    {
        return $this->hasMany(RegistroTarea::class, 'id_tarea', 'id_tarea');
    }
}

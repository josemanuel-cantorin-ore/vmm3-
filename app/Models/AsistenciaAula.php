<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaAula extends Model
{
    protected $table = 'asistencia_aula';
    protected $primaryKey = 'id_asistencia_aula';

    protected $fillable = [
        'id_matricula', 'id_asignacion', 'fecha',
        'estado', 'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
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

    // Scopes útiles
    public function scopeAsistio($query)
    {
        return $query->where('estado', 'Asistió');
    }

    public function scopeTardanza($query)
    {
        return $query->where('estado', 'Tardanza');
    }

    public function scopeFalto($query)
    {
        return $query->where('estado', 'Faltó');
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenciaAula extends Model
{
    protected $table = 'incidencias_aula';
    protected $primaryKey = 'id_incidencia';

    protected $fillable = [
        'id_matricula', 'id_asignacion', 'fecha',
        'tipo', 'descripcion',
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

    public function scopePositivas($query)
    {
        return $query->where('tipo', 'Positiva');
    }

    public function scopeNegativas($query)
    {
        return $query->where('tipo', 'Negativa');
    }
}
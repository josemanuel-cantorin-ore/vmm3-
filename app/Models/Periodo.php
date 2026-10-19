<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'periodos';
    protected $primaryKey = 'id_periodo';
    public $timestamps = false;

    protected $fillable = [
        'nombre_periodo', 'fecha_inicio', 'fecha_fin', 'estado', 'tipo_periodo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'id_periodo', 'id_periodo');
    }

    public function conductas()
    {
        return $this->hasMany(ConductaPeriodo::class, 'id_periodo', 'id_periodo');
    }

    public function promedios()
    {
        return $this->hasMany(PromedioPeriodo::class, 'id_periodo', 'id_periodo');
    }

    public function scopeAbierto($query)
    {
        return $query->where('estado', 'Abierto');
    }
}
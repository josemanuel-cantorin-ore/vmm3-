<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductaPeriodo extends Model
{
    protected $table = 'conducta_periodo';
    protected $primaryKey = 'id_conducta';

    protected $fillable = [
        'id_matricula', 'id_periodo', 'id_tutor',
        'nota_conducta', 'observacion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function tutor()
    {
        return $this->belongsTo(Usuario::class, 'id_tutor', 'id_usuario');
    }
}
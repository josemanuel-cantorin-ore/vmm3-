<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

 
    protected $keyType = 'int'; 
    

    public $incrementing = true; 

    protected $fillable = [
        'dni', 'nombres', 'apellidos', 'correo', 'clave',
        'id_rol', 'estado',
    ];

    protected $hidden = ['clave', 'remember_token'];

    // Usa casts modernos (array) en lugar del formato antiguo
    protected function casts(): array
    {
        return [
            'clave' => 'hashed',       // Encripta automáticamente al guardar
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ✅ MÉTODO CORRECTO para Laravel 10/11/12
    // Indica qué columna contiene la contraseña
    public function getAuthPasswordName(): string
    {
        return 'clave';
    }

    public function getEmailForVerification(): string
    {
        return $this->correo;
    }

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function asignacionesDocente()
    {
        return $this->hasMany(AsignacionDocente::class, 'id_usuario', 'id_usuario');
    }

    public function tutorias()
    {
        return $this->hasMany(AsignacionDocente::class, 'id_tutor', 'id_usuario');
    }

    public function logsAuditoria()
    {
        return $this->hasMany(AuditoriaLog::class, 'id_usuario', 'id_usuario');
    }

    // Scopes
    public function scopeProfesores($query)
    {
        return $query->where('id_rol', 2);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }
}
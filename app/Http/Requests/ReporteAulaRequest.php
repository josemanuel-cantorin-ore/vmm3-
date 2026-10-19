<?php

namespace App\Http\Requests;

use App\Models\AsignacionDocente;
use Illuminate\Foundation\Http\FormRequest;

abstract class ReporteAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && AsignacionDocente::query()
                ->whereKey($this->route('asignacion'))
                ->where('id_usuario', auth()->user()->id_usuario)
                ->exists();
    }
}

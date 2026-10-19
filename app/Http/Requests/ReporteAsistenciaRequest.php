<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ReporteAsistenciaRequest extends ReporteAulaRequest
{
    protected function prepareForValidation(): void
    {
        $modo = $this->input('modo_periodo', 'fecha');
        $defaults = ['modo_periodo' => $modo];

        if ($modo === 'fecha' && ! $this->filled('fecha')) {
            $defaults['fecha'] = now('America/Lima')->toDateString();
        }

        if ($modo === 'rango') {
            $defaults['fecha_inicio'] = $this->input('fecha_inicio', now('America/Lima')->toDateString());
            $defaults['fecha_fin'] = $this->input('fecha_fin', now('America/Lima')->toDateString());
        }

        $this->merge($defaults);
    }

    public function rules(): array
    {
        return [
            'modo_periodo' => ['required', Rule::in(['fecha', 'rango'])],
            'fecha' => ['required_if:modo_periodo,fecha', 'nullable', 'date'],
            'fecha_inicio' => ['required_if:modo_periodo,rango', 'nullable', 'date'],
            'fecha_fin' => ['required_if:modo_periodo,rango', 'nullable', 'date', 'after_or_equal:fecha_inicio'],
            'buscar_estudiante' => ['nullable', 'string', 'max:100'],
            'formato' => ['sometimes', 'required', Rule::in(['pdf', 'excel'])],
        ];
    }
}

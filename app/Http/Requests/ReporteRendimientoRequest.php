<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ReporteRendimientoRequest extends ReporteAulaRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'filtro' => $this->input('filtro', 'semana'),
            'tipo_periodo' => $this->input('tipo_periodo', 'bimestre'),
        ]);
    }

    public function rules(): array
    {
        return [
            'filtro' => ['required', Rule::in(['semana', 'mes', 'periodo', 'personalizado'])],
            'tipo_periodo' => ['required_if:filtro,periodo', 'nullable', Rule::in(['bimestre', 'trimestre'])],
            'id_periodo' => [
                'required_if:filtro,periodo',
                'nullable',
                'integer',
                Rule::exists('periodos', 'id_periodo')->where('tipo_periodo', $this->input('tipo_periodo', 'bimestre')),
            ],
            'fecha_inicio' => ['required_if:filtro,personalizado', 'nullable', 'date'],
            'fecha_fin' => ['required_if:filtro,personalizado', 'nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_estudiante' => ['nullable', 'integer'],
            'formato' => ['sometimes', 'required', Rule::in(['pdf', 'excel'])],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ReporteTareaRequest extends ReporteAulaRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'alcance' => $this->input('alcance', 'todas'),
        ]);
    }

    public function rules(): array
    {
        $idAsignacion = $this->route('asignacion');

        return [
            'alcance' => ['required', Rule::in(['todas', 'seleccionadas'])],
            'tareas' => ['exclude_unless:alcance,seleccionadas', 'required', 'array', 'min:1'],
            'tareas.*' => [
                'exclude_unless:alcance,seleccionadas',
                'integer',
                'distinct',
                Rule::exists('tareas', 'id_tarea')
                    ->where('id_asignacion', $idAsignacion)
                    ->whereNull('deleted_at'),
            ],
            'buscar_tarea' => ['nullable', 'string', 'max:150'],
            'buscar_estudiante' => ['nullable', 'string', 'max:100'],
            'formato' => ['sometimes', 'required', Rule::in(['pdf', 'excel'])],
        ];
    }
}

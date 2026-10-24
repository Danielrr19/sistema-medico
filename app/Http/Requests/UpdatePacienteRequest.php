<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' = solo se valida si el campo viene en la petición (permite actualizar parcialmente)
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            // ignore(...) = el paciente puede conservar SU PROPIO email sin que cuente como repetido
            'email' => ['sometimes', 'required', 'email', 'max:255',
                Rule::unique('pacientes', 'email')->ignore($this->route('paciente'))],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
            'historial_medico' => ['nullable', 'string'],
        ];
    }
}
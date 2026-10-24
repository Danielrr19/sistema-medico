<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    // true = cualquier usuario autenticado puede usar este formulario
    public function authorize(): bool
    {
        return true;
    }

    // Las reglas: el "control de calidad" de los datos que entran
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:pacientes,email'], // no repetido
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],            // no puede ser futura
            'historial_medico' => ['nullable', 'string'],
        ];
    }
}
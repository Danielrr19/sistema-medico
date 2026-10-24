<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' = solo se valida si el campo viene en la petición
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            // El doctor puede conservar SU PROPIO email (el parámetro de la ruta se llama 'doctor')
            'email' => ['sometimes', 'required', 'email', 'max:255',
                Rule::unique('doctores', 'email')->ignore($this->route('doctor'))],
            'especialidad' => ['sometimes', 'required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cualificaciones' => ['nullable', 'string'],
        ];
    }
}
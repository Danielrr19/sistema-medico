<?php

namespace App\Http\Requests;

use App\Models\Cita;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // En la ruta de actualizar viene la cita; al crear es null
        $cita = $this->route('cita');

        // Al crear los campos son obligatorios; al actualizar solo se validan si vienen
        $obligatorio = $cita ? ['sometimes', 'required'] : ['required'];

        return [
            'paciente_id' => [...$obligatorio, 'exists:pacientes,id'], // debe existir el paciente
            'doctor_id' => [...$obligatorio, 'exists:doctores,id'],    // debe existir el doctor

            'fecha_cita' => [
                'bail', // si falla una regla, no sigue con las demás
                ...$obligatorio,
                'date',        // debe ser una fecha válida
                'after:now',   // no puede estar en el pasado

                // Regla propia: el doctor no puede tener otra cita a esa misma hora.
                // Las canceladas no cuentan (liberan el horario) y la cita misma se ignora al actualizar.
                function (string $attribute, mixed $value, Closure $fail) use ($cita) {
                    $doctorId = $this->input('doctor_id', $cita?->doctor_id);

                    $ocupado = Cita::where('doctor_id', $doctorId)
                        ->where('fecha_cita', Carbon::parse($value))
                        ->where('estado', '!=', 'cancelada')
                        ->when($cita, fn ($consulta) => $consulta->where('id', '!=', $cita->id))
                        ->exists();

                    if ($ocupado) {
                        $fail('El doctor ya tiene una cita en ese horario.');
                    }
                },
            ],

            'estado' => ['sometimes', Rule::in(['pendiente', 'confirmada', 'completada', 'cancelada'])],
            'notas' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Si no le indicas un paciente/doctor, la factory crea uno nuevo automáticamente
            'paciente_id' => Paciente::factory(),
            'doctor_id' => Doctor::factory(),
            // Una fecha entre mañana y dentro de un mes
            'fecha_cita' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'estado' => fake()->randomElement(['pendiente', 'confirmada', 'completada', 'cancelada']),
            'notas' => fake()->sentence(),
        ];
    }
}
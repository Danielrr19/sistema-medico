<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Dr. ' . fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // Escoge una especialidad al azar de la lista
            'especialidad' => fake()->randomElement(['Cardiología', 'Pediatría', 'Dermatología', 'Neurología']),
            'telefono' => fake()->numerify('####-####'),
            'cualificaciones' => fake()->sentence(),
        ];
    }
}
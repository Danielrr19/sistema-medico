<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    // Define cómo se ve un paciente "de mentira"
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),                                // nombre aleatorio
            'email' => fake()->unique()->safeEmail(),                  // correo distinto cada vez
            'telefono' => fake()->numerify('####-####'),               // ej. 7345-1290
            'fecha_nacimiento' => fake()->date('Y-m-d', '-18 years'),  // mayor de 18 años
            'historial_medico' => fake()->sentence(),                  // una frase cualquiera
        ];
    }
}
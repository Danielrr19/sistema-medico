<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PacienteTest extends TestCase
{
    use RefreshDatabase; // base limpia en cada prueba

    // Datos de un paciente válido; $extra permite cambiar solo lo que cada prueba necesite
    private function datosValidos(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Ana Martínez',
            'email' => 'ana@example.com',
            'telefono' => '7345-1290',
            'fecha_nacimiento' => '1990-05-20',
            'historial_medico' => 'Sin antecedentes.',
        ], $extra);
    }

    // Simula a un usuario con token válido (el "gafete")
    private function autenticar(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    // --- CONTROL DE ACCESO ---
    public function test_sin_token_no_se_puede_acceder_a_pacientes(): void
    {
        $this->getJson('/api/pacientes')->assertStatus(401);
        $this->postJson('/api/pacientes', $this->datosValidos())->assertStatus(401);
    }

    // --- LISTAR (index) ---
    public function test_se_pueden_listar_los_pacientes(): void
    {
        $this->autenticar();
        Paciente::factory()->count(3)->create();

        $this->getJson('/api/pacientes')
            ->assertOk()
            ->assertJsonCount(3, 'data'); // la respuesta viene paginada dentro de "data"
    }

    // --- CREAR (store) ---
    public function test_se_puede_crear_un_paciente(): void
    {
        $this->autenticar();

        $this->postJson('/api/pacientes', $this->datosValidos())
            ->assertStatus(201) // 201 = creado
            ->assertJsonFragment(['email' => 'ana@example.com']);

        $this->assertDatabaseHas('pacientes', ['email' => 'ana@example.com']);
    }

    public function test_no_se_crea_un_paciente_con_datos_invalidos(): void
    {
        $this->autenticar();

        // Sin nombre, email mal escrito y fecha de nacimiento en el futuro
        $this->postJson('/api/pacientes', [
            'email' => 'esto-no-es-un-email',
            'fecha_nacimiento' => now()->addDay()->toDateString(),
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'email', 'fecha_nacimiento']);
    }

    public function test_no_se_permite_un_email_repetido(): void
    {
        $this->autenticar();
        Paciente::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/pacientes', $this->datosValidos())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // --- VER UNO (show) ---
    public function test_se_puede_ver_un_paciente(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create();

        $this->getJson("/api/pacientes/{$paciente->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $paciente->id]);
    }

    public function test_ver_un_paciente_inexistente_da_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/pacientes/9999')->assertStatus(404);
    }

    // --- ACTUALIZAR (update) ---
    public function test_se_puede_actualizar_un_paciente(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create();

        $this->putJson("/api/pacientes/{$paciente->id}", ['nombre' => 'Nombre Nuevo'])
            ->assertOk();

        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id, 'nombre' => 'Nombre Nuevo']);
    }

    public function test_actualizar_permite_conservar_su_propio_email(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create(['email' => 'ana@example.com']);

        // Mandar el mismo email NO debe contar como "repetido"
        $this->putJson("/api/pacientes/{$paciente->id}", ['email' => 'ana@example.com'])
            ->assertOk();
    }

    // --- ELIMINAR (destroy) ---
    public function test_se_puede_eliminar_un_paciente(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create();

        $this->deleteJson("/api/pacientes/{$paciente->id}")
            ->assertNoContent(); // 204 = hecho, sin contenido

        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
    }
}

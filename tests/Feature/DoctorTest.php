<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Dr. Carlos Rivas',
            'email' => 'carlos@example.com',
            'especialidad' => 'Cardiología',
            'telefono' => '7345-1290',
            'cualificaciones' => 'Especialista en cardiología clínica.',
        ], $extra);
    }

    // Simula a un usuario con token válido
    private function autenticar(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_sin_token_no_se_puede_acceder_a_doctores(): void
    {
        $this->getJson('/api/doctores')->assertStatus(401);
        $this->postJson('/api/doctores', $this->datosValidos())->assertStatus(401);
    }

    public function test_se_pueden_listar_los_doctores(): void
    {
        $this->autenticar();
        Doctor::factory()->count(3)->create();

        $this->getJson('/api/doctores')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_se_puede_crear_un_doctor(): void
    {
        $this->autenticar();

        $this->postJson('/api/doctores', $this->datosValidos())
            ->assertStatus(201)
            ->assertJsonFragment(['email' => 'carlos@example.com']);

        $this->assertDatabaseHas('doctores', ['email' => 'carlos@example.com']);
    }

    public function test_no_se_crea_un_doctor_con_datos_invalidos(): void
    {
        $this->autenticar();

        // Sin nombre ni especialidad y con email mal escrito
        $this->postJson('/api/doctores', ['email' => 'no-es-un-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'email', 'especialidad']);
    }

    public function test_no_se_permite_un_email_repetido(): void
    {
        $this->autenticar();
        Doctor::factory()->create(['email' => 'carlos@example.com']);

        $this->postJson('/api/doctores', $this->datosValidos())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_se_puede_ver_un_doctor(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create();

        $this->getJson("/api/doctores/{$doctor->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $doctor->id]);
    }

    public function test_ver_un_doctor_inexistente_da_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/doctores/9999')->assertStatus(404);
    }

    public function test_se_puede_actualizar_un_doctor(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create();

        $this->putJson("/api/doctores/{$doctor->id}", ['especialidad' => 'Pediatría'])->assertOk();

        $this->assertDatabaseHas('doctores', ['id' => $doctor->id, 'especialidad' => 'Pediatría']);
    }

    public function test_actualizar_permite_conservar_su_propio_email(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create(['email' => 'carlos@example.com']);

        $this->putJson("/api/doctores/{$doctor->id}", ['email' => 'carlos@example.com'])->assertOk();
    }

    public function test_se_puede_eliminar_un_doctor(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create();

        $this->deleteJson("/api/doctores/{$doctor->id}")->assertNoContent();

        $this->assertDatabaseMissing('doctores', ['id' => $doctor->id]);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    // Simula a un usuario con token válido
    private function autenticar(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_los_reportes_exigen_token(): void
    {
        $this->getJson('/api/reportes/citas-por-estado')->assertStatus(401);
        $this->getJson('/api/reportes/citas-por-doctor')->assertStatus(401);
    }

    public function test_reporte_de_citas_por_estado(): void
    {
        $this->autenticar();
        Cita::factory()->count(2)->create(['estado' => 'pendiente']);
        Cita::factory()->count(1)->create(['estado' => 'confirmada']);

        // Los estados sin citas deben aparecer igual, con 0
        $this->getJson('/api/reportes/citas-por-estado')
            ->assertOk()
            ->assertExactJson([
                'total' => 3,
                'por_estado' => [
                    'pendiente' => 2,
                    'confirmada' => 1,
                    'completada' => 0,
                    'cancelada' => 0,
                ],
            ]);
    }

    public function test_reporte_de_citas_por_estado_sin_citas(): void
    {
        $this->autenticar();

        $this->getJson('/api/reportes/citas-por-estado')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_reporte_de_citas_por_doctor(): void
    {
        $this->autenticar();
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $doctorC = Doctor::factory()->create(); // sin citas

        Cita::factory()->count(3)->create(['doctor_id' => $doctorA->id]);
        Cita::factory()->count(1)->create(['doctor_id' => $doctorB->id]);

        // Ordenado de más a menos citas; el doctor sin citas también aparece
        $this->getJson('/api/reportes/citas-por-doctor')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $doctorA->id)
            ->assertJsonPath('data.0.total_citas', 3)
            ->assertJsonPath('data.1.id', $doctorB->id)
            ->assertJsonPath('data.1.total_citas', 1)
            ->assertJsonPath('data.2.id', $doctorC->id)
            ->assertJsonPath('data.2.total_citas', 0);
    }
}
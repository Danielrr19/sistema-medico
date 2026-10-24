<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CitaTest extends TestCase
{
    use RefreshDatabase;

    // Una cita válida: paciente y doctor reales, fecha futura, estado correcto
    private function datosValidos(array $extra = []): array
    {
        return array_merge([
            'paciente_id' => Paciente::factory()->create()->id,
            'doctor_id' => Doctor::factory()->create()->id,
            'fecha_cita' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'estado' => 'pendiente',
            'notas' => 'Primera consulta',
        ], $extra);
    }

    private function autenticar(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    // --- CONTROL DE ACCESO ---
    public function test_sin_token_no_se_puede_acceder_a_citas(): void
    {
        $this->getJson('/api/citas')->assertStatus(401);
        $this->postJson('/api/citas', [])->assertStatus(401);
    }

    // --- CRUD BÁSICO ---
    public function test_se_pueden_listar_las_citas(): void
    {
        $this->autenticar();
        Cita::factory()->count(3)->create();

        $this->getJson('/api/citas')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_se_puede_crear_una_cita(): void
    {
        $this->autenticar();
        $datos = $this->datosValidos();

        $this->postJson('/api/citas', $datos)
            ->assertStatus(201)
            ->assertJsonFragment(['estado' => 'pendiente']);

        $this->assertDatabaseHas('citas', [
            'paciente_id' => $datos['paciente_id'],
            'doctor_id' => $datos['doctor_id'],
        ]);
    }

    public function test_se_puede_ver_una_cita(): void
    {
        $this->autenticar();
        $cita = Cita::factory()->create();

        $this->getJson("/api/citas/{$cita->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $cita->id]);
    }

    public function test_ver_una_cita_inexistente_da_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/citas/9999')->assertStatus(404);
    }

    public function test_se_puede_actualizar_el_estado_de_una_cita(): void
    {
        $this->autenticar();
        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        $this->putJson("/api/citas/{$cita->id}", ['estado' => 'confirmada'])->assertOk();

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'confirmada']);
    }

    public function test_se_puede_eliminar_una_cita(): void
    {
        $this->autenticar();
        $cita = Cita::factory()->create();

        $this->deleteJson("/api/citas/{$cita->id}")->assertNoContent();

        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }

    // --- VALIDACIONES ---
    public function test_no_se_crea_una_cita_sin_datos_obligatorios(): void
    {
        $this->autenticar();

        $this->postJson('/api/citas', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['paciente_id', 'doctor_id', 'fecha_cita']);
    }

    public function test_no_se_crea_una_cita_con_paciente_o_doctor_inexistente(): void
    {
        $this->autenticar();

        $this->postJson('/api/citas', $this->datosValidos(['paciente_id' => 9999, 'doctor_id' => 9999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['paciente_id', 'doctor_id']);
    }

    public function test_no_se_permite_una_estado_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/citas', $this->datosValidos(['estado' => 'inventado']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estado']);
    }

    // --- VALIDACIONES DE FECHA ---
    public function test_no_se_permite_una_fecha_en_el_pasado(): void
    {
        $this->autenticar();

        $this->postJson('/api/citas', $this->datosValidos([
            'fecha_cita' => now()->subDay()->format('Y-m-d H:i:s'),
        ]))->assertStatus(422)->assertJsonValidationErrors(['fecha_cita']);
    }

    public function test_no_se_permite_una_fecha_con_formato_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/citas', $this->datosValidos(['fecha_cita' => 'mañana por la tarde']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_cita']);
    }

    public function test_un_doctor_no_puede_tener_dos_citas_a_la_misma_hora(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create();
        $fecha = now()->addDays(3)->setTime(10, 0);

        Cita::factory()->create(['doctor_id' => $doctor->id, 'fecha_cita' => $fecha, 'estado' => 'pendiente']);

        $this->postJson('/api/citas', $this->datosValidos([
            'doctor_id' => $doctor->id,
            'fecha_cita' => $fecha->format('Y-m-d H:i:s'),
        ]))->assertStatus(422)->assertJsonValidationErrors(['fecha_cita']);
    }

    public function test_doctores_distintos_si_pueden_tener_citas_a_la_misma_hora(): void
    {
        $this->autenticar();
        $fecha = now()->addDays(3)->setTime(10, 0);

        Cita::factory()->create(['fecha_cita' => $fecha, 'estado' => 'pendiente']);

        // El doctor de datosValidos() es otro, así que el horario está libre para él
        $this->postJson('/api/citas', $this->datosValidos(['fecha_cita' => $fecha->format('Y-m-d H:i:s')]))
            ->assertStatus(201);
    }

    public function test_una_cita_cancelada_libera_el_horario(): void
    {
        $this->autenticar();
        $doctor = Doctor::factory()->create();
        $fecha = now()->addDays(3)->setTime(10, 0);

        Cita::factory()->create(['doctor_id' => $doctor->id, 'fecha_cita' => $fecha, 'estado' => 'cancelada']);

        $this->postJson('/api/citas', $this->datosValidos([
            'doctor_id' => $doctor->id,
            'fecha_cita' => $fecha->format('Y-m-d H:i:s'),
        ]))->assertStatus(201);
    }

    public function test_actualizar_una_cita_conserva_su_propio_horario(): void
    {
        $this->autenticar();
        $fecha = now()->addDays(3)->setTime(10, 0);
        $cita = Cita::factory()->create(['fecha_cita' => $fecha, 'estado' => 'pendiente']);

        // Reenviar su misma fecha NO debe contar como choque consigo misma
        $this->putJson("/api/citas/{$cita->id}", ['fecha_cita' => $fecha->format('Y-m-d H:i:s')])
            ->assertOk();
    }
}

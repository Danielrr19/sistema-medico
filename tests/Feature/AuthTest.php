<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase; // base limpia en cada prueba

    public function test_un_usuario_puede_registrarse(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Daniel',
            'email' => 'daniel@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201) // 201 = creado
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'daniel@example.com']);
    }

    public function test_el_registro_falla_con_datos_invalidos(): void
    {
        User::factory()->create(['email' => 'repetido@example.com']);

        // Email repetido y sin confirmar contraseña: debe dar 422
        $this->postJson('/api/register', [
            'name' => 'Otro',
            'email' => 'repetido@example.com',
            'password' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_un_usuario_puede_iniciar_sesion(): void
    {
        User::factory()->create(['email' => 'daniel@example.com', 'password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => 'daniel@example.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['user' => ['id', 'email'], 'token']);
    }

    public function test_el_login_falla_con_contrasena_incorrecta(): void
    {
        User::factory()->create(['email' => 'daniel@example.com']);

        $this->postJson('/api/login', [
            'email' => 'daniel@example.com',
            'password' => 'contrasena-equivocada',
        ])->assertStatus(401); // 401 = no autorizado
    }

    public function test_una_ruta_protegida_rechaza_a_quien_no_tiene_token(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_un_usuario_autenticado_puede_ver_su_perfil(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()->assertJson(['email' => $user->email]);
    }

    public function test_un_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        // El token debe haberse borrado de la base
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
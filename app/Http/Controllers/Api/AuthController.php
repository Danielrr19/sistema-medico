<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Registrar un usuario',
        description: 'Crea el usuario y devuelve su primer token.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Daniel'),
                new OA\Property(property: 'email', type: 'string', example: 'daniel@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123', description: 'Mínimo 8 caracteres'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/Usuario'),
                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
            ])),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($datos);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Iniciar sesión',
        description: 'Verifica las credenciales y devuelve un token nuevo.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'daniel@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Sesión iniciada', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/Usuario'),
                new OA\Property(property: 'token', type: 'string', example: '2|xyz789...'),
            ])),
            new OA\Response(response: 401, description: 'Credenciales incorrectas', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Credenciales incorrectas.'),
            ])),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credenciales['email'])->first();

        // Mismo mensaje si falla correo o contraseña (no revela cuál estaba mal)
        if (! $user || ! Hash::check($credenciales['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Cerrar sesión',
        description: 'Invalida el token con el que se hizo la petición.',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Sesión cerrada', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente.'),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    #[OA\Get(
        path: '/user',
        summary: 'Ver el usuario autenticado',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Datos del usuario', content: new OA\JsonContent(ref: '#/components/schemas/Usuario')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}

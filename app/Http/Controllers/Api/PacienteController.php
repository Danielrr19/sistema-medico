<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class PacienteController extends Controller
{
    #[OA\Get(
        path: '/pacientes',
        summary: 'Listar pacientes',
        description: 'Lista paginada (10 por página), los más nuevos primero.',
        tags: ['Pacientes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Número de página', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de pacientes', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Paciente')),
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 25),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(Paciente::latest()->paginate(10));
    }

    #[OA\Post(
        path: '/pacientes',
        summary: 'Crear un paciente',
        tags: ['Pacientes'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PacienteInput')),
        responses: [
            new OA\Response(response: 201, description: 'Paciente creado', content: new OA\JsonContent(ref: '#/components/schemas/Paciente')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function store(StorePacienteRequest $request): JsonResponse
    {
        $paciente = Paciente::create($request->validated());

        return response()->json($paciente, 201);
    }

    #[OA\Get(
        path: '/pacientes/{paciente}',
        summary: 'Ver un paciente',
        tags: ['Pacientes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'paciente', in: 'path', required: true, description: 'ID del paciente', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del paciente', content: new OA\JsonContent(ref: '#/components/schemas/Paciente')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function show(Paciente $paciente): JsonResponse
    {
        return response()->json($paciente);
    }

    #[OA\Put(
        path: '/pacientes/{paciente}',
        summary: 'Actualizar un paciente',
        description: 'Se pueden enviar solo los campos que cambian.',
        tags: ['Pacientes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'paciente', in: 'path', required: true, description: 'ID del paciente', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PacienteInput')),
        responses: [
            new OA\Response(response: 200, description: 'Paciente actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Paciente')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function update(UpdatePacienteRequest $request, Paciente $paciente): JsonResponse
    {
        $paciente->update($request->validated());

        return response()->json($paciente);
    }

    #[OA\Delete(
        path: '/pacientes/{paciente}',
        summary: 'Eliminar un paciente',
        description: 'También elimina sus citas.',
        tags: ['Pacientes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'paciente', in: 'path', required: true, description: 'ID del paciente', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Eliminado (sin contenido)'),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function destroy(Paciente $paciente): Response
    {
        $paciente->delete();

        return response()->noContent();
    }
}

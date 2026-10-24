<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CitaRequest;
use App\Models\Cita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class CitaController extends Controller
{
    #[OA\Get(
        path: '/citas',
        summary: 'Listar citas',
        description: 'Lista paginada (10 por página) con paciente y doctor incluidos.',
        tags: ['Citas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Número de página', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de citas', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Cita')),
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 25),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            Cita::with(['paciente', 'doctor'])->latest('fecha_cita')->paginate(10)
        );
    }

    #[OA\Post(
        path: '/citas',
        summary: 'Crear una cita',
        description: 'Valida que paciente y doctor existan, que la fecha sea futura y que el doctor no tenga otra cita (no cancelada) a esa hora.',
        tags: ['Citas'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CitaInput')),
        responses: [
            new OA\Response(response: 201, description: 'Cita creada', content: new OA\JsonContent(ref: '#/components/schemas/Cita')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function store(CitaRequest $request): JsonResponse
    {
        // refresh() trae el estado 'pendiente' que pone la base por defecto
        $cita = Cita::create($request->validated())->refresh();

        return response()->json($cita->load(['paciente', 'doctor']), 201);
    }

    #[OA\Get(
        path: '/citas/{cita}',
        summary: 'Ver una cita',
        tags: ['Citas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'cita', in: 'path', required: true, description: 'ID de la cita', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos de la cita', content: new OA\JsonContent(ref: '#/components/schemas/Cita')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function show(Cita $cita): JsonResponse
    {
        return response()->json($cita->load(['paciente', 'doctor']));
    }

    #[OA\Put(
        path: '/citas/{cita}',
        summary: 'Actualizar una cita',
        description: 'Se pueden enviar solo los campos que cambian (por ejemplo, solo el estado).',
        tags: ['Citas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'cita', in: 'path', required: true, description: 'ID de la cita', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CitaInput')),
        responses: [
            new OA\Response(response: 200, description: 'Cita actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Cita')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function update(CitaRequest $request, Cita $cita): JsonResponse
    {
        $cita->update($request->validated());

        return response()->json($cita->load(['paciente', 'doctor']));
    }

    #[OA\Delete(
        path: '/citas/{cita}',
        summary: 'Eliminar una cita',
        tags: ['Citas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'cita', in: 'path', required: true, description: 'ID de la cita', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Eliminada (sin contenido)'),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function destroy(Cita $cita): Response
    {
        $cita->delete();

        return response()->noContent();
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class DoctorController extends Controller
{
    #[OA\Get(
        path: '/doctores',
        summary: 'Listar doctores',
        description: 'Lista paginada (10 por página), los más nuevos primero.',
        tags: ['Doctores'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Número de página', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de doctores', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Doctor')),
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 25),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(Doctor::latest()->paginate(10));
    }

    #[OA\Post(
        path: '/doctores',
        summary: 'Crear un doctor',
        tags: ['Doctores'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DoctorInput')),
        responses: [
            new OA\Response(response: 201, description: 'Doctor creado', content: new OA\JsonContent(ref: '#/components/schemas/Doctor')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        return response()->json(Doctor::create($request->validated()), 201);
    }

    #[OA\Get(
        path: '/doctores/{doctor}',
        summary: 'Ver un doctor',
        tags: ['Doctores'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'doctor', in: 'path', required: true, description: 'ID del doctor', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del doctor', content: new OA\JsonContent(ref: '#/components/schemas/Doctor')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json($doctor);
    }

    #[OA\Put(
        path: '/doctores/{doctor}',
        summary: 'Actualizar un doctor',
        description: 'Se pueden enviar solo los campos que cambian.',
        tags: ['Doctores'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'doctor', in: 'path', required: true, description: 'ID del doctor', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DoctorInput')),
        responses: [
            new OA\Response(response: 200, description: 'Doctor actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Doctor')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/ErrorValidacion', response: 422),
        ]
    )]
    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor->update($request->validated());

        return response()->json($doctor);
    }

    #[OA\Delete(
        path: '/doctores/{doctor}',
        summary: 'Eliminar un doctor',
        description: 'También elimina sus citas.',
        tags: ['Doctores'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'doctor', in: 'path', required: true, description: 'ID del doctor', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Eliminado (sin contenido)'),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
        ]
    )]
    public function destroy(Doctor $doctor): Response
    {
        $doctor->delete();

        return response()->noContent();
    }
}
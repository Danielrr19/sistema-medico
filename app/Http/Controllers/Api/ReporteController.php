<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ReporteController extends Controller
{
    #[OA\Get(
        path: '/reportes/citas-por-estado',
        summary: 'Reporte de citas por estado',
        description: 'Cuenta las citas de cada estado. Los estados sin citas aparecen con 0.',
        tags: ['Reportes'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Conteo por estado', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'total', type: 'integer', example: 3),
                new OA\Property(property: 'por_estado', type: 'object', example: [
                    'pendiente' => 2, 'confirmada' => 1, 'completada' => 0, 'cancelada' => 0,
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function citasPorEstado(): JsonResponse
    {
        // GROUP BY estado -> ['pendiente' => 2, 'confirmada' => 1, ...]
        $conteos = Cita::query()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Aseguramos que aparezcan los 4 estados, aunque valgan 0
        $porEstado = [];
        foreach (['pendiente', 'confirmada', 'completada', 'cancelada'] as $estado) {
            $porEstado[$estado] = (int) ($conteos[$estado] ?? 0);
        }

        return response()->json([
            'total' => array_sum($porEstado),
            'por_estado' => $porEstado,
        ]);
    }

    #[OA\Get(
        path: '/reportes/citas-por-doctor',
        summary: 'Reporte de citas por doctor',
        description: 'Cantidad de citas de cada doctor, del que más tiene al que menos. Incluye doctores sin citas.',
        tags: ['Reportes'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Citas por doctor', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Dr. Carlos Rivas'),
                    new OA\Property(property: 'especialidad', type: 'string', example: 'Cardiología'),
                    new OA\Property(property: 'total_citas', type: 'integer', example: 3),
                ], type: 'object')),
            ])),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ]
    )]
    public function citasPorDoctor(): JsonResponse
    {
        $doctores = Doctor::withCount('citas')   // agrega la columna citas_count
            ->orderByDesc('citas_count')
            ->get()
            ->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'nombre' => $doctor->nombre,
                'especialidad' => $doctor->especialidad,
                'total_citas' => $doctor->citas_count,
            ]);

        return response()->json(['data' => $doctores]);
    }
}
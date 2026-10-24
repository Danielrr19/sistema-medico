<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

// Información general de la API
#[OA\Info(
    version: '1.0.0',
    title: 'API de Gestión Médica',
    description: 'API RESTful para administrar pacientes, doctores y citas. Autenticación con Laravel Sanctum (token Bearer).'
)]
// Las rutas se documentan sin "/api" porque el servidor ya lo incluye
#[OA\Server(url: '/api', description: 'Servidor local')]
#[OA\Components(
    // El "gafete": el token Bearer que devuelve /login o /register
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'sanctum',
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'Token',
            description: 'Pega el token que devuelve /login o /register (sin la palabra Bearer).'
        ),
    ],
    // SCHEMAS: la forma de cada dato, para reutilizarla en los endpoints
    schemas: [
        new OA\Schema(schema: 'Usuario', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Daniel'),
            new OA\Property(property: 'email', type: 'string', example: 'daniel@example.com'),
        ]),

        new OA\Schema(schema: 'Paciente', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'nombre', type: 'string', example: 'Ana Martínez'),
            new OA\Property(property: 'email', type: 'string', example: 'ana@example.com'),
            new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '7345-1290'),
            new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1990-05-20'),
            new OA\Property(property: 'historial_medico', type: 'string', nullable: true, example: 'Sin antecedentes.'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]),
        // Lo que se ENVÍA al crear o actualizar un paciente
        new OA\Schema(schema: 'PacienteInput', required: ['nombre', 'email', 'fecha_nacimiento'], properties: [
            new OA\Property(property: 'nombre', type: 'string', example: 'Ana Martínez'),
            new OA\Property(property: 'email', type: 'string', example: 'ana@example.com'),
            new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '7345-1290'),
            new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1990-05-20', description: 'Debe ser anterior a hoy'),
            new OA\Property(property: 'historial_medico', type: 'string', nullable: true, example: 'Sin antecedentes.'),
        ]),

        new OA\Schema(schema: 'Doctor', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'nombre', type: 'string', example: 'Dr. Carlos Rivas'),
            new OA\Property(property: 'email', type: 'string', example: 'carlos@example.com'),
            new OA\Property(property: 'especialidad', type: 'string', example: 'Cardiología'),
            new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '7345-1290'),
            new OA\Property(property: 'cualificaciones', type: 'string', nullable: true, example: 'Especialista en cardiología clínica.'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]),
        new OA\Schema(schema: 'DoctorInput', required: ['nombre', 'email', 'especialidad'], properties: [
            new OA\Property(property: 'nombre', type: 'string', example: 'Dr. Carlos Rivas'),
            new OA\Property(property: 'email', type: 'string', example: 'carlos@example.com'),
            new OA\Property(property: 'especialidad', type: 'string', example: 'Cardiología'),
            new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '7345-1290'),
            new OA\Property(property: 'cualificaciones', type: 'string', nullable: true, example: 'Especialista en cardiología clínica.'),
        ]),

        new OA\Schema(schema: 'Cita', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'paciente_id', type: 'integer', example: 1),
            new OA\Property(property: 'doctor_id', type: 'integer', example: 1),
            new OA\Property(property: 'fecha_cita', type: 'string', format: 'date-time', example: '2026-11-05T10:00:00.000000Z'),
            new OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'confirmada', 'completada', 'cancelada'], example: 'pendiente'),
            new OA\Property(property: 'notas', type: 'string', nullable: true, example: 'Primera consulta'),
            new OA\Property(property: 'paciente', ref: '#/components/schemas/Paciente'),
            new OA\Property(property: 'doctor', ref: '#/components/schemas/Doctor'),
        ]),
        new OA\Schema(schema: 'CitaInput', required: ['paciente_id', 'doctor_id', 'fecha_cita'], properties: [
            new OA\Property(property: 'paciente_id', type: 'integer', example: 1, description: 'Debe existir'),
            new OA\Property(property: 'doctor_id', type: 'integer', example: 1, description: 'Debe existir'),
            new OA\Property(property: 'fecha_cita', type: 'string', example: '2026-11-05 10:00:00', description: 'Fecha futura; el doctor no puede tener otra cita a esa hora'),
            new OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'confirmada', 'completada', 'cancelada'], example: 'pendiente'),
            new OA\Property(property: 'notas', type: 'string', nullable: true, example: 'Primera consulta'),
        ]),
    ],
    // Respuestas de error que se repiten en muchos endpoints
    responses: [
        new OA\Response(response: 'NoAutenticado', description: 'Falta el token o no es válido', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
        ])),
        new OA\Response(response: 'NoEncontrado', description: 'El registro no existe', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'message', type: 'string', example: 'No query results for model.'),
        ])),
        new OA\Response(response: 'ErrorValidacion', description: 'Los datos enviados no son válidos', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'message', type: 'string', example: 'The email field is required.'),
            new OA\Property(property: 'errors', type: 'object', example: ['email' => ['The email field is required.']]),
        ])),
    ],
)]
class OpenApiSpec
{
    // Clase vacía a propósito: solo sirve de "percha" para las anotaciones de arriba
}

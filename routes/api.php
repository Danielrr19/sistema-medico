<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\ReporteController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas: exigen token (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('pacientes', PacienteController::class);
    Route::apiResource('doctores', DoctorController::class)->parameters(['doctores' => 'doctor']);
    Route::apiResource('citas', CitaController::class);

    // Reportes
    Route::get('/reportes/citas-por-estado', [ReporteController::class, 'citasPorEstado']);
    Route::get('/reportes/citas-por-doctor', [ReporteController::class, 'citasPorDoctor']);
});

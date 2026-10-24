<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            // Llave foránea: paciente_id solo puede ser un id que exista en 'pacientes'.
            // cascadeOnDelete = si borras al paciente, se borran sus citas
            // (como tirar un expediente y todas sus hojas con él).
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();

            // Lo mismo para el doctor (tabla 'doctores')
            $table->foreignId('doctor_id')->constrained('doctores')->cascadeOnDelete();

            $table->dateTime('fecha_cita');                // fecha y hora de la cita

            // Valores esperados: pendiente, confirmada, completada, cancelada.
            // default = si no se indica, queda como 'pendiente'
            $table->string('estado', 20)->default('pendiente');

            $table->text('notas')->nullable();             // observaciones opcionales
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};

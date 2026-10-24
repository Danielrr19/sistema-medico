<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ponemos 'doctores' a mano porque Laravel pluraliza en inglés y diría "doctors"
        Schema::create('doctores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();             // un correo por doctor
            $table->string('especialidad');                // obligatoria (ej. Cardiología)
            $table->string('telefono', 20)->nullable();
            $table->text('cualificaciones')->nullable();   // títulos, certificaciones, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctores');
    }
};
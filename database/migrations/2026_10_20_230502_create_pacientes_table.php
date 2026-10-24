<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Clase anónima: Laravel ejecuta esta migración al correr "php artisan migrate"
return new class extends Migration
{
    // up() = lo que se hace al migrar (construir la tabla)
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();                                  // id autoincremental (llave primaria)
            $table->string('nombre');                      // texto corto (hasta 255 caracteres)
            $table->string('email')->unique();             // unique = no puede repetirse entre pacientes
            $table->string('telefono', 20)->nullable();    // máx. 20 caracteres; nullable = es opcional
            $table->date('fecha_nacimiento');              // solo fecha (sin hora)
            $table->text('historial_medico')->nullable();  // texto largo y opcional
            $table->timestamps();                          // crea created_at y updated_at automáticamente
        });
    }

    // down() = lo que se hace al revertir (deshacer lo de up)
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
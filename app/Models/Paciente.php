<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasFactory; // permite usar Paciente::factory() en las pruebas

    // $fillable = lista de campos que se pueden guardar en masa con create()/update().
    // Protege contra que alguien envíe campos que no debe (ej. 'id').
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'fecha_nacimiento',
        'historial_medico',
    ];

    // $casts = convierte el dato automáticamente al leerlo.
    // Aquí la fecha se vuelve un objeto de fecha (Carbon) fácil de manipular.
    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // RELACIÓN hasMany: "un paciente tiene MUCHAS citas".
    // Uso: $paciente->citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    // Laravel buscaría la tabla "doctors" (en inglés).
    // Con esto le decimos cuál es el nombre real de nuestra tabla.
    protected $table = 'doctores';

    protected $fillable = [
        'nombre',
        'email',
        'especialidad',
        'telefono',
        'cualificaciones',
    ];

    // RELACIÓN hasMany: "un doctor tiene MUCHAS citas".
    // Uso: $doctor->citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}

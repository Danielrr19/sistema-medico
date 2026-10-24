<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha_cita',
        'estado',
        'notas',
    ];

    // fecha_cita se convierte en objeto de fecha con hora (Carbon)
    protected $casts = [
        'fecha_cita' => 'datetime',
    ];

    // RELACIÓN belongsTo: "cada cita PERTENECE a un paciente".
    // Es el camino inverso de hasMany. Uso: $cita->paciente
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    // "cada cita PERTENECE a un doctor". Uso: $cita->doctor
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}

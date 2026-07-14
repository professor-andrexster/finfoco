<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStep extends Model
{
    protected $fillable = ['appointment_id', 'titulo', 'concluido'];
    protected $casts    = ['concluido' => 'boolean'];

    public function appointment() { return $this->belongsTo(Appointment::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'titulo',
        'data_lembrete',
        'concluido',
    ];

    protected $casts = [
        'data_lembrete' => 'date',
        'concluido'     => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['user_id', 'titulo', 'data_lembrete', 'concluido'];
    protected $casts    = ['data_lembrete' => 'date', 'concluido' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}

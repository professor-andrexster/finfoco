<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['user_id', 'titulo', 'data', 'hora', 'lembrete_min', 'concluido'];

    protected $casts = [
        'data'         => 'date',
        'concluido'    => 'boolean',
        'lembrete_min' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeDoDia(Builder $query, \DateTimeInterface|string $data): Builder
    {
        return $query->whereDate('data', $data)
            ->orderByRaw('hora IS NULL DESC')   // "dia todo" primeiro
            ->orderBy('hora');
    }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}

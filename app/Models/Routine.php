<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = ['user_id', 'titulo', 'hora', 'dias'];

    public function user()   { return $this->belongsTo(User::class); }
    public function checks() { return $this->hasMany(RoutineCheck::class); }

    /** Rotinas agendadas para o dia da semana da data (dias: seg..dom). */
    public function scopeDoDia(Builder $query, Carbon $data): Builder
    {
        // SUBSTRING é 1-indexado; dayOfWeekIso: 1 = segunda ... 7 = domingo
        return $query->whereRaw('SUBSTRING(dias, ?, 1) = ?', [$data->dayOfWeekIso, '1'])
            ->orderByRaw('hora IS NULL DESC')
            ->orderBy('hora');
    }

    public function agendadaEm(Carbon $data): bool
    {
        return substr($this->dias, $data->dayOfWeekIso - 1, 1) === '1';
    }

    public function feitaEm(Carbon $data): bool
    {
        return $this->checks->contains(fn($c) => $c->data->isSameDay($data));
    }

    /**
     * Sequência de dias agendados e cumpridos, contando de trás pra frente.
     * O dia atual ainda pendente não quebra a sequência.
     */
    public function streak(): int
    {
        $feitos = $this->checks->map(fn($c) => $c->data->toDateString())->flip();
        $cursor = today();
        $streak = 0;

        if ($this->agendadaEm($cursor) && !isset($feitos[$cursor->toDateString()])) {
            $cursor = $cursor->subDay(); // hoje ainda dá tempo — não quebra
        }

        for ($i = 0; $i < 366; $i++) {
            if ($this->agendadaEm($cursor)) {
                if (!isset($feitos[$cursor->toDateString()])) break;
                $streak++;
            }
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}

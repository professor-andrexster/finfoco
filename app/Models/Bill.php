<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'user_id', 'tipo', 'descricao', 'valor', 'categoria_id',
        'vencimento', 'status', 'recorrente', 'recorrencia', 'pago_em',
        'parcelas_total', 'parcela_atual',
    ];

    protected $casts = [
        'vencimento'     => 'date',
        'pago_em'        => 'date',
        'valor'          => 'decimal:2',
        'recorrente'     => 'boolean',
        'parcelas_total' => 'integer',
        'parcela_atual'  => 'integer',
    ];

    public function categoria() { return $this->belongsTo(Category::class, 'categoria_id'); }
    public function user()      { return $this->belongsTo(User::class); }

    public function isParcelado(): bool
    {
        return $this->parcelas_total !== null && $this->parcelas_total > 1;
    }

    public function parcelasRestantes(): int
    {
        if (!$this->isParcelado()) return 0;
        return $this->parcelas_total - $this->parcela_atual;
    }

    public function calcularProximaOcorrencia(): Carbon
    {
        return match ($this->recorrencia) {
            'semanal' => $this->vencimento->copy()->addWeek(),
            'anual'   => $this->vencimento->copy()->addYear(),
            default   => $this->vencimento->copy()->addMonth(),
        };
    }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (auth()->check()) $m->user_id ??= auth()->id();
        });
    }
}

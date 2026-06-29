<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'descricao',
        'valor',
        'categoria_id',
        'vencimento',
        'status',
        'recorrente',
        'recorrencia',
        'pago_em',
    ];

    protected $casts = [
        'valor'      => 'decimal:2',
        'vencimento' => 'date',
        'pago_em'    => 'date',
        'recorrente' => 'boolean',
    ];

    // Apenas created_at (sem updated_at na tabela)
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['pendente', 'atrasado']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('vencimento', '<', Carbon::today())
                     ->whereIn('status', ['pendente', 'atrasado']);
    }

    public function scopeVencendoEm3Dias($query)
    {
        $hoje   = Carbon::today();
        $limite = Carbon::today()->addDays(3);
        return $query->whereBetween('vencimento', [$hoje, $limite])
                     ->whereIn('status', ['pendente', 'atrasado']);
    }

    public function calcularProximaOcorrencia(): Carbon
    {
        $base = $this->vencimento ?? Carbon::today();

        return match ($this->recorrencia) {
            'mensal'  => $base->copy()->addMonth(),
            'semanal' => $base->copy()->addWeek(),
            'anual'   => $base->copy()->addYear(),
            default   => $base->copy()->addMonth(),
        };
    }
}

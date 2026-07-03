<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'user_id', 'tipo', 'descricao', 'valor', 'valor_pago', 'categoria_id',
        'vencimento', 'status', 'recorrente', 'recorrencia', 'pago_em',
        'parcelas_total', 'parcela_atual',
    ];

    protected $casts = [
        'vencimento'     => 'date',
        'pago_em'        => 'date',
        'valor'          => 'decimal:2',
        'valor_pago'     => 'decimal:2',
        'recorrente'     => 'boolean',
        'parcelas_total' => 'integer',
        'parcela_atual'  => 'integer',
    ];

    /** Quanto ainda falta pagar/receber desta conta (valor − abatimentos parciais). */
    public function restante(): float
    {
        return max(0, (float) $this->valor - (float) $this->valor_pago);
    }

    public function categoria() { return $this->belongsTo(Category::class, 'categoria_id'); }
    public function user()      { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    /** Valor da conta convertido pro equivalente mensal (semanal ×52/12, anual ÷12). */
    public function valorMensalNormalizado(): float
    {
        return (float) $this->valor * match ($this->recorrencia) {
            'semanal' => 52 / 12,
            'anual'   => 1 / 12,
            default   => 1, // mensal
        };
    }

    /**
     * Custo fixo mensal do usuário: soma normalizada de todas as contas
     * recorrentes. Deduplica por descrição (uma recorrente paga gera a próxima
     * ocorrência, criando histórico duplicado), mantendo a mais recente.
     *
     * @return array{total: float, qtd: int, contas: \Illuminate\Support\Collection}
     */
    public static function custoFixoMensal(int $userId): array
    {
        $recorrentes = static::with('categoria')
            ->where('user_id', $userId)
            ->where('recorrente', true)
            ->where('tipo', 'pagar')
            ->orderByDesc('vencimento')
            ->get()
            ->unique('descricao')
            ->values();

        return [
            'total'  => round($recorrentes->sum(fn($b) => $b->valorMensalNormalizado()), 2),
            'qtd'    => $recorrentes->count(),
            'contas' => $recorrentes,
        ];
    }

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

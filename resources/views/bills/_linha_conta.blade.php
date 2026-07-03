@php
    use App\Helpers\DateHelper;
    $semaforo = DateHelper::semaforo($bill->vencimento);
    $corSema  = match($semaforo) { 'red' => '#DC2626', 'yellow' => '#D97706', default => '#16A34A' };
    $emoji    = match($semaforo) { 'red' => '🔴', 'yellow' => '🟡', default => '🟢' };
    $temAbatimento = (float) $bill->valor_pago > 0;
    $restante = $bill->restante();
@endphp
<div x-data="{ abater: false }">
    <div class="flex items-center gap-4 px-5 py-4">

        <span class="text-xl shrink-0">{{ $emoji }}</span>

        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
             style="background:{{ ($bill->categoria?->cor ?? '#6366F1') }}18">
            <i data-lucide="{{ $bill->categoria?->icone ?? 'tag' }}" class="w-4 h-4"
               style="color:{{ $bill->categoria?->cor ?? '#6366F1' }}"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-baseline gap-2">
                <span class="font-semibold text-foco-text">{{ $bill->descricao }}</span>
                @if($bill->recorrente)
                <span class="text-xs text-foco-muted flex items-center gap-1">
                    <i data-lucide="repeat" class="w-3 h-3"></i>
                    {{ ucfirst($bill->recorrencia ?? 'mensal') }}
                </span>
                @endif
            </div>
            <p class="text-xs mt-0.5" style="color:{{ $corSema }}">
                {{ DateHelper::formatarDataRelativa($bill->vencimento) }}
                @if($bill->categoria) <span class="text-foco-muted">· {{ $bill->categoria->nome }}</span> @endif
            </p>
        </div>

        <div class="text-right shrink-0">
            <p class="font-bold" style="color:{{ $bill->tipo === 'pagar' ? '#DC2626' : '#16A34A' }}; font-size:1.05rem">
                {{ $bill->tipo === 'pagar' ? '−' : '+' }}&nbsp;R$&nbsp;{{ number_format($restante, 2, ',', '.') }}
            </p>
            @if($temAbatimento)
            <p class="text-xs text-foco-muted mt-0.5">
                de R$&nbsp;{{ number_format($bill->valor, 2, ',', '.') }} · já abatido R$&nbsp;{{ number_format($bill->valor_pago, 2, ',', '.') }}
            </p>
            @elseif($bill->recorrente && $bill->recorrencia && $bill->recorrencia !== 'mensal')
            <p class="text-xs text-foco-muted mt-0.5">
                ≈ R$&nbsp;{{ number_format($bill->valorMensalNormalizado(), 2, ',', '.') }}/mês
            </p>
            @endif
        </div>

        <div class="flex items-center gap-2 ml-1 shrink-0">
            <form action="{{ route('bills.marcarPago', $bill) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg text-white"
                        style="background:{{ $bill->tipo === 'pagar' ? '#16A34A' : '#6366F1' }}">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    {{ $bill->tipo === 'pagar' ? 'Marcar pago' : 'Marcar recebido' }}
                </button>
            </form>
            <button type="button" @click="abater = !abater"
                    :class="abater ? 'text-foco-accent bg-foco-surface' : 'text-foco-muted'"
                    class="p-2 rounded-lg hover:text-foco-accent hover:bg-foco-surface transition-colors"
                    title="Abater um valor parcial">
                <i data-lucide="circle-minus" class="w-4 h-4"></i>
            </button>
            <a href="{{ route('bills.edit', $bill) }}"
               class="p-2 rounded-lg text-foco-muted hover:text-foco-accent hover:bg-foco-surface transition-colors">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </a>
            <form action="{{ route('bills.destroy', $bill) }}" method="POST"
                  onsubmit="return confirm('Excluir a conta \'{{ addslashes($bill->descricao) }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 text-foco-muted hover:text-foco-saida transition-colors rounded-lg hover:bg-foco-surface">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Pagamento parcial: abater um valor da dívida --}}
    <div x-show="abater" x-cloak style="display:none; border-top:1px dashed #E4E4F0" class="bg-foco-surface">
        <form action="{{ route('bills.pagarParcial', $bill) }}" method="POST"
              class="px-5 py-3 flex items-center gap-3 flex-wrap">
            @csrf
            <span class="text-xs text-foco-muted">
                Restam <strong class="text-foco-text">R$&nbsp;{{ number_format($restante, 2, ',', '.') }}</strong> — quanto vai {{ $bill->tipo === 'pagar' ? 'pagar' : 'receber' }} agora?
            </span>
            <input type="number" name="valor" step="0.01" min="0.01" max="{{ $restante }}"
                   placeholder="0,00" required
                   class="w-32 border border-foco-border rounded-xl px-3 py-2 text-sm font-bold text-foco-text focus:outline-none focus:border-foco-accent">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg text-white"
                    style="background:#6366F1">
                <i data-lucide="circle-minus" class="w-3.5 h-3.5"></i>
                Abater valor
            </button>
        </form>
    </div>
</div>

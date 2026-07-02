@extends('layouts.app')
@section('title', 'Contas')

@section('content')
@php use App\Helpers\DateHelper; @endphp

{{-- Cabeçalho --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-foco-text">Contas</h1>
        <p class="text-sm text-foco-muted mt-0.5">A pagar, parcelamentos e recebimentos</p>
    </div>
    <a href="{{ route('bills.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nova conta
    </a>
</div>

{{-- ════════════════════════════════════════════════════
     SEÇÃO 1 — PARCELAMENTOS ATIVOS
     Cada compra parcelada mostra UMA linha consolidada
     ════════════════════════════════════════════════════ --}}
@if($parcelamentos->isNotEmpty())
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="credit-card" class="w-4 h-4 text-foco-accent"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Parcelamentos</h2>
        <span class="text-xs text-foco-muted ml-auto">{{ $parcelamentos->count() }} compra(s)</span>
    </div>

    <div class="space-y-2">
    @foreach($parcelamentos as $key => $p)
    @php
        $proxima    = $p['proxima'];
        $pagas      = $p['pagas'];
        $total      = $p['total'];
        $pendentes  = $p['pendentes'];
        $pct        = $total > 0 ? round($pagas / $total * 100) : 0;
        $semaforo   = DateHelper::semaforo($proxima->vencimento);
        $corSema    = match($semaforo) { 'red' => '#DC2626', 'yellow' => '#D97706', default => '#16A34A' };
        $totalDivida = $proxima->valor * $pendentes;
    @endphp
    <div x-data="{ expandido: false }" class="card overflow-hidden">

        {{-- Linha principal --}}
        <div class="flex items-center gap-4 px-5 py-4">

            {{-- Ícone categoria --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                 style="background:{{ ($proxima->categoria?->cor ?? '#6366F1') }}18">
                <i data-lucide="{{ $proxima->categoria?->icone ?? 'credit-card' }}" class="w-5 h-5"
                   style="color:{{ $proxima->categoria?->cor ?? '#6366F1' }}"></i>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-baseline gap-2 flex-wrap">
                    <span class="font-semibold text-foco-text">{{ $proxima->descricao }}</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full text-white"
                          style="background:#6366F1">{{ $pagas }}/{{ $total }}x</span>
                </div>
                <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                    {{-- Barra de progresso --}}
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 rounded-full bg-foco-border overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%; background:#6366F1"></div>
                        </div>
                        <span class="text-xs text-foco-muted">{{ $pct }}%</span>
                    </div>
                    <span class="text-xs text-foco-muted">
                        Próx. venc: <strong style="color:{{ $corSema }}">{{ DateHelper::formatarDataRelativa($proxima->vencimento) }}</strong>
                    </span>
                    <span class="text-xs text-foco-muted">
                        Faltam <strong class="text-foco-text">{{ $pendentes }}</strong> parcelas
                    </span>
                </div>
            </div>

            {{-- Valor --}}
            <div class="text-right shrink-0">
                <p class="font-bold text-foco-saida" style="font-size:1.1rem">
                    R$&nbsp;{{ number_format($proxima->valor, 2, ',', '.') }}
                </p>
                <p class="text-xs text-foco-muted mt-0.5">
                    Restante: R$&nbsp;{{ number_format($totalDivida, 2, ',', '.') }}
                </p>
            </div>

            {{-- Ações --}}
            <div class="flex items-center gap-2 ml-2 shrink-0">
                {{-- Pagar próxima parcela --}}
                <form action="{{ route('bills.marcarPago', $proxima) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg text-white"
                            style="background:#16A34A">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Pagar {{ $pagas + 1 }}/{{ $total }}
                    </button>
                </form>
                {{-- Expandir --}}
                <button @click="expandido = !expandido"
                        class="p-2 rounded-lg text-foco-muted hover:text-foco-text hover:bg-foco-surface transition-colors">
                    <i :data-lucide="expandido ? 'chevron-up' : 'chevron-down'" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Detalhe expandível: todas as parcelas pendentes --}}
        <div x-show="expandido" x-cloak style="display:none; border-top:1px solid #E4E4F0">
            <div class="px-5 py-3 bg-foco-surface">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-foco-muted uppercase tracking-wide">Parcelas pendentes</p>
                    {{-- Excluir parcelamento inteiro --}}
                    <form action="{{ route('bills.destroyParcelamento') }}" method="POST"
                          onsubmit="return confirm('Excluir todas as parcelas pendentes de \'{{ addslashes($proxima->descricao) }}\'?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="descricao" value="{{ $proxima->descricao }}">
                        <input type="hidden" name="parcelas_total" value="{{ $total }}">
                        <button type="submit"
                                class="text-xs text-foco-saida font-medium flex items-center gap-1 hover:underline">
                            <i data-lucide="trash-2" class="w-3 h-3"></i> Excluir tudo
                        </button>
                    </form>
                </div>
                <div class="space-y-1.5">
                    @foreach($p['todas'] as $parcela)
                    @php $sem = DateHelper::semaforo($parcela->vencimento); @endphp
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-lg bg-white">
                        <div class="flex items-center gap-2">
                            <span class="text-base leading-none">
                                {!! match($sem) { 'red'=>'🔴','yellow'=>'🟡',default=>'🟢' } !!}
                            </span>
                            <span class="text-xs font-semibold text-foco-muted w-10">{{ $parcela->parcela_atual }}/{{ $parcela->parcelas_total }}</span>
                            <span class="text-sm text-foco-text">{{ DateHelper::formatarDataRelativa($parcela->vencimento) }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-foco-text">R$&nbsp;{{ number_format($parcela->valor, 2, ',', '.') }}</span>
                            <a href="{{ route('bills.edit', $parcela) }}"
                               class="text-foco-muted hover:text-foco-accent transition-colors p-1">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form action="{{ route('bills.destroy', $parcela) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
    @endforeach
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════
     SEÇÃO 2 — CONTAS SIMPLES (pendentes/atrasadas)
     ════════════════════════════════════════════════════ --}}
@if($contasSimples->isNotEmpty())
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="receipt" class="w-4 h-4 text-foco-saida"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Contas e recorrentes</h2>
        <span class="text-xs text-foco-muted ml-auto">{{ $contasSimples->count() }} pendente(s)</span>
    </div>

    <div class="card overflow-hidden divide-y divide-foco-border">
    @foreach($contasSimples as $bill)
    @php
        $semaforo = DateHelper::semaforo($bill->vencimento);
        $corSema  = match($semaforo) { 'red' => '#DC2626', 'yellow' => '#D97706', default => '#16A34A' };
        $emoji    = match($semaforo) { 'red' => '🔴', 'yellow' => '🟡', default => '🟢' };
    @endphp
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
                {{ $bill->tipo === 'pagar' ? '−' : '+' }}&nbsp;R$&nbsp;{{ number_format($bill->valor, 2, ',', '.') }}
            </p>
        </div>

        <div class="flex items-center gap-2 ml-1 shrink-0">
            <form action="{{ route('bills.marcarPago', $bill) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg text-white"
                        style="background:{{ $bill->tipo === 'pagar' ? '#16A34A' : '#6366F1' }}">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    {{ $bill->tipo === 'pagar' ? 'Pago' : 'Recebido' }}
                </button>
            </form>
            <a href="{{ route('bills.edit', $bill) }}"
               class="p-2 rounded-lg text-foco-muted hover:text-foco-accent hover:bg-foco-surface transition-colors">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </a>
            <form action="{{ route('bills.destroy', $bill) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 text-foco-muted hover:text-foco-saida transition-colors rounded-lg hover:bg-foco-surface">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
    </div>
</div>
@endif

{{-- Vazio --}}
@if($parcelamentos->isEmpty() && $contasSimples->isEmpty())
<div class="card px-8 py-16 text-center mb-6">
    <div class="w-14 h-14 rounded-2xl bg-foco-surface mx-auto mb-4 flex items-center justify-center">
        <i data-lucide="check-circle-2" class="w-7 h-7 text-foco-entrada"></i>
    </div>
    <p class="font-semibold text-foco-text mb-1">Tudo em dia!</p>
    <p class="text-sm text-foco-muted mb-5">Nenhuma conta pendente.</p>
    <a href="{{ route('bills.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Adicionar conta
    </a>
</div>
@endif

{{-- ════════════════════════════════════════════════════
     SEÇÃO 3 — PAGAS RECENTEMENTE
     ════════════════════════════════════════════════════ --}}
@if($contasPagas->isNotEmpty())
<div>
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="check-circle" class="w-4 h-4 text-foco-entrada"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Pagas recentemente</h2>
    </div>
    <div class="card overflow-hidden divide-y divide-foco-border">
    @foreach($contasPagas as $bill)
    <div class="flex items-center gap-4 px-5 py-3 opacity-60">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
             style="background:#16A34A18">
            <i data-lucide="check" class="w-4 h-4" style="color:#16A34A"></i>
        </div>
        <div class="flex-1 min-w-0">
            <span class="text-sm font-medium text-foco-text">{{ $bill->descricao }}</span>
            @if($bill->isParcelado())
            <span class="text-xs text-foco-muted ml-1">{{ $bill->parcela_atual }}/{{ $bill->parcelas_total }}</span>
            @endif
            <p class="text-xs text-foco-muted mt-0.5">{{ $bill->pago_em ? DateHelper::formatarDataRelativa($bill->pago_em) : '' }}</p>
        </div>
        <p class="text-sm font-semibold text-foco-muted shrink-0">
            R$&nbsp;{{ number_format($bill->valor, 2, ',', '.') }}
        </p>
    </div>
    @endforeach
    </div>
</div>
@endif

@push('scripts')
<script>setTimeout(() => lucide.createIcons(), 100);</script>
@endpush
@endsection

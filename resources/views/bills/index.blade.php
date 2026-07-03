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

{{-- RESUMO GERAL: total pendente + base fixa mensal --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="card p-4">
        <div class="flex items-center gap-1.5 mb-1.5">
            <i data-lucide="arrow-up-circle" class="w-3.5 h-3.5" style="color:#DC2626"></i>
            <p class="text-xs text-foco-muted font-medium">Total a pagar (pendente)</p>
        </div>
        <p class="text-xl font-bold" style="color:#DC2626; letter-spacing:-0.02em">
            −&nbsp;R$&nbsp;{{ number_format($totalAPagar, 2, ',', '.') }}
        </p>
    </div>
    <div class="card p-4">
        <div class="flex items-center gap-1.5 mb-1.5">
            <i data-lucide="arrow-down-circle" class="w-3.5 h-3.5" style="color:#16A34A"></i>
            <p class="text-xs text-foco-muted font-medium">Total a receber</p>
        </div>
        <p class="text-xl font-bold" style="color:#16A34A; letter-spacing:-0.02em">
            +&nbsp;R$&nbsp;{{ number_format($totalAReceber, 2, ',', '.') }}
        </p>
    </div>
    <div class="card p-4" style="border-top:3px solid #6366F1">
        <div class="flex items-center gap-1.5 mb-1.5">
            <i data-lucide="repeat" class="w-3.5 h-3.5" style="color:#6366F1"></i>
            <p class="text-xs text-foco-muted font-medium">Custo fixo mensal</p>
        </div>
        <p class="text-xl font-bold" style="color:#6366F1; letter-spacing:-0.02em">
            R$&nbsp;{{ number_format($custoFixo['total'], 2, ',', '.') }}<span class="text-sm font-semibold text-foco-muted">/mês</span>
        </p>
        @if($custoFixo['qtd'] > 0)
        <p class="text-[11px] text-foco-muted mt-0.5">{{ $custoFixo['qtd'] }} conta(s) fixa(s)</p>
        @endif
    </div>
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
        $totalDivida = $p['restante_total'];
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
                        <input type="hidden" name="valor" value="{{ $proxima->valor }}">
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
     SEÇÃO 2 — CONTAS FIXAS MENSAIS (recorrentes)
     A base do que sai todo mês: aluguel, água, luz, internet...
     ════════════════════════════════════════════════════ --}}
@if($contasFixas->isNotEmpty() || $custoFixo['qtd'] > 0)
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="repeat" class="w-4 h-4 text-foco-accent"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Contas fixas mensais</h2>
        <span class="ml-auto text-xs font-bold px-2.5 py-1 rounded-full"
              style="color:#6366F1; background:#EEF2FF">
            R$&nbsp;{{ number_format($custoFixo['total'], 2, ',', '.') }}/mês
        </span>
    </div>

    @if($contasFixas->isNotEmpty())
    <div class="card overflow-hidden divide-y divide-foco-border">
        @foreach($contasFixas as $bill)
            @include('bills._linha_conta', ['bill' => $bill])
        @endforeach
    </div>
    @else
    <div class="card px-5 py-4 text-sm text-foco-muted flex items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-foco-entrada"></i>
        Todas as contas fixas deste ciclo já foram pagas.
    </div>
    @endif
</div>
@endif

{{-- ════════════════════════════════════════════════════
     SEÇÃO 3 — CONTAS AVULSAS (pendentes/atrasadas, não recorrentes)
     ════════════════════════════════════════════════════ --}}
@if($contasAvulsas->isNotEmpty())
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="receipt" class="w-4 h-4 text-foco-saida"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Contas avulsas</h2>
        <span class="text-xs text-foco-muted ml-auto">{{ $contasAvulsas->count() }} pendente(s)</span>
    </div>

    <div class="card overflow-hidden divide-y divide-foco-border">
        @foreach($contasAvulsas as $bill)
            @include('bills._linha_conta', ['bill' => $bill])
        @endforeach
    </div>
</div>
@endif

{{-- Vazio --}}
@if($parcelamentos->isEmpty() && $contasFixas->isEmpty() && $contasAvulsas->isEmpty() && $custoFixo['qtd'] === 0)
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

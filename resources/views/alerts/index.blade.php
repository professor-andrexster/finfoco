@extends('layouts.app')
@section('title', 'Alertas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-foco-text">Alertas</h1>
        <p class="text-sm text-foco-muted mt-0.5">Limites de gasto por categoria</p>
    </div>
    <a href="{{ route('alerts.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Novo alerta
    </a>
</div>

@if($alertas->isEmpty())
<div class="card px-8 py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-foco-surface mx-auto mb-4 flex items-center justify-center">
        <i data-lucide="bell-off" class="w-7 h-7 text-foco-muted"></i>
    </div>
    <p class="font-semibold text-foco-text mb-1">Nenhum alerta configurado</p>
    <p class="text-sm text-foco-muted mb-5">Defina limites de gasto e seja avisado no dashboard.</p>
    <a href="{{ route('alerts.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Criar alerta
    </a>
</div>
@else
<div class="space-y-3">
    @foreach($alertas as $alerta)
    @php
        $pct     = $alerta->limite_valor > 0 ? min(100, round(($alerta->gasto_atual / $alerta->limite_valor) * 100)) : 0;
        $barCor  = $pct >= 100 ? '#DC2626' : ($pct >= 80 ? '#D97706' : '#16A34A');
        $periodo = match($alerta->periodo) { 'dia' => 'hoje', 'semana' => 'esta semana', default => 'este mês' };
        $restante = max(0, $alerta->limite_valor - $alerta->gasto_atual);
    @endphp
    <div class="card overflow-hidden {{ !$alerta->ativo ? 'opacity-50' : '' }}">
        <div class="px-5 py-4">
            <div class="flex items-center gap-3 mb-3">
                {{-- Ícone categoria --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background:{{ $alerta->categoria->cor }}18">
                    <i data-lucide="{{ $alerta->categoria->icone }}" class="w-5 h-5"
                       style="color:{{ $alerta->categoria->cor }}"></i>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="font-semibold text-foco-text">{{ $alerta->categoria->nome }}</span>
                        <span class="text-xs text-foco-muted">
                            {{ ucfirst($periodo) }}
                            @if(!$alerta->ativo)
                                · <span style="color:#D97706">pausado</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-sm font-bold" style="color:{{ $barCor }}">
                            R$&nbsp;{{ number_format($alerta->gasto_atual, 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-foco-muted">de
                            R$&nbsp;{{ number_format($alerta->limite_valor, 2, ',', '.') }}
                        </span>
                        @if($pct < 100)
                        <span class="text-xs text-foco-muted ml-1">
                            · faltam R$&nbsp;{{ number_format($restante, 2, ',', '.') }}
                        </span>
                        @else
                        <span class="text-xs font-semibold ml-1" style="color:#DC2626">· LIMITE ATINGIDO</span>
                        @endif
                    </div>
                </div>

                {{-- Percentual + ações --}}
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm font-bold tabular-nums" style="color:{{ $barCor }}">{{ $pct }}%</span>
                    <form action="{{ route('alerts.toggle', $alerta) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="p-2 rounded-lg transition-colors text-foco-muted hover:bg-foco-surface"
                                title="{{ $alerta->ativo ? 'Pausar' : 'Ativar' }}">
                            <i data-lucide="{{ $alerta->ativo ? 'bell' : 'bell-off' }}" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <form action="{{ route('alerts.destroy', $alerta) }}" method="POST"
                          onsubmit="return confirm('Excluir alerta de {{ addslashes($alerta->categoria->nome) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="p-2 rounded-lg transition-colors text-foco-muted hover:text-foco-saida hover:bg-red-50">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Barra de progresso --}}
            <div class="w-full h-2 rounded-full bg-foco-border overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     style="width:{{ $pct }}%; background:{{ $barCor }}"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

@extends('layouts.app')
@section('title', 'Relatórios')

@section('content')
@php
    $nomesMeses = [1=>'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                   'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $tituloMes  = $nomesMeses[$ref->month] . ' de ' . $ref->year;
    $resultado  = $entradas - $saidas;
@endphp

<div class="max-w-3xl mx-auto">

    {{-- Cabeçalho com navegação de mês --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <i data-lucide="bar-chart-3" class="w-6 h-6 text-foco-accent"></i>
            Relatório do mês
        </h1>
    </div>

    <div class="flex items-center justify-center gap-4 mb-6">
        <a href="{{ route('reports.index', ['mes' => $mesAnterior]) }}"
           class="p-2.5 rounded-xl card card-hover text-foco-muted hover:text-foco-accent transition-colors"
           title="Mês anterior">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <span class="text-lg font-bold text-foco-text min-w-48 text-center">{{ $tituloMes }}</span>
        @if(!$ehMesAtual)
        <a href="{{ route('reports.index', ['mes' => $mesSeguinte]) }}"
           class="p-2.5 rounded-xl card card-hover text-foco-muted hover:text-foco-accent transition-colors"
           title="Próximo mês">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </a>
        @else
        <span class="p-2.5 rounded-xl text-foco-border">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </span>
        @endif
    </div>

    {{-- Resumo do mês: 3 números, sem mais --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
        <div class="card p-5">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="arrow-down-circle" class="w-4 h-4" style="color:#16A34A"></i>
                <p class="text-xs text-foco-muted font-medium">Entrou</p>
            </div>
            <p class="text-2xl font-bold" style="color:#16A34A">
                +&nbsp;{{ number_format($entradas, 2, ',', '.') }}
            </p>
            @if($deltaEntradas !== null)
            {{-- entrar mais = bom (verde); entrar menos = atenção --}}
            <p class="text-xs mt-1 font-medium" style="color:{{ $deltaEntradas >= 0 ? '#16A34A' : '#D97706' }}">
                {{ $deltaEntradas >= 0 ? '▲' : '▼' }} {{ abs($deltaEntradas) }}% vs mês anterior
            </p>
            @endif
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="arrow-up-circle" class="w-4 h-4" style="color:#DC2626"></i>
                <p class="text-xs text-foco-muted font-medium">Saiu</p>
            </div>
            <p class="text-2xl font-bold" style="color:#DC2626">
                −&nbsp;{{ number_format($saidas, 2, ',', '.') }}
            </p>
            @if($deltaSaidas !== null)
            {{-- gastar menos = bom (verde); gastar mais = alerta (vermelho) --}}
            <p class="text-xs mt-1 font-medium" style="color:{{ $deltaSaidas <= 0 ? '#16A34A' : '#DC2626' }}">
                {{ $deltaSaidas <= 0 ? '▼' : '▲' }} {{ abs($deltaSaidas) }}% vs mês anterior
            </p>
            @endif
        </div>
        <div class="card p-5" style="border-top: 3px solid {{ $resultado >= 0 ? '#16A34A' : '#DC2626' }}">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="scale" class="w-4 h-4 text-foco-muted"></i>
                <p class="text-xs text-foco-muted font-medium">{{ $resultado >= 0 ? 'Sobrou' : 'Faltou' }}</p>
            </div>
            <p class="text-2xl font-bold" style="color:{{ $resultado >= 0 ? '#16A34A' : '#DC2626' }}">
                {{ $resultado < 0 ? '−' : '' }}R$&nbsp;{{ number_format(abs($resultado), 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Fixo × Dia a dia: quanto do mês foi obrigação e quanto foi escolha --}}
    @if($saidas > 0)
    @php
        $pctFixo   = round($gastoFixo   / $saidas * 100);
        $pctContas = round($gastoContas / $saidas * 100);
        $pctDia    = max(0, 100 - $pctFixo - $pctContas);
        $buckets = [
            ['label' => 'Contas fixas',      'valor' => $gastoFixo,    'pct' => $pctFixo,   'cor' => '#6366F1', 'icone' => 'repeat',      'hint' => 'aluguel, luz, internet...'],
            ['label' => 'Contas e parcelas', 'valor' => $gastoContas,  'pct' => $pctContas, 'cor' => '#D97706', 'icone' => 'receipt',     'hint' => 'avulsas e parcelamentos'],
            ['label' => 'Dia a dia',         'valor' => $gastoDiaADia, 'pct' => $pctDia,    'cor' => '#DC2626', 'icone' => 'shopping-cart','hint' => 'lançamentos do cotidiano'],
        ];
    @endphp
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="split" class="w-4 h-4 text-foco-accent"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Fixo × Dia a dia</h2>
    </div>
    <div class="card p-5 mb-8">
        {{-- Barra proporcional --}}
        <div class="w-full h-4 rounded-full overflow-hidden flex mb-4" style="background:#E4E4F0">
            @foreach($buckets as $b)
            @if($b['valor'] > 0)
            <div style="width:{{ $b['pct'] }}%; background:{{ $b['cor'] }}" title="{{ $b['label'] }}: {{ $b['pct'] }}%"></div>
            @endif
            @endforeach
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($buckets as $b)
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                     style="background:{{ $b['cor'] }}18">
                    <i data-lucide="{{ $b['icone'] }}" class="w-4 h-4" style="color:{{ $b['cor'] }}"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-foco-muted font-medium">{{ $b['label'] }} · {{ $b['pct'] }}%</p>
                    <p class="font-bold text-foco-text">R$&nbsp;{{ number_format($b['valor'], 2, ',', '.') }}</p>
                    <p class="text-[11px] text-foco-muted">{{ $b['hint'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @if($custoFixoBase['qtd'] > 0)
        <p class="text-xs text-foco-muted mt-4 pt-3 flex items-center gap-1.5" style="border-top:1px solid #E4E4F0">
            <i data-lucide="info" class="w-3.5 h-3.5"></i>
            Sua base fixa cadastrada é <strong class="text-foco-text">R$&nbsp;{{ number_format($custoFixoBase['total'], 2, ',', '.') }}/mês</strong>
            ({{ $custoFixoBase['qtd'] }} conta(s) fixa(s)).
        </p>
        @endif
    </div>
    @endif

    {{-- Para onde foi o dinheiro --}}
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="pie-chart" class="w-4 h-4 text-foco-saida"></i>
        <h2 class="text-sm font-semibold text-foco-text uppercase tracking-wide">Para onde foi o dinheiro</h2>
    </div>

    @if($porCategoria->isEmpty())
    <div class="card px-8 py-16 text-center">
        <div class="w-14 h-14 rounded-2xl bg-foco-surface mx-auto mb-4 flex items-center justify-center">
            <i data-lucide="inbox" class="w-7 h-7 text-foco-muted"></i>
        </div>
        <p class="font-semibold text-foco-text mb-1">Nenhuma saída neste mês</p>
        <p class="text-sm text-foco-muted">Quando você lançar gastos, eles aparecem aqui por categoria.</p>
    </div>
    @else
    <div class="card overflow-hidden divide-y divide-foco-border">
        @foreach($porCategoria as $linha)
        @php
            $cat  = $linha['categoria'];
            $cor  = $cat?->cor ?? '#64748B';
            $pct  = $saidas > 0 ? round($linha['total'] / $saidas * 100) : 0;
        @endphp
        <div class="px-5 py-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     style="background:{{ $cor }}18">
                    <i data-lucide="{{ $cat?->icone ?? 'tag' }}" class="w-4 h-4" style="color:{{ $cor }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-foco-text">{{ $cat?->nome ?? 'Sem categoria' }}</p>
                    <p class="text-xs text-foco-muted">{{ $linha['qtd'] }} lançamento(s)</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-foco-text">R$&nbsp;{{ number_format($linha['total'], 2, ',', '.') }}</p>
                    <p class="text-xs text-foco-muted">{{ $pct }}% do total</p>
                </div>
            </div>
            <div class="w-full h-2 rounded-full bg-foco-border overflow-hidden">
                <div class="h-full rounded-full" style="width:{{ $pct }}%; background:{{ $cor }}"></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

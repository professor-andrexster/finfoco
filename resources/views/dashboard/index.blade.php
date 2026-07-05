@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php use App\Helpers\DateHelper; @endphp

{{-- ONBOARDING: 3 passos até o sistema fazer sentido --}}
@if(!$temLancamento || !$temContaFixa)
@php
    $passos = [
        ['feito' => $temContaFixa,  'titulo' => 'Cadastre suas contas fixas',   'sub' => 'Aluguel, água, luz, internet...', 'rota' => route('bills.create'),        'icone' => 'repeat'],
        ['feito' => $temLancamento, 'titulo' => 'Lance seu primeiro gasto',     'sub' => 'Leva menos de 10 segundos',       'rota' => route('transactions.create'), 'icone' => 'plus-circle'],
        ['feito' => false,          'titulo' => 'Veja pra onde o dinheiro vai', 'sub' => 'Relatório fixo × dia a dia',      'rota' => route('reports.index'),       'icone' => 'bar-chart-3'],
    ];
@endphp
<div class="card p-5 mb-6" style="border-top:3px solid #6366F1">
    <p class="text-sm font-semibold text-foco-text mb-1 flex items-center gap-2">
        <i data-lucide="rocket" class="w-4 h-4 text-foco-accent"></i>
        Bem-vindo ao FinFoco! Comece por aqui:
    </p>
    <p class="text-xs text-foco-muted mb-4">Três passos e o sistema começa a trabalhar pra você.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach($passos as $i => $p)
        <a href="{{ $p['rota'] }}"
           class="flex items-start gap-3 p-3 rounded-xl border-2 transition-colors {{ $p['feito'] ? 'border-foco-entrada/40 bg-green-50/50' : 'border-foco-border hover:border-foco-accent/60' }}">
            <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 text-xs font-bold
                        {{ $p['feito'] ? 'text-white' : 'text-foco-accent' }}"
                 style="background:{{ $p['feito'] ? '#16A34A' : '#EEF2FF' }}">
                @if($p['feito'])
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                @else
                    {{ $i + 1 }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold {{ $p['feito'] ? 'text-foco-muted line-through' : 'text-foco-text' }}">{{ $p['titulo'] }}</p>
                <p class="text-xs text-foco-muted mt-0.5">{{ $p['sub'] }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- AVISOS --}}
@php
    $danger  = collect($avisos)->where('tipo','danger')->first();
    $warning = collect($avisos)->where('tipo','warning')->first();
    $hasAlert = $danger || $warning;
@endphp

@if($hasAlert)
<div class="space-y-2 mb-6">
    @foreach($avisos as $aviso)
    @if($aviso['tipo'] !== 'success')
    @php
        $borderColor = $aviso['tipo'] === 'danger' ? '#DC2626' : '#D97706';
        $bgColor     = $aviso['tipo'] === 'danger' ? '#FEF2F2' : '#FFFBEB';
        $textColor   = $aviso['tipo'] === 'danger' ? '#DC2626' : '#D97706';
    @endphp
    @if($aviso['link'])
    <a href="{{ $aviso['link'] }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-opacity hover:opacity-80"
       style="background:{{ $bgColor }}; color:{{ $textColor }}; border-left: 3px solid {{ $borderColor }}">
        <i data-lucide="{{ $aviso['icone'] }}" class="w-4 h-4 shrink-0"></i>
        <span class="flex-1">{{ $aviso['mensagem'] }}</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0 opacity-60"></i>
    </a>
    @else
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:{{ $bgColor }}; color:{{ $textColor }}; border-left: 3px solid {{ $borderColor }}">
        <i data-lucide="{{ $aviso['icone'] }}" class="w-4 h-4 shrink-0"></i>
        <span>{{ $aviso['mensagem'] }}</span>
    </div>
    @endif
    @endif
    @endforeach
</div>
@endif

{{-- TOGGLE DIA / SEMANA --}}
<div x-data="{ visao: localStorage.getItem('finfoco_visao') || 'dia' }"
     x-init="$watch('visao', v => localStorage.setItem('finfoco_visao', v))"
     class="mb-6">

    {{-- Selector --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-foco-text">Visão geral</h2>
        <div class="flex items-center gap-1 bg-foco-surface rounded-xl p-1" style="border:1px solid #E4E4F0">
            <button @click="visao='dia'"
                    :class="visao==='dia' ? 'bg-white text-foco-accent shadow-sm' : 'text-foco-muted'"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all">
                Hoje
            </button>
            <button @click="visao='semana'"
                    :class="visao==='semana' ? 'bg-white text-foco-accent shadow-sm' : 'text-foco-muted'"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all">
                Esta semana
            </button>
            <button @click="visao='mes'"
                    :class="visao==='mes' ? 'bg-white text-foco-accent shadow-sm' : 'text-foco-muted'"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-all">
                Este mês
            </button>
        </div>
    </div>

    {{-- HERO: SALDO + PODE GASTAR --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

        {{-- Saldo --}}
        <div class="card p-7">
            <p class="text-xs font-semibold uppercase tracking-widest text-foco-muted mb-3">Saldo atual</p>
            <p class="font-bold leading-none {{ $saldoTotal >= 0 ? 'text-foco-accent' : 'text-foco-saida' }}"
               style="font-size: 2.75rem; letter-spacing: -0.03em">
                {{ $saldoTotal < 0 ? '−' : '' }}R$&nbsp;{{ number_format(abs($saldoTotal), 2, ',', '.') }}
            </p>
            @if($saldoTotal >= 0)
            <p class="mt-3 text-xs text-foco-muted flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3 text-foco-entrada"></i>
                Positivo
            </p>
            @else
            <p class="mt-3 text-xs text-foco-saida flex items-center gap-1">
                <i data-lucide="trending-down" class="w-3 h-3"></i>
                Atenção: saldo negativo
            </p>
            @endif
        </div>

        {{-- Pode Gastar --}}
        @php
            // Cores fixas do semáforo: verde=ok, amarelo=atenção, vermelho=negativo
            $pgColor = match($semaforoPodeGastar) {
                'red'    => '#DC2626',
                'yellow' => '#D97706',
                default  => '#16A34A',
            };
        @endphp
        <div class="card p-7" style="border-top: 3px solid {{ $pgColor }}">
            {{-- Label muda com visão --}}
            <p class="text-xs font-semibold uppercase tracking-widest text-foco-muted mb-3">
                <span x-show="visao==='dia'">Pode gastar hoje</span>
                <span x-show="visao==='semana'" x-cloak>Pode gastar esta semana (por dia)</span>
                <span x-show="visao==='mes'" x-cloak>Pode gastar este mês (total)</span>
            </p>
            {{-- Valor muda com visão --}}
            <p class="font-bold leading-none" style="font-size: 2.75rem; letter-spacing: -0.03em; color:{{ $pgColor }}">
                <span x-show="visao==='dia'">
                    {{ $podeGastarHoje < 0 ? '−' : '' }}R$&nbsp;{{ number_format(abs($podeGastarHoje), 2, ',', '.') }}
                </span>
                <span x-show="visao==='semana'" x-cloak>
                    {{ $podeGastarSemana < 0 ? '−' : '' }}R$&nbsp;{{ number_format(abs($podeGastarSemana), 2, ',', '.') }}
                </span>
                <span x-show="visao==='mes'" x-cloak>
                    {{ $podeGastarMes < 0 ? '−' : '' }}R$&nbsp;{{ number_format(abs($podeGastarMes), 2, ',', '.') }}
                </span>
            </p>
            <p class="mt-3 text-xs text-foco-muted">
                <span x-show="visao==='dia'">saldo − contas pendentes ÷ dias restantes</span>
                <span x-show="visao==='semana'" x-cloak>saldo − contas pendentes ÷ dias restantes</span>
                <span x-show="visao==='mes'" x-cloak>saldo + a receber − contas a pagar do mês</span>
            </p>
        </div>
    </div>

    {{-- STATS --}}
    {{-- Dia --}}
    <div x-show="visao==='dia'" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $statsDia = [
                ['label'=>'Saídas hoje',   'icon'=>'sun',              'valor'=>$gastosHoje, 'cor'=>'#DC2626','sinal'=>'−'],
                ['label'=>'Saídas semana', 'icon'=>'calendar',         'valor'=>$gastosSemanais,'cor'=>'#DC2626','sinal'=>'−'],
                ['label'=>'Entrou no mês', 'icon'=>'arrow-down-circle','valor'=>$entradaMes,'cor'=>'#16A34A','sinal'=>'+'],
                ['label'=>'Saiu no mês',   'icon'=>'arrow-up-circle',  'valor'=>$saidaMes,  'cor'=>'#DC2626','sinal'=>'−'],
            ];
        @endphp
        @foreach($statsDia as $s)
        <div class="card p-4">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5" style="color:{{ $s['cor'] }}"></i>
                <p class="text-xs text-foco-muted font-medium">{{ $s['label'] }}</p>
            </div>
            <p class="text-xl font-bold" style="color:{{ $s['cor'] }}; letter-spacing:-0.02em">
                {{ $s['sinal'] }}&nbsp;{{ number_format($s['valor'], 2, ',', '.') }}
            </p>
        </div>
        @endforeach
    </div>

    {{-- Semana --}}
    <div x-show="visao==='semana'" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $statsSemana = [
                ['label'=>'Saídas semana',  'icon'=>'trending-down',    'valor'=>$gastosSemanais,'cor'=>'#DC2626','sinal'=>'−'],
                ['label'=>'Entradas semana','icon'=>'trending-up',      'valor'=>$entradasSemana,'cor'=>'#16A34A','sinal'=>'+'],
                ['label'=>'Entrou no mês',  'icon'=>'arrow-down-circle','valor'=>$entradaMes,    'cor'=>'#16A34A','sinal'=>'+'],
                ['label'=>'Saiu no mês',    'icon'=>'arrow-up-circle',  'valor'=>$saidaMes,      'cor'=>'#DC2626','sinal'=>'−'],
            ];
        @endphp
        @foreach($statsSemana as $s)
        <div class="card p-4">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5" style="color:{{ $s['cor'] }}"></i>
                <p class="text-xs text-foco-muted font-medium">{{ $s['label'] }}</p>
            </div>
            <p class="text-xl font-bold" style="color:{{ $s['cor'] }}; letter-spacing:-0.02em">
                {{ $s['sinal'] }}&nbsp;{{ number_format($s['valor'], 2, ',', '.') }}
            </p>
        </div>
        @endforeach
    </div>

    {{-- Mês --}}
    <div x-show="visao==='mes'" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $statsMes = [
                ['label'=>'Entrou no mês',       'icon'=>'arrow-down-circle','valor'=>$entradaMes,               'cor'=>'#16A34A','sinal'=>'+'],
                ['label'=>'Saiu no mês',         'icon'=>'arrow-up-circle',  'valor'=>$saidaMes,                 'cor'=>'#DC2626','sinal'=>'−'],
                ['label'=>'Gastos recorrentes',  'icon'=>'repeat',           'valor'=>$gastosRecorrentes['total'],'cor'=>'#DC2626','sinal'=>'−'],
                ['label'=>'Contas pendentes',    'icon'=>'receipt',          'valor'=>$contasPendentesMes,       'cor'=>'#D97706','sinal'=>'−'],
            ];
        @endphp
        @foreach($statsMes as $s)
        <div class="card p-4">
            <div class="flex items-center gap-1.5 mb-2">
                <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5" style="color:{{ $s['cor'] }}"></i>
                <p class="text-xs text-foco-muted font-medium">{{ $s['label'] }}</p>
            </div>
            <p class="text-xl font-bold" style="color:{{ $s['cor'] }}; letter-spacing:-0.02em">
                {{ $s['sinal'] }}&nbsp;{{ number_format($s['valor'], 2, ',', '.') }}
            </p>
            @if($s['label'] === 'Gastos recorrentes' && $gastosRecorrentes['qtd'] > 0)
            <p class="text-[11px] text-foco-muted mt-1">{{ $gastosRecorrentes['qtd'] }} {{ $gastosRecorrentes['qtd'] == 1 ? 'conta fixa' : 'contas fixas' }}</p>
            @endif
        </div>
        @endforeach
    </div>

</div>{{-- /x-data visao --}}

{{-- META DO DIA A DIA --}}
@if($metaDiaADia > 0)
@php
    $pctMeta  = min(100, round($gastoDiaADia / $metaDiaADia * 100));
    $corMeta  = $pctMeta >= 100 ? '#DC2626' : ($pctMeta >= 80 ? '#D97706' : '#16A34A');
    $sobraMeta = max(0, $metaDiaADia - $gastoDiaADia);
@endphp
<div class="card p-5 mb-6">
    <div class="flex items-center justify-between mb-2 flex-wrap gap-1">
        <p class="text-sm font-semibold text-foco-text flex items-center gap-2">
            <i data-lucide="target" class="w-4 h-4" style="color:{{ $corMeta }}"></i>
            Meta do dia a dia
        </p>
        <p class="text-sm">
            <strong style="color:{{ $corMeta }}">R$&nbsp;{{ number_format($gastoDiaADia, 2, ',', '.') }}</strong>
            <span class="text-foco-muted">de R$&nbsp;{{ number_format($metaDiaADia, 2, ',', '.') }} · {{ $pctMeta }}%</span>
        </p>
    </div>
    <div class="w-full h-3 rounded-full bg-foco-border overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500"
             style="width:{{ $pctMeta }}%; background:{{ $corMeta }}"></div>
    </div>
    <p class="text-xs text-foco-muted mt-2">
        @if($pctMeta >= 100)
            Meta do mês estourada — cada gasto agora é escolha consciente.
        @else
            Ainda cabem R$&nbsp;{{ number_format($sobraMeta, 2, ',', '.') }} no dia a dia este mês.
        @endif
    </p>
</div>
@endif

{{-- CTA --}}
<div class="flex justify-center mb-8">
    <a href="{{ route('transactions.create') }}"
       class="btn-primary inline-flex items-center gap-2.5 text-white px-7 py-3.5 rounded-2xl transition-all"
       style="background:#6366F1; box-shadow: 0 4px 14px rgba(99,102,241,.35);"
       onmouseover="this.style.background='#4F46E5'"
       onmouseout="this.style.background='#6366F1'">
        <i data-lucide="plus" class="w-5 h-5"></i>
        Novo lançamento
    </a>
</div>

{{-- GRID INFERIOR --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- LEMBRETES --}}
    <div class="card overflow-hidden" x-data="{ novoLembrete: false }">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #E4E4F0">
            <h2 class="text-sm font-semibold flex items-center gap-2 text-foco-text">
                <i data-lucide="bookmark" class="w-4 h-4 text-foco-accent"></i>
                Lembretes
            </h2>
            <button @click="novoLembrete = !novoLembrete"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                    style="color:#6366F1; background:#EEF2FF"
                    onmouseover="this.style.background='#E0E7FF'"
                    onmouseout="this.style.background='#EEF2FF'">
                + Adicionar
            </button>
        </div>

        <div x-show="novoLembrete" x-cloak style="display:none; border-bottom:1px solid #E4E4F0">
            <form action="{{ route('reminders.store') }}" method="POST" class="px-5 py-4 bg-foco-surface">
                @csrf
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="titulo" maxlength="60" placeholder="O que lembrar?"
                           class="flex-1 border border-foco-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-foco-accent/30" required>
                    <input type="date" name="data_lembrete" value="{{ date('Y-m-d') }}"
                           class="border border-foco-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-foco-accent/30" required>
                    <button type="submit"
                            class="text-white text-sm font-semibold px-4 py-2 rounded-xl"
                            style="background:#6366F1">
                        Salvar lembrete
                    </button>
                </div>
            </form>
        </div>

        @if($lembretes->isEmpty())
        <div class="px-5 py-10 text-center">
            <div class="w-10 h-10 rounded-full bg-foco-surface mx-auto mb-3 flex items-center justify-center">
                <i data-lucide="check" class="w-5 h-5 text-foco-accent"></i>
            </div>
            <p class="text-sm text-foco-muted">Nenhum lembrete ativo.</p>
        </div>
        @else
        <ul>
            @foreach($lembretes as $lembrete)
            <li class="flex items-center gap-3 px-5 py-3 {{ $lembrete->concluido ? 'opacity-40' : '' }}"
                style="border-bottom:1px solid #F3F3FB">
                <form action="{{ route('reminders.toggle', $lembrete) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-5 h-5 rounded-md border-2 flex items-center justify-center shrink-0 transition-colors"
                            style="{{ $lembrete->concluido ? 'background:#16A34A; border-color:#16A34A' : 'border-color:#E4E4F0' }}">
                        @if($lembrete->concluido)
                            <i data-lucide="check" class="w-3 h-3 text-white"></i>
                        @endif
                    </button>
                </form>
                @php $vencido = !$lembrete->concluido && $lembrete->data_lembrete->lt(today()); @endphp
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate {{ $lembrete->concluido ? 'line-through text-foco-muted' : 'text-foco-text' }}">
                        {{ $lembrete->titulo }}
                    </p>
                    <p class="text-xs mt-0.5 {{ $vencido ? 'font-semibold' : 'text-foco-muted' }}"
                       @if($vencido) style="color:#DC2626" @endif>
                        @if($vencido)<i data-lucide="alert-circle" class="w-3 h-3 inline-block" style="vertical-align:-1px"></i>@endif
                        {{ DateHelper::formatarDataRelativa($lembrete->data_lembrete) }}
                    </p>
                </div>
                <form action="{{ route('reminders.destroy', $lembrete) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1 text-foco-muted hover:text-foco-saida transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- ÚLTIMAS TRANSAÇÕES --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #E4E4F0">
            <h2 class="text-sm font-semibold flex items-center gap-2 text-foco-text">
                <i data-lucide="layers" class="w-4 h-4 text-foco-muted"></i>
                Últimos lançamentos
            </h2>
            <a href="{{ route('history.index') }}"
               class="text-xs font-semibold text-foco-accent hover:text-foco-accent/70 flex items-center gap-1 transition-opacity">
                Ver todos <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        @if($ultimasTransacoes->isEmpty())
        <div class="px-5 py-10 text-center">
            <div class="w-10 h-10 rounded-full bg-foco-surface mx-auto mb-3 flex items-center justify-center">
                <i data-lucide="inbox" class="w-5 h-5 text-foco-muted"></i>
            </div>
            <p class="text-sm text-foco-muted mb-2">Nenhum lançamento ainda.</p>
            <a href="{{ route('transactions.create') }}" class="text-sm text-foco-accent font-medium hover:underline">
                Criar o primeiro
            </a>
        </div>
        @else
        <ul>
            @foreach($ultimasTransacoes as $t)
            <li class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-foco-surface"
                style="border-bottom:1px solid #F3F3FB">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                         style="background:{{ ($t->categoria?->cor ?? '#6366F1') }}18">
                        <i data-lucide="{{ $t->categoria?->icone ?? 'tag' }}" class="w-4 h-4"
                           style="color:{{ $t->categoria?->cor ?? '#6366F1' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate text-foco-text">{{ $t->descricao }}</p>
                        <p class="text-xs text-foco-muted mt-0.5">
                            {{ $t->categoria?->nome ?? '—' }} · {{ DateHelper::formatarDataRelativa($t->data) }}
                        </p>
                    </div>
                </div>
                <span class="font-semibold text-sm shrink-0 ml-3"
                      style="color:{{ $t->tipo === 'entrada' ? '#16A34A' : '#DC2626' }}">
                    {{ $t->tipo === 'entrada' ? '+' : '−' }}&nbsp;{{ number_format($t->valor, 2, ',', '.') }}
                </span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

</div>
@endsection

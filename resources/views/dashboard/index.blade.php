@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php use App\Helpers\DateHelper; @endphp

{{-- AVISOS INTELIGENTES --}}
<div class="space-y-2 mb-6">
    @foreach($avisos as $aviso)
    @php
        $cores = match($aviso['tipo']) {
            'danger'  => 'border-foco-saida/40 bg-foco-saida/10 text-foco-saida',
            'warning' => 'border-foco-alerta/40 bg-foco-alerta/10 text-foco-alerta',
            default   => 'border-foco-entrada/40 bg-foco-entrada/10 text-foco-entrada',
        };
    @endphp
    @if($aviso['link'])
    <a href="{{ $aviso['link'] }}"
       class="flex items-center gap-3 border rounded-xl px-4 py-3 transition-opacity hover:opacity-80 {{ $cores }}">
        <i data-lucide="{{ $aviso['icone'] }}" class="w-5 h-5 shrink-0"></i>
        <span class="text-sm font-semibold flex-1">{{ $aviso['mensagem'] }}</span>
        <i data-lucide="chevron-right" class="w-4 h-4 shrink-0 opacity-60"></i>
    </a>
    @else
    <div class="flex items-center gap-3 border rounded-xl px-4 py-3 {{ $cores }}">
        <i data-lucide="{{ $aviso['icone'] }}" class="w-5 h-5 shrink-0"></i>
        <span class="text-sm font-semibold">{{ $aviso['mensagem'] }} 🎯</span>
    </div>
    @endif
    @endforeach
</div>

{{-- SALDO + PODE GASTAR --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-foco-surface border border-foco-border rounded-2xl p-6 text-center">
        <p class="text-foco-muted text-sm mb-1">Saldo atual</p>
        <p class="text-5xl font-bold {{ $saldoTotal >= 0 ? 'text-foco-entrada' : 'text-foco-saida' }}">
            {{ $saldoTotal < 0 ? '-' : '' }}R$ {{ number_format(abs($saldoTotal), 2, ',', '.') }}
        </p>
    </div>
    @php
        $semaforoClasses = match($semaforoPodeGastar) {
            'red'    => 'text-foco-saida',
            'yellow' => 'text-foco-alerta',
            default  => 'text-foco-entrada',
        };
        $semaforoEmoji = match($semaforoPodeGastar) { 'red'=>'🔴','yellow'=>'🟡',default=>'🟢' };
    @endphp
    <div class="bg-foco-surface border border-foco-border rounded-2xl p-6 text-center">
        <p class="text-foco-muted text-sm mb-1">{{ $semaforoEmoji }} Pode gastar hoje</p>
        <p class="text-5xl font-bold {{ $semaforoClasses }}">
            {{ $podeGastarHoje < 0 ? '-' : '' }}R$ {{ number_format(abs($podeGastarHoje), 2, ',', '.') }}
        </p>
        <p class="text-foco-muted text-xs mt-1">saldo − contas do mês ÷ dias restantes</p>
    </div>
</div>

{{-- CARDS RESUMO --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
    <div class="bg-foco-surface border border-foco-border rounded-xl p-4">
        <p class="text-foco-muted text-xs mb-1 flex items-center gap-1"><i data-lucide="sun" class="w-3 h-3"></i> Gastos hoje</p>
        <p class="text-2xl font-bold text-foco-saida">R$ {{ number_format($gastosHoje, 2, ',', '.') }}</p>
    </div>
    <div class="bg-foco-surface border border-foco-border rounded-xl p-4">
        <p class="text-foco-muted text-xs mb-1 flex items-center gap-1"><i data-lucide="calendar-days" class="w-3 h-3"></i> Gastos semana</p>
        <p class="text-2xl font-bold text-foco-saida">R$ {{ number_format($gastosSemanais, 2, ',', '.') }}</p>
    </div>
    <div class="bg-foco-surface border border-foco-border rounded-xl p-4">
        <p class="text-foco-muted text-xs mb-1 flex items-center gap-1"><i data-lucide="trending-up" class="w-3 h-3"></i> Entradas mês</p>
        <p class="text-2xl font-bold text-foco-entrada">R$ {{ number_format($entradaMes, 2, ',', '.') }}</p>
    </div>
    <div class="bg-foco-surface border border-foco-border rounded-xl p-4">
        <p class="text-foco-muted text-xs mb-1 flex items-center gap-1"><i data-lucide="trending-down" class="w-3 h-3"></i> Saídas mês</p>
        <p class="text-2xl font-bold text-foco-saida">R$ {{ number_format($saidaMes, 2, ',', '.') }}</p>
    </div>
</div>

{{-- AÇÃO PRINCIPAL --}}
<div class="flex justify-center mb-8">
    <a href="{{ route('transactions.create') }}"
       class="btn-primary bg-foco-accent hover:bg-foco-accent/80 text-white px-8 py-4 rounded-2xl flex items-center gap-3 transition-colors shadow-lg">
        <i data-lucide="plus-circle" class="w-7 h-7"></i>
        Novo Lançamento
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- LEMBRETES --}}
    <div class="bg-foco-surface border border-foco-border rounded-2xl overflow-hidden"
         x-data="{ novoLembrete: false }">
        <div class="flex items-center justify-between px-5 py-4 border-b border-foco-border">
            <h2 class="font-semibold flex items-center gap-2">
                <i data-lucide="bookmark" class="w-4 h-4 text-foco-accent"></i>
                Lembretes
            </h2>
            <button @click="novoLembrete = !novoLembrete"
                    class="text-foco-accent text-sm flex items-center gap-1 font-medium hover:opacity-80 transition-opacity">
                <i data-lucide="plus" class="w-4 h-4"></i> Adicionar
            </button>
        </div>

        <div x-show="novoLembrete" x-cloak style="display:none">
            <form action="{{ route('reminders.store') }}" method="POST"
                  class="px-5 py-4 border-b border-foco-border bg-foco-bg/40">
                @csrf
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="titulo" maxlength="60" placeholder="O que lembrar?"
                           class="flex-1 bg-foco-surface border border-foco-border rounded-xl px-3 py-2 text-sm text-foco-text focus:outline-none focus:border-foco-accent" required>
                    <input type="date" name="data_lembrete" value="{{ date('Y-m-d') }}"
                           class="bg-foco-surface border border-foco-border rounded-xl px-3 py-2 text-sm text-foco-text focus:outline-none focus:border-foco-accent" required>
                    <button type="submit"
                            class="bg-foco-accent text-white px-3 py-2 rounded-xl text-sm font-semibold hover:bg-foco-accent/80 transition-colors">
                        Salvar lembrete
                    </button>
                </div>
            </form>
        </div>

        @if($lembretes->isEmpty())
        <div class="px-5 py-8 text-center text-foco-muted">
            <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
            <p class="text-sm">Nenhum lembrete ativo — mente livre! 😄</p>
        </div>
        @else
        <ul class="divide-y divide-foco-border">
            @foreach($lembretes as $lembrete)
            <li class="flex items-center gap-3 px-5 py-3 {{ $lembrete->concluido ? 'opacity-50' : '' }}">
                <form action="{{ route('reminders.toggle', $lembrete) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-colors
                                   {{ $lembrete->concluido ? 'bg-foco-entrada border-foco-entrada' : 'border-foco-muted hover:border-foco-accent' }}">
                        @if($lembrete->concluido)
                            <i data-lucide="check" class="w-3 h-3 text-white"></i>
                        @endif
                    </button>
                </form>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate {{ $lembrete->concluido ? 'line-through text-foco-muted' : '' }}">
                        {{ $lembrete->titulo }}
                    </p>
                    <p class="text-xs text-foco-muted">
                        {{ \App\Helpers\DateHelper::formatarDataRelativa($lembrete->data_lembrete) }}
                        · {{ $lembrete->data_lembrete->format('d/m') }}
                    </p>
                </div>
                <form action="{{ route('reminders.destroy', $lembrete) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- ÚLTIMAS TRANSAÇÕES --}}
    <div class="bg-foco-surface border border-foco-border rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-foco-border">
            <h2 class="font-semibold flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-foco-muted"></i>
                Últimos lançamentos
            </h2>
            <a href="{{ route('history.index') }}" class="text-foco-accent text-sm hover:underline flex items-center gap-1">
                Ver todos <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        @if($ultimasTransacoes->isEmpty())
        <div class="px-5 py-8 text-center text-foco-muted">
            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
            <p class="text-sm">Nenhum lançamento ainda.</p>
            <a href="{{ route('transactions.create') }}" class="mt-2 inline-block text-foco-accent text-sm hover:underline">
                Criar o primeiro agora
            </a>
        </div>
        @else
        <ul class="divide-y divide-foco-border">
            @foreach($ultimasTransacoes as $t)
            <li class="flex items-center justify-between px-5 py-3 hover:bg-foco-border/20 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                         style="background-color:{{ $t->categoria?->cor ?? '#64748B' }}22">
                        <i data-lucide="{{ $t->categoria?->icone ?? 'tag' }}" class="w-4 h-4"
                           style="color:{{ $t->categoria?->cor ?? '#64748B' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ $t->descricao }}</p>
                        <p class="text-foco-muted text-xs">
                            {{ $t->categoria?->nome ?? '—' }}
                            · {{ \App\Helpers\DateHelper::formatarDataRelativa($t->data) }}
                        </p>
                    </div>
                </div>
                <span class="font-bold text-sm shrink-0 ml-2 {{ $t->tipo === 'entrada' ? 'text-foco-entrada' : 'text-foco-saida' }}">
                    {{ $t->tipo === 'entrada' ? '+' : '-' }}R$ {{ number_format($t->valor, 2, ',', '.') }}
                </span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

</div>
@endsection

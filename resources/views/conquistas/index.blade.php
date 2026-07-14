@extends('layouts.app')

@section('title', 'Conquistas')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold">Conquistas</h1>
        <p class="text-sm text-foco-muted mt-1">O que você já construiu, um dia de cada vez.</p>
    </div>

    {{-- Números que importam --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach([
            ['icone' => 'flame',      'valor' => $sequenciaAtual,  'sufixo' => $sequenciaAtual === 1 ? 'dia' : 'dias',  'label' => 'Sequência atual'],
            ['icone' => 'trophy',     'valor' => $melhorSequencia, 'sufixo' => $melhorSequencia === 1 ? 'dia' : 'dias', 'label' => 'Melhor sequência'],
            ['icone' => 'check-check','valor' => $totalConcluidos, 'sufixo' => '',                                      'label' => 'Itens concluídos'],
            ['icone' => 'zap',        'valor' => $sessoesFoco,     'sufixo' => $minutosFoco >= 60 ? '· ' . intdiv($minutosFoco, 60) . 'h' . ($minutosFoco % 60 ? str_pad($minutosFoco % 60, 2, '0', STR_PAD_LEFT) : '') : ($minutosFoco > 0 ? '· ' . $minutosFoco . 'min' : ''), 'label' => 'Sessões de foco'],
        ] as $s)
        <div class="card p-5">
            <div class="flex items-center gap-1.5 mb-2 text-foco-muted">
                <i data-lucide="{{ $s['icone'] }}" class="w-4 h-4 text-foco-accent"></i>
                <p class="text-xs font-medium">{{ $s['label'] }}</p>
            </div>
            <p class="text-2xl font-bold" style="letter-spacing:-0.02em">
                {{ $s['valor'] }} <span class="text-sm font-semibold text-foco-muted">{{ $s['sufixo'] }}</span>
            </p>
        </div>
        @endforeach
    </div>

    {{-- Mapa de constância --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h2 class="text-sm font-bold flex items-center gap-2">
                <i data-lucide="calendar-days" class="w-4 h-4 text-foco-accent"></i>
                Constância — últimas 20 semanas
            </h2>
            <p class="text-xs text-foco-muted">{{ $diasAtivos }} {{ $diasAtivos === 1 ? 'dia ativo' : 'dias ativos' }}</p>
        </div>

        <div class="overflow-x-auto pb-1">
            <div class="flex gap-1 min-w-max">
                @foreach($semanas as $dias)
                <div class="flex flex-col gap-1">
                    @for($dow = 1; $dow <= 7; $dow++)
                        @php $cel = $dias[$dow] ?? null; @endphp
                        @if($cel === null)
                            <span class="w-3.5 h-3.5 rounded-[4px]" style="background:transparent"></span>
                        @else
                            @php
                                $n = $cel['n'];
                                $alpha = $n === 0 ? null : ($n === 1 ? '.3' : ($n <= 3 ? '.55' : ($n <= 5 ? '.8' : '1')));
                            @endphp
                            <span class="w-3.5 h-3.5 rounded-[4px]"
                                  title="{{ \Carbon\Carbon::parse($cel['data'])->format('d/m') }} — {{ $n }} {{ $n === 1 ? 'conclusão' : 'conclusões' }}"
                                  style="{{ $alpha ? "background:rgba(99,102,241,{$alpha})" : 'background:var(--c-surface); box-shadow: inset 0 0 0 1px var(--c-border)' }}"></span>
                        @endif
                    @endfor
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-1.5 mt-3 text-xs text-foco-muted">
            Menos
            <span class="w-3 h-3 rounded-[3px]" style="background:var(--c-surface); box-shadow: inset 0 0 0 1px var(--c-border)"></span>
            <span class="w-3 h-3 rounded-[3px]" style="background:rgba(99,102,241,.3)"></span>
            <span class="w-3 h-3 rounded-[3px]" style="background:rgba(99,102,241,.55)"></span>
            <span class="w-3 h-3 rounded-[3px]" style="background:rgba(99,102,241,.8)"></span>
            <span class="w-3 h-3 rounded-[3px]" style="background:rgba(99,102,241,1)"></span>
            Mais
        </div>
    </div>

    {{-- Marcos --}}
    <div>
        <h2 class="text-sm font-bold text-foco-muted uppercase tracking-wide px-1 mb-3">Marcos</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($marcos as $m)
            @php $atingido = $m['atual'] >= $m['alvo']; @endphp
            <div class="card p-4 flex items-center gap-3 {{ $atingido ? '' : 'opacity-80' }}">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                      style="{{ $atingido ? 'background:rgba(99,102,241,.14)' : 'background:var(--c-surface)' }}">
                    <i data-lucide="{{ $m['icone'] }}" class="w-5 h-5 {{ $atingido ? 'text-foco-accent' : 'text-foco-muted' }}"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">{{ $m['nome'] }}</p>
                    <p class="text-xs text-foco-muted">{{ $m['desc'] }}</p>
                    @unless($atingido)
                    <div class="h-1 rounded-full mt-2 overflow-hidden" style="background:var(--c-surface)">
                        <div class="h-full rounded-full bg-foco-accent" style="width:{{ round($m['atual'] / $m['alvo'] * 100) }}%"></div>
                    </div>
                    @endunless
                </div>
                @if($atingido)
                <i data-lucide="check" class="w-4 h-4 text-foco-entrada shrink-0"></i>
                @else
                <span class="text-xs font-semibold text-foco-muted shrink-0">{{ $m['atual'] }}/{{ $m['alvo'] }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <p class="text-xs text-foco-muted text-center">
        Constância vale mais que perfeição. Um quadrado por vez.
    </p>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Agenda da semana')

@section('content')
@php
    $semanaAnterior = $inicio->copy()->subWeek()->toDateString();
    $proximaSemana  = $inicio->copy()->addWeek()->toDateString();
    $ehSemanaAtual  = today()->between($inicio, $fim);
@endphp

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Navegação da semana --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('agenda.semana', ['data' => $semanaAnterior]) }}"
           class="w-11 h-11 card card-hover flex items-center justify-center text-foco-muted hover:text-foco-text">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold">{{ $ehSemanaAtual ? 'Esta semana' : 'Semana' }}</h1>
            <p class="text-sm text-foco-muted">
                {{ $inicio->translatedFormat('d/m') }} – {{ $fim->translatedFormat('d/m') }}
            </p>
            <div class="flex items-center justify-center gap-3 mt-1">
                <a href="{{ route('agenda.index') }}" class="text-xs font-semibold text-foco-accent hover:underline">Ver dia</a>
                @unless($ehSemanaAtual)
                    <a href="{{ route('agenda.semana') }}" class="text-xs font-semibold text-foco-accent hover:underline">Semana atual</a>
                @endunless
            </div>
        </div>
        <a href="{{ route('agenda.semana', ['data' => $proximaSemana]) }}"
           class="w-11 h-11 card card-hover flex items-center justify-center text-foco-muted hover:text-foco-text">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </a>
    </div>

    {{-- 7 dias --}}
    <div class="grid gap-3 lg:grid-cols-7 sm:grid-cols-2">
        @for($i = 0; $i < 7; $i++)
        @php
            $dia       = $inicio->copy()->addDays($i);
            $ehHoje    = $dia->isToday();
            $doDia     = $compromissos->get($dia->toDateString(), collect());
            $rotinasDia = $rotinas->filter(fn($r) => $r->agendadaEm($dia));
            $rotinasFeitas = $rotinasDia->filter(fn($r) => $r->feitaEm($dia))->count();
        @endphp
        <a href="{{ route('agenda.index', ['data' => $dia->toDateString()]) }}"
           class="card card-hover p-3 flex flex-col gap-2 min-h-[120px] {{ $ehHoje ? 'ring-2 ring-foco-accent' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase {{ $ehHoje ? 'text-foco-accent' : 'text-foco-muted' }}">
                    {{ $dia->translatedFormat('D') }}
                </span>
                <span class="text-sm font-bold {{ $ehHoje ? 'text-foco-accent' : '' }}">{{ $dia->format('d') }}</span>
            </div>

            @if($ehHoje)
                <span class="text-[10px] font-bold text-white bg-foco-accent px-1.5 py-0.5 rounded-full self-start -mt-1">HOJE</span>
            @endif

            @php $googleDia = $eventosGoogleSemana[$dia->toDateString()] ?? []; @endphp
            <div class="space-y-1.5 flex-1">
                @forelse($doDia as $c)
                <p class="text-xs leading-snug {{ $c->concluido ? 'line-through text-foco-muted' : '' }}">
                    @if($c->hora)<span class="font-bold text-foco-accent">{{ substr($c->hora, 0, 5) }}</span>@endif
                    {{ $c->titulo }}
                </p>
                @empty
                    @if(empty($googleDia))
                    <p class="text-xs text-foco-muted italic">Livre</p>
                    @endif
                @endforelse

                @foreach($googleDia as $g)
                <p class="text-xs leading-snug text-foco-muted">
                    @if($g['hora'])<span class="font-bold">{{ $g['hora'] }}</span>@endif
                    {{ $g['titulo'] }} <span class="text-[9px] font-bold">· G</span>
                </p>
                @endforeach
            </div>

            @if($rotinasDia->isNotEmpty())
            <span class="text-[10px] font-semibold {{ $rotinasFeitas === $rotinasDia->count() ? 'text-foco-entrada' : 'text-foco-muted' }} flex items-center gap-1">
                <i data-lucide="repeat" class="w-3 h-3"></i>
                {{ $rotinasFeitas }}/{{ $rotinasDia->count() }} rotinas
            </span>
            @endif
        </a>
        @endfor
    </div>

    <p class="text-xs text-foco-muted text-center">Toque num dia para abrir, marcar e adicionar compromissos.</p>
</div>
@endsection

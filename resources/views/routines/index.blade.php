@extends('layouts.app')

@section('title', 'Rotinas')

@section('content')
@php
    $diasLabel = ['S', 'T', 'Q', 'Q', 'S', 'S', 'D']; // seg..dom
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <i data-lucide="repeat" class="w-6 h-6 text-foco-accent"></i> Rotinas
            </h1>
            <p class="text-sm text-foco-muted mt-1">Hábitos que se repetem. Cada dia cumprido soma na sequência.</p>
        </div>
        <a href="{{ route('agenda.index') }}" class="text-sm font-semibold text-foco-accent hover:underline shrink-0">
            Voltar para agenda
        </a>
    </div>

    {{-- Criar rotina: máximo 3 campos (o quê, hora opcional, dias) --}}
    <form action="{{ route('routines.store') }}" method="POST" class="card p-5 space-y-3">
        @csrf
        <input type="text" name="titulo" maxlength="80" required
               placeholder="Qual hábito você quer manter? Ex.: Tomar o remédio"
               value="{{ old('titulo') }}"
               class="w-full rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">

        <div class="flex flex-wrap items-center gap-2">
            <input type="time" name="hora" value="{{ old('hora') }}"
                   class="w-32 rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">
            <span class="text-xs text-foco-muted">Hora é opcional.</span>
        </div>

        <div>
            <p class="text-xs font-semibold text-foco-muted mb-2">Em quais dias?</p>
            <div class="flex gap-1.5">
                @foreach($diasLabel as $i => $letra)
                @php $dia = $i + 1; @endphp
                <label x-data="{ on: {{ in_array($dia, old('dias', [1,2,3,4,5,6,7])) ? 'true' : 'false' }} }"
                       class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer border-2 transition-colors select-none"
                       :class="on ? 'bg-foco-accent border-foco-accent text-white' : 'border-foco-border text-foco-muted hover:border-foco-accent'">
                    <input type="checkbox" name="dias[]" value="{{ $dia }}" x-model="on" class="sr-only">
                    {{ $letra }}
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit"
                class="btn-primary w-full bg-foco-accent text-white rounded-xl py-3.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Criar rotina
        </button>
    </form>

    {{-- Lista de rotinas --}}
    @if($rotinas->isEmpty())
        <div class="card p-10 text-center space-y-3">
            <i data-lucide="repeat" class="w-10 h-10 mx-auto text-foco-muted"></i>
            <p class="font-semibold">Nenhuma rotina ainda.</p>
            <p class="text-sm text-foco-muted">Comece com uma só — a mais importante. Ex.: "Tomar o remédio".</p>
        </div>
    @else
        <ul class="space-y-3">
            @foreach($rotinas as $r)
            @php $feita = $r->feitaEm(today()); $sequencia = $r->streak(); @endphp
            <li class="card card-hover p-4 flex items-center gap-4">

                {{-- Check de hoje (só se a rotina vale hoje) --}}
                @if($r->agendadaEm(today()))
                <form action="{{ route('routines.check', $r) }}" method="POST" x-data="{ ok: {{ $feita ? 'true' : 'false' }} }">
                    @csrf
                    <input type="hidden" name="voltar" value="rotinas">
                    <button type="submit" @click="ok = !ok"
                            class="w-11 h-11 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                            :class="ok ? 'bg-foco-entrada border-foco-entrada text-white' : 'border-foco-border text-transparent hover:border-foco-entrada'"
                            title="{{ $feita ? 'Desmarcar hoje' : 'Concluir hoje' }}">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </button>
                </form>
                @else
                <span class="w-11 h-11 rounded-full border-2 border-dashed border-foco-border shrink-0" title="Não vale hoje"></span>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-base">{{ $r->titulo }}</p>
                    <p class="text-sm text-foco-muted flex items-center gap-2 flex-wrap">
                        <span>{{ $r->hora ? substr($r->hora, 0, 5) : 'Qualquer hora' }}</span>
                        <span class="flex gap-0.5">
                            @foreach($diasLabel as $i => $letra)
                            <span class="w-5 h-5 rounded-full text-[10px] font-bold flex items-center justify-center
                                         {{ substr($r->dias, $i, 1) === '1' ? 'bg-foco-accent text-white' : 'bg-foco-surface text-foco-muted' }}">
                                {{ $letra }}
                            </span>
                            @endforeach
                        </span>
                    </p>
                </div>

                @if($sequencia > 0)
                <span class="flex items-center gap-1 text-sm font-bold text-foco-accent shrink-0"
                      title="{{ $sequencia }} dia{{ $sequencia > 1 ? 's' : '' }} seguidos">
                    <i data-lucide="flame" class="w-4 h-4"></i> {{ $sequencia }}
                </span>
                @endif

                <form action="{{ route('routines.destroy', $r) }}" method="POST"
                      onsubmit="return confirm('Remover esta rotina? A sequência será perdida.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-2" title="Excluir rotina">
                        <i data-lucide="trash-2" class="w-4.5 h-4.5" style="width:18px;height:18px"></i>
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection

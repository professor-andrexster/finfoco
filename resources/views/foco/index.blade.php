@extends('layouts.app')

@section('title', 'Modo Hiperfoco')

@section('content')
@php
    $sugestoesJson = $sugestoes->map(fn($c) => [
        'id'     => $c->id,
        'titulo' => $c->titulo,
        'passos' => $c->steps->pluck('titulo')->values(),
    ])->values();
@endphp

<div class="max-w-xl mx-auto"
     x-data="{
        etapa: 'escolha',           // escolha | foco | fim
        tarefa: '',
        compromissoId: null,
        minutos: 25,
        restante: 0,
        totalSeg: 0,
        timer: null,

        comecar() {
            if (!this.tarefa.trim()) return;
            this.totalSeg  = this.minutos * 60;
            this.restante  = this.totalSeg;
            this.etapa     = 'foco';
            this.timer     = setInterval(() => this.tique(), 1000);
        },
        tique() {
            this.restante--;
            document.title = this.relogio + ' — Hiperfoco | Norte';
            if (this.restante <= 0) this.terminar();
        },
        terminar() {
            clearInterval(this.timer);
            this.etapa = 'fim';
            document.title = 'Sessão concluída | Norte';
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Hiperfoco completo', { body: this.tarefa + ' — ' + this.minutos + ' minutos de foco.', icon: '/icon.svg' });
            }
            fetch('{{ route('foco.sessao') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ titulo: this.tarefa, minutos: this.minutos }),
            }).catch(() => {});
        },
        parar() {
            clearInterval(this.timer);
            this.etapa = 'escolha';
            document.title = 'Norte — Modo Hiperfoco';
        },
        escolherSugestao(s) {
            this.tarefa = s.titulo;
            this.compromissoId = s.id;
        },
        get relogio() {
            const m = Math.floor(this.restante / 60), s = this.restante % 60;
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        },
        get progresso() {
            return this.totalSeg ? (this.totalSeg - this.restante) / this.totalSeg : 0;
        }
     }">

    {{-- ─── Etapa 1: escolher UMA coisa ─────────────────────────────────── --}}
    <div x-show="etapa === 'escolha'" class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold flex items-center justify-center gap-2">
                <i data-lucide="zap" class="w-6 h-6 text-foco-accent"></i> Modo Hiperfoco
            </h1>
            <p class="text-sm text-foco-muted mt-2">
                Hiperfoco é o superpoder do cérebro TDAH. Escolha <strong class="text-foco-text">uma coisa só</strong> e mergulhe.
            </p>
        </div>

        <div class="card p-5 space-y-4">
            <input type="text" x-model="tarefa" maxlength="80"
                   placeholder="No que você vai focar agora?"
                   class="w-full rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">

            @if($sugestoes->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-foco-muted mb-2">Do seu dia de hoje:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($sugestoes as $s)
                    <button type="button"
                            @click='escolherSugestao({{ json_encode(['id' => $s->id, 'titulo' => $s->titulo]) }})'
                            class="text-sm font-semibold px-3 py-2 rounded-xl border-2 transition-colors"
                            :class="compromissoId === {{ $s->id }} ? 'border-foco-accent text-foco-accent' : 'border-foco-border text-foco-muted hover:border-foco-accent'">
                        {{ $s->titulo }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <p class="text-xs font-semibold text-foco-muted mb-2">Por quanto tempo?</p>
                <div class="flex gap-2">
                    <template x-for="m in [15, 25, 45]" :key="m">
                        <button type="button" @click="minutos = m"
                                class="flex-1 py-3 rounded-xl border-2 font-bold transition-colors"
                                :class="minutos === m ? 'border-foco-accent bg-foco-accent text-white' : 'border-foco-border text-foco-muted hover:border-foco-accent'">
                            <span x-text="m + ' min'"></span>
                        </button>
                    </template>
                </div>
            </div>

            <button @click="comecar()" :disabled="!tarefa.trim()"
                    class="btn-primary w-full bg-foco-accent text-white rounded-xl py-3.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                <i data-lucide="zap" class="w-5 h-5"></i> Entrar em hiperfoco
            </button>
        </div>

        <p class="text-xs text-foco-muted text-center">
            Celular longe, uma aba só. O resto do mundo pode esperar <span x-text="minutos"></span> minutos.
        </p>
    </div>

    {{-- ─── Etapa 2: focando ────────────────────────────────────────────── --}}
    <div x-show="etapa === 'foco'" x-cloak class="text-center space-y-8 py-8">
        <p class="text-sm font-semibold text-foco-muted uppercase tracking-wide">Focando em</p>
        <h1 class="text-2xl font-bold -mt-6" x-text="tarefa"></h1>

        <div class="relative w-64 h-64 mx-auto">
            <svg viewBox="0 0 200 200" class="w-full h-full -rotate-90">
                <circle cx="100" cy="100" r="88" fill="none" stroke="#E4E4F0" stroke-width="10"/>
                <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" class="text-foco-accent" stroke-width="10"
                        stroke-linecap="round"
                        :stroke-dasharray="2 * Math.PI * 88"
                        :stroke-dashoffset="2 * Math.PI * 88 * progresso"/>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-5xl font-bold tabular-nums" x-text="relogio"></span>
            </div>
        </div>

        <button @click="parar()" class="text-sm text-foco-muted hover:text-foco-text underline">
            Parar o foco (sem culpa — recomeçar também é foco)
        </button>
    </div>

    {{-- ─── Etapa 3: conseguiu ──────────────────────────────────────────── --}}
    <div x-show="etapa === 'fim'" x-cloak class="text-center space-y-6 py-10">
        <span class="w-16 h-16 mx-auto rounded-full flex items-center justify-center"
              style="background:rgba(34,197,94,.14)">
            <i data-lucide="check" class="w-8 h-8 text-foco-entrada"></i>
        </span>
        <h1 class="text-2xl font-bold">Você hiperfocou por <span x-text="minutos"></span> minutos</h1>
        <p class="text-foco-muted" x-text="tarefa"></p>

        <div class="flex flex-col gap-3 max-w-xs mx-auto">
            <form x-show="compromissoId" :action="'{{ url('/agenda') }}/' + compromissoId + '/concluir'" method="POST">
                @csrf
                <button type="submit"
                        class="btn-primary w-full bg-foco-entrada text-white rounded-xl py-3.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                    <i data-lucide="check" class="w-5 h-5"></i> Marcar como feito na agenda
                </button>
            </form>
            <button @click="etapa = 'escolha'; tarefa = ''; compromissoId = null"
                    class="w-full border-2 border-foco-accent text-foco-accent font-bold rounded-xl py-3 hover:bg-foco-surface transition-colors">
                Focar em outra coisa
            </button>
            <a href="{{ route('agenda.index') }}" class="text-sm text-foco-muted hover:text-foco-text underline">
                Voltar para a agenda
            </a>
        </div>
    </div>
</div>
@endsection

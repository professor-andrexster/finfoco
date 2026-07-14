@extends('layouts.app')

@section('title', 'Agenda')

@section('content')
@php
    $ehHoje    = $dia->isToday();
    $anterior  = $dia->copy()->subDay()->toDateString();
    $proximo   = $dia->copy()->addDay()->toDateString();
    $tituloDia = $ehHoje ? 'Hoje' : ($dia->isTomorrow() ? 'Amanhã' : ($dia->isYesterday() ? 'Ontem' : $dia->translatedFormat('l')));
    $feitos    = $compromissos->where('concluido', true)->count()
               + $rotinas->filter(fn($r) => $r->feitaEm($dia))->count();
    $total     = $compromissos->count() + $rotinas->count();
    $eventosNotificacao = $compromissos->where('concluido', false)->whereNotNull('hora')->values()->map(fn($c) => [
        'id'     => 'c' . $c->id,
        'titulo' => $c->titulo,
        'data'   => $c->data->toDateString(),
        'hora'   => substr($c->hora, 0, 5),
        'avisar' => $c->lembrete_min,
    ])->concat(
        $rotinas->filter(fn($r) => $r->hora && !$r->feitaEm($dia))->values()->map(fn($r) => [
            'id'     => 'r' . $r->id,
            'titulo' => $r->titulo,
            'data'   => $dia->toDateString(),
            'hora'   => substr($r->hora, 0, 5),
            'avisar' => 10,
        ])
    )->concat(
        collect($eventosGoogle)->filter(fn($g) => $g['hora'])->values()->map(fn($g) => [
            'id'     => 'g' . substr(md5($g['titulo'] . $g['hora']), 0, 10),
            'titulo' => $g['titulo'],
            'data'   => $dia->toDateString(),
            'hora'   => $g['hora'],
            'avisar' => 30,
        ])
    )->values();

    // Compromissos do FinFoco + eventos do Google numa linha do tempo só
    $itens = $compromissos->map(fn($c) => ['tipo' => 'app', 'obj' => $c, 'hora' => $c->hora])
        ->concat(collect($eventosGoogle)->map(fn($g) => [
            'tipo' => 'google', 'titulo' => $g['titulo'],
            'hora' => $g['hora'] ? $g['hora'] . ':00' : null,
        ]))
        ->sortBy(fn($i) => $i['hora'] ?? '')
        ->values();
@endphp

<div class="max-w-2xl lg:max-w-6xl mx-auto space-y-6">

    {{-- Navegação do dia --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('agenda.index', ['data' => $anterior]) }}"
           class="w-11 h-11 card card-hover flex items-center justify-center text-foco-muted hover:text-foco-text">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold capitalize">{{ $tituloDia }}</h1>
            <p class="text-sm text-foco-muted">{{ $dia->translatedFormat('d \d\e F') }}</p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('agenda.semana', ['data' => $dia->toDateString()]) }}" class="text-xs font-semibold text-foco-accent hover:underline">
                    Ver semana
                </a>
                @unless($ehHoje)
                    <a href="{{ route('agenda.index') }}" class="text-xs font-semibold text-foco-accent hover:underline">
                        Voltar para hoje
                    </a>
                @endunless
            </div>
        </div>
        <a href="{{ route('agenda.index', ['data' => $proximo]) }}"
           class="w-11 h-11 card card-hover flex items-center justify-center text-foco-muted hover:text-foco-text">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </a>
    </div>

    {{-- Desktop: linha do tempo à esquerda, rotinas e integrações à direita --}}
    <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-6 lg:items-start space-y-6 lg:space-y-0">
    <div class="space-y-6">

    {{-- Progresso do dia (recompensa visual imediata) --}}
    @if($total > 0)
    <div class="card px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold">{{ $feitos }} de {{ $total }} concluído{{ $total > 1 ? 's' : '' }}</span>
            @if($feitos === $total)
                <span class="text-sm font-bold text-foco-entrada flex items-center gap-1">
                    <i data-lucide="party-popper" class="w-4 h-4"></i> Dia completo!
                </span>
            @endif
        </div>
        <div class="h-2.5 rounded-full bg-foco-surface overflow-hidden">
            <div class="h-full rounded-full bg-foco-entrada transition-all duration-500"
                 style="width: {{ $total ? round($feitos / $total * 100) : 0 }}%"></div>
        </div>
    </div>
    @endif

    {{-- Adicionar compromisso: máximo 3 campos, hora opcional --}}
    <form action="{{ route('agenda.store') }}" method="POST" class="card p-5 space-y-3">
        @csrf
        <input type="text" name="titulo" maxlength="80" required
               placeholder="O que você precisa fazer ou onde estar?"
               value="{{ old('titulo') }}"
               class="w-full rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">
        <div class="flex gap-3">
            <input type="date" name="data" required value="{{ old('data', $dia->toDateString()) }}"
                   class="flex-1 rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">
            <input type="time" name="hora" value="{{ old('hora') }}"
                   class="w-32 rounded-xl border px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-foco-accent">
        </div>
        <button type="submit"
                class="btn-primary w-full bg-foco-accent text-white rounded-xl py-3.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
            <i data-lucide="calendar-plus" class="w-5 h-5"></i> Salvar compromisso
        </button>
        <p class="text-xs text-foco-muted text-center">Hora é opcional — sem hora, vale o dia todo.</p>
    </form>

    {{-- Linha do tempo do dia --}}
    @if($itens->isEmpty())
        <div class="card p-10 text-center space-y-3">
            <i data-lucide="calendar" class="w-10 h-10 mx-auto text-foco-muted"></i>
            <p class="font-semibold">Nada marcado {{ $ehHoje ? 'para hoje' : 'neste dia' }}.</p>
            <p class="text-sm text-foco-muted">Adicione um compromisso no campo acima. Um de cada vez. 💜</p>
        </div>
    @else
        <ul id="timeline" class="space-y-3" data-hoje="{{ $ehHoje ? '1' : '0' }}">
            @foreach($itens as $item)
            @if($item['tipo'] === 'google')
            @php
                $gMin = $item['hora'] ? ((int) substr($item['hora'], 0, 2)) * 60 + (int) substr($item['hora'], 3, 2) : null;
            @endphp
            <li data-min="{{ $gMin ?? -1 }}" class="card p-4 flex items-center gap-4">
                <span class="w-11 h-11 rounded-full border-2 border-foco-border flex items-center justify-center shrink-0 text-foco-muted"
                      title="Evento do Google Agenda (somente leitura)">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-base">{{ $item['titulo'] }}</p>
                    <p class="text-sm text-foco-muted">
                        {{ $item['hora'] ? substr($item['hora'], 0, 5) : 'O dia todo' }}
                    </p>
                </div>
                <span class="text-[10px] font-bold text-foco-muted bg-foco-surface px-2 py-1 rounded-full shrink-0">GOOGLE</span>
            </li>
            @else
            @php
                $c        = $item['obj'];
                $minutos  = $c->hora ? ((int) substr($c->hora, 0, 2)) * 60 + (int) substr($c->hora, 3, 2) : null;
                $atrasado = $ehHoje && !$c->concluido && $minutos !== null && $minutos < (now()->hour * 60 + now()->minute);
            @endphp
            @php
                $passosFeitos = $c->steps->where('concluido', true)->count();
                $passosTotal  = $c->steps->count();
            @endphp
            <li data-min="{{ $minutos ?? -1 }}" x-data="{ passos: false }"
                class="card card-hover p-4 transition-opacity
                       {{ $c->concluido ? 'opacity-50' : '' }}
                       {{ $atrasado ? 'ring-2 ring-foco-alerta' : '' }}">

                <div class="flex items-center gap-4">
                    {{-- Check grande: feedback < 200ms via Alpine antes do POST --}}
                    <form action="{{ route('agenda.concluir', $c) }}" method="POST" x-data="{ ok: {{ $c->concluido ? 'true' : 'false' }} }">
                        @csrf
                        <button type="submit" @click="ok = !ok"
                                class="w-11 h-11 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                                :class="ok ? 'bg-foco-entrada border-foco-entrada text-white' : 'border-foco-border text-transparent hover:border-foco-entrada'"
                                title="{{ $c->concluido ? 'Desmarcar compromisso' : 'Concluir compromisso' }}">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </button>
                    </form>

                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-base {{ $c->concluido ? 'line-through' : '' }}">{{ $c->titulo }}</p>
                        <p class="text-sm {{ $atrasado ? 'text-foco-alerta font-semibold' : 'text-foco-muted' }}">
                            @if($c->hora)
                                <i data-lucide="clock" class="w-3.5 h-3.5 inline -mt-0.5"></i>
                                {{ substr($c->hora, 0, 5) }}
                                @if($atrasado) · passou da hora @endif
                            @else
                                O dia todo
                            @endif
                        </p>
                    </div>

                    {{-- Micro-passos: abre o painel de quebrar em pedaços pequenos --}}
                    <button @click="passos = !passos"
                            class="flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors shrink-0
                                   {{ $passosTotal > 0 ? 'text-foco-accent' : 'text-foco-muted hover:text-foco-accent' }}"
                            style="{{ $passosTotal > 0 ? 'background:#6366F118' : '' }}"
                            title="Quebrar em passos pequenos">
                        <i data-lucide="list-tree" class="w-4 h-4"></i>
                        @if($passosTotal > 0)
                            {{ $passosFeitos }}/{{ $passosTotal }}
                        @else
                            <span class="hidden sm:inline">Passos</span>
                        @endif
                    </button>

                    <form action="{{ route('agenda.destroy', $c) }}" method="POST"
                          onsubmit="return confirm('Remover este compromisso?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-2" title="Excluir compromisso">
                            <i data-lucide="trash-2" class="w-4.5 h-4.5" style="width:18px;height:18px"></i>
                        </button>
                    </form>
                </div>

                {{-- Painel de micro-passos --}}
                <div x-show="passos" x-transition x-cloak class="mt-3 ml-[60px] space-y-2">
                    @foreach($c->steps as $passo)
                    <div class="flex items-center gap-2.5">
                        <form action="{{ route('agenda.passos.toggle', $passo) }}" method="POST"
                              x-data="{ ok: {{ $passo->concluido ? 'true' : 'false' }} }">
                            @csrf
                            <button type="submit" @click="ok = !ok"
                                    class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                                    :class="ok ? 'bg-foco-entrada border-foco-entrada text-white' : 'border-foco-border text-transparent hover:border-foco-entrada'"
                                    title="{{ $passo->concluido ? 'Desmarcar passo' : 'Concluir passo' }}">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                        <span class="flex-1 text-sm {{ $passo->concluido ? 'line-through text-foco-muted' : '' }}">{{ $passo->titulo }}</span>
                        <form action="{{ route('agenda.passos.destroy', $passo) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-1" title="Excluir passo">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach

                    <form action="{{ route('agenda.passos.store', $c) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="titulo" maxlength="80" required
                               placeholder="Um passo pequeno. Ex.: separar os documentos"
                               class="flex-1 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-foco-accent">
                        <button type="submit"
                                class="rounded-lg px-3 py-2 text-sm font-semibold text-white bg-foco-accent hover:opacity-90 transition-opacity shrink-0">
                            Adicionar passo
                        </button>
                    </form>
                </div>
            </li>
            @endif
            @endforeach

            {{-- Linha do AGORA: reposicionada por script conforme a hora atual --}}
            <li id="linha-agora" class="items-center gap-2 py-1" style="display:none">
                <span class="text-[11px] font-bold text-white bg-foco-accent px-2 py-0.5 rounded-full shrink-0">
                    AGORA · <span id="agora-hora"></span>
                </span>
                <span class="flex-1 h-0.5 rounded bg-foco-accent"></span>
            </li>
        </ul>
    @endif

    </div>{{-- /coluna principal --}}

    {{-- Coluna lateral (desktop) / continuação (mobile) --}}
    <div class="space-y-6">

    {{-- Rotinas do dia --}}
    @if($rotinas->isNotEmpty())
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-foco-muted uppercase tracking-wide flex items-center gap-1.5">
                <i data-lucide="repeat" class="w-4 h-4"></i> Rotinas do dia
            </h2>
            <a href="{{ route('routines.index') }}" class="text-xs font-semibold text-foco-accent hover:underline">
                Gerenciar rotinas
            </a>
        </div>
        <ul class="space-y-3">
            @foreach($rotinas as $r)
            @php $feita = $r->feitaEm($dia); $sequencia = $r->streak(); @endphp
            <li class="card card-hover p-4 flex items-center gap-4 {{ $feita ? 'opacity-50' : '' }}">
                <form action="{{ route('routines.check', $r) }}" method="POST" x-data="{ ok: {{ $feita ? 'true' : 'false' }} }">
                    @csrf
                    <input type="hidden" name="data" value="{{ $dia->toDateString() }}">
                    <button type="submit" @click="ok = !ok"
                            class="w-11 h-11 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                            :class="ok ? 'bg-foco-entrada border-foco-entrada text-white' : 'border-foco-border text-transparent hover:border-foco-entrada'"
                            title="{{ $feita ? 'Desmarcar rotina' : 'Concluir rotina' }}">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </button>
                </form>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-base {{ $feita ? 'line-through' : '' }}">{{ $r->titulo }}</p>
                    <p class="text-sm text-foco-muted">
                        {{ $r->hora ? substr($r->hora, 0, 5) : 'Qualquer hora' }}
                    </p>
                </div>
                @if($sequencia > 0)
                <span class="flex items-center gap-1 text-sm font-bold text-foco-accent shrink-0"
                      title="{{ $sequencia }} dia{{ $sequencia > 1 ? 's' : '' }} seguidos">
                    <i data-lucide="flame" class="w-4 h-4"></i> {{ $sequencia }}
                </span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <a href="{{ route('routines.index') }}" class="card card-hover p-4 flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#EEF2FF">
            <i data-lucide="repeat" class="w-5 h-5 text-foco-accent"></i>
        </span>
        <span>
            <span class="block font-semibold text-sm">Criar rotinas diárias</span>
            <span class="block text-xs text-foco-muted">Hábitos com sequência 🔥 que aparecem aqui todo dia.</span>
        </span>
    </a>
    @endif

    {{-- Alertas do navegador --}}
    <div x-data="{ perm: ('Notification' in window) ? Notification.permission : 'unsupported' }">
        <button x-show="perm === 'default'" x-cloak
                @click="Notification.requestPermission().then(p => { perm = p; if (p === 'granted' && window.finfocoAssinarPush) window.finfocoAssinarPush(); })"
                class="card card-hover w-full p-4 flex items-center gap-3 text-left">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#D9770618">
                <i data-lucide="bell-ring" class="w-5 h-5 text-foco-alerta"></i>
            </span>
            <span>
                <span class="block font-semibold text-sm">Ativar alertas no navegador</span>
                <span class="block text-xs text-foco-muted">Avisamos 30 min antes de cada compromisso, mesmo em outra aba.</span>
            </span>
        </button>
        <p x-show="perm === 'granted'" x-cloak class="text-xs text-foco-muted flex items-center gap-1.5 px-1">
            <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-foco-entrada"></i>
            Alertas do navegador ativados — avisamos antes de cada compromisso.
        </p>
    </div>

    {{-- Google Agenda --}}
    <div x-data="{ aberto: false, copiado: false }" class="card p-5">
        <button @click="aberto = !aberto" class="w-full flex items-center justify-between text-left">
            <span class="flex items-center gap-2 font-semibold text-sm">
                <i data-lucide="calendar-sync" class="w-4.5 h-4.5 text-foco-accent" style="width:18px;height:18px"></i>
                Ver no Google Agenda
            </span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-foco-muted transition-transform" :class="aberto ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="aberto" x-transition x-cloak class="mt-4 space-y-3">
            <ol class="text-sm text-foco-muted space-y-1.5 list-decimal list-inside">
                <li>Copie o link secreto abaixo.</li>
                <li>No Google Agenda: <strong class="text-foco-text">Outras agendas → + → Com um URL</strong>.</li>
                <li>Cole o link e pronto — seus compromissos aparecem lá.</li>
            </ol>
            <div class="flex gap-2">
                <input type="text" readonly value="{{ $icsUrl }}" id="ics-url"
                       class="flex-1 rounded-xl border px-3 py-2.5 text-xs text-foco-muted">
                <button @click="navigator.clipboard.writeText(document.getElementById('ics-url').value); copiado = true; setTimeout(() => copiado = false, 3000)"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white shrink-0 transition-colors"
                        :class="copiado ? 'bg-foco-entrada' : 'bg-foco-accent hover:opacity-90'">
                    <span x-show="!copiado">Copiar link</span>
                    <span x-show="copiado" x-cloak>Copiado ✓</span>
                </button>
            </div>
            <p class="text-xs text-foco-muted">Este link é só seu. Não compartilhe — quem tiver o link vê sua agenda.</p>
        </div>
    </div>

    </div>{{-- /coluna lateral --}}
    </div>{{-- /grid desktop --}}
</div>
@endsection

@push('scripts')
<script>
    // ── Linha do AGORA: posiciona o marcador entre o que já passou e o que vem ──
    (function () {
        const linha = document.getElementById('linha-agora');
        const lista = document.getElementById('timeline');
        if (!linha || !lista || lista.dataset.hoje !== '1') return;

        function posicionar() {
            const agora = new Date();
            const min   = agora.getHours() * 60 + agora.getMinutes();
            document.getElementById('agora-hora').textContent =
                String(agora.getHours()).padStart(2, '0') + ':' + String(agora.getMinutes()).padStart(2, '0');

            let anterior = null;
            for (const li of lista.querySelectorAll('li[data-min]')) {
                if (parseInt(li.dataset.min) <= min) anterior = li;
            }
            linha.style.display = 'flex';
            if (anterior) anterior.after(linha); else lista.prepend(linha);
        }

        posicionar();
        setInterval(posicionar, 30000);
    })();

    // ── Alertas do navegador: avisa lembrete_min antes de cada compromisso ──
    (function () {
        if (!('Notification' in window)) return;
        const eventos = @json($eventosNotificacao);

        function checar() {
            if (Notification.permission !== 'granted') return;
            const agora = new Date();
            eventos.forEach(e => {
                const inicio = new Date(e.data + 'T' + e.hora + ':00');
                const avisarEm = new Date(inicio.getTime() - e.avisar * 60000);
                const chave = 'finfoco-notif-' + e.id + '-' + e.data;
                if (agora >= avisarEm && agora <= inicio && !localStorage.getItem(chave)) {
                    new Notification('⏰ ' + e.hora + ' — ' + e.titulo, {
                        body: 'Começa em breve. Respira, você consegue.',
                        icon: '/icon.svg',
                    });
                    localStorage.setItem(chave, '1');
                }
            });
        }

        checar();
        setInterval(checar, 30000);
    })();
</script>
@endpush

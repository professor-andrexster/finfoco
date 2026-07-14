{{-- Prévia animada do app (puro HTML/CSS — representa a tela Hoje real) --}}
<div class="p-4 space-y-3 text-left bg-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold leading-none">Hoje</p>
            <p class="text-[10px] text-foco-muted mt-1">{{ today()->translatedFormat('l, d \d\e F') }}</p>
        </div>
        <span class="text-[9px] font-bold text-foco-accent px-2 py-1 rounded-full" style="background:#EEF2FF">2 de 4</span>
    </div>

    <div class="h-1.5 rounded-full overflow-hidden" style="background:#F7F7FD">
        <div class="h-full rounded-full bg-foco-entrada anima-barra"></div>
    </div>

    {{-- Rotina feita --}}
    <div class="flex items-center gap-2.5 rounded-xl p-2.5" style="box-shadow:0 0 0 1px #EEEEF6">
        <span class="w-6 h-6 rounded-full bg-foco-entrada flex items-center justify-center shrink-0">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-semibold line-through text-foco-muted leading-tight">Tomar o remédio</p>
            <p class="text-[9px] text-foco-muted">08:00</p>
        </div>
        <span class="flex items-center gap-0.5 text-[9px] font-bold text-foco-accent">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            12
        </span>
    </div>

    {{-- Linha do AGORA --}}
    <div class="flex items-center gap-1.5 py-0.5">
        <span class="text-[8px] font-bold text-white bg-foco-accent px-1.5 py-0.5 rounded-full anima-pulso shrink-0">AGORA · 14:32</span>
        <span class="flex-1 h-px bg-foco-accent opacity-60"></span>
    </div>

    {{-- Compromisso sendo concluído (anima em loop) --}}
    <div class="flex items-center gap-2.5 rounded-xl p-2.5" style="box-shadow:0 0 0 1px #EEEEF6">
        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 anima-check">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-semibold leading-tight">Reunião com a equipe</p>
            <p class="text-[9px] text-foco-muted">15:00 · 3 micro-passos</p>
        </div>
    </div>

    {{-- Evento do Google --}}
    <div class="flex items-center gap-2.5 rounded-xl p-2.5 opacity-80" style="box-shadow:0 0 0 1px #EEEEF6">
        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0" style="box-shadow: inset 0 0 0 1.5px #E4E4F0">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#9794B8" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-semibold leading-tight">Dentista</p>
            <p class="text-[9px] text-foco-muted">17:30</p>
        </div>
        <span class="text-[7px] font-bold text-foco-muted px-1.5 py-0.5 rounded-full" style="background:#F7F7FD">GOOGLE</span>
    </div>

    {{-- Constância --}}
    <div class="flex items-center gap-1 pt-1">
        <span class="text-[8px] text-foco-muted mr-1">Constância</span>
        @foreach([.3,.55,.3,.8,1,.55,.8,.3,.55,1,.8,.55] as $i => $a)
        <span class="w-2 h-2 rounded-[2px] {{ $i === 11 ? 'anima-quadrado' : '' }}" style="background:rgba(99,102,241,{{ $a }})"></span>
        @endforeach
    </div>
</div>

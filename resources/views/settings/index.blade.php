@extends('layouts.app')
@section('title', 'Configurações')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="settings-2" class="w-6 h-6 text-foco-accent"></i>
        Configurações
    </h1>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-5">
        @csrf

        <div class="card rounded-2xl p-6 space-y-5">
            <h2 class="font-semibold text-foco-text flex items-center gap-2">
                <i data-lucide="brain" class="w-4 h-4 text-foco-accent"></i>
                Recursos TDAH
            </h2>

            {{-- Valor da hora --}}
            <div>
                <label for="valor_hora" class="block text-sm font-medium mb-1 text-foco-muted">
                    Valor da sua hora (R$/h)
                </label>
                <p class="text-xs text-foco-muted mb-2">
                    Ao lançar uma saída, mostramos quantas horas de trabalho ela representa.
                </p>
                <input type="number" id="valor_hora" name="valor_hora" step="0.01" min="0"
                       value="{{ old('valor_hora', $valorHora) }}" placeholder="Ex: 50,00"
                       class="w-full bg-white border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
            </div>

            {{-- Limite anti-impulso --}}
            <div>
                <label for="limite_impulso" class="block text-sm font-medium mb-1 text-foco-muted">
                    Limite da pausa anti-impulso (R$)
                </label>
                <p class="text-xs text-foco-muted mb-2">
                    Saídas acima deste valor exibem uma pergunta de confirmação antes de lançar.
                </p>
                <input type="number" id="limite_impulso" name="limite_impulso" step="0.01" min="0"
                       value="{{ old('limite_impulso', $limiteImpulso) }}" placeholder="150,00"
                       class="w-full bg-white border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
            </div>

            {{-- Meta do dia a dia --}}
            <div>
                <label for="meta_dia_a_dia" class="block text-sm font-medium mb-1 text-foco-muted">
                    Meta de gasto do dia a dia por mês (R$)
                </label>
                <p class="text-xs text-foco-muted mb-2">
                    Quanto você quer gastar no máximo por mês fora das contas fixas e parcelas.
                    O dashboard mostra a barra de progresso.
                </p>
                <input type="number" id="meta_dia_a_dia" name="meta_dia_a_dia" step="0.01" min="0"
                       value="{{ old('meta_dia_a_dia', $metaDiaADia) }}" placeholder="Ex: 800,00"
                       class="w-full bg-white border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
            </div>
        </div>

        <div class="card rounded-2xl p-6 space-y-5">
            <h2 class="font-semibold text-foco-text flex items-center gap-2">
                <i data-lucide="calendar-sync" class="w-4 h-4 text-foco-accent"></i>
                Google Agenda dentro do FinFoco
            </h2>

            <div>
                <label for="google_ics_url" class="block text-sm font-medium mb-1 text-foco-muted">
                    Endereço secreto em iCal da sua agenda Google
                </label>
                <p class="text-xs text-foco-muted mb-2">
                    No Google Agenda (computador): <strong>Configurações → sua agenda →
                    Integrar agenda → Endereço secreto no formato iCal</strong>. Cole aqui e
                    seus eventos do Google aparecem na agenda do FinFoco.
                </p>
                <input type="url" id="google_ics_url" name="google_ics_url" maxlength="500"
                       value="{{ old('google_ics_url', $googleIcsUrl) }}"
                       placeholder="https://calendar.google.com/calendar/ical/..."
                       class="w-full bg-white border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                @if($googleIcsUrl)
                <p class="text-xs text-foco-entrada mt-2 flex items-center gap-1">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    Conectado — seus eventos do Google aparecem na agenda. Apague o campo para desconectar.
                </p>
                @endif
            </div>
        </div>

        <button type="submit"
                class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="save" class="w-6 h-6"></i>
            Salvar configurações
        </button>
    </form>

    {{-- Alertas no Telegram --}}
    @if(config('services.telegram.bot_username'))
    <div class="card rounded-2xl p-6 mt-5">
        <h2 class="font-semibold text-foco-text flex items-center gap-2 mb-2">
            <i data-lucide="send" class="w-4 h-4 text-foco-accent"></i>
            Alertas no Telegram
        </h2>
        @if($telegramConectado)
            <p class="text-sm text-foco-entrada flex items-center gap-1.5 mb-4">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                Conectado — seus alertas de compromissos e rotinas chegam no Telegram.
            </p>
            <form action="{{ route('telegram.desconectar') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-semibold text-foco-saida hover:underline">
                    Desconectar Telegram
                </button>
            </form>
        @else
            <p class="text-sm text-foco-muted mb-4">
                Receba os avisos de compromissos e rotinas direto no Telegram —
                funciona mesmo com o navegador fechado. Um toque e pronto.
            </p>
            <a href="{{ route('telegram.conectar') }}"
               class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
                <i data-lucide="send" class="w-6 h-6"></i>
                Conectar Telegram
            </a>
        @endif
    </div>
    @endif

    {{-- Suporte --}}
    <div class="card rounded-2xl p-6 mt-5">
        <h2 class="font-semibold text-foco-text flex items-center gap-2 mb-2">
            <i data-lucide="life-buoy" class="w-4 h-4 text-foco-accent"></i>
            Precisa de ajuda?
        </h2>
        <p class="text-sm text-foco-muted mb-4">
            Fale direto com o suporte pelo WhatsApp.
        </p>
        <a href="https://wa.me/5533984656356" target="_blank" rel="noopener"
           class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="message-circle" class="w-6 h-6"></i>
            Falar no WhatsApp
        </a>
    </div>
</div>
@endsection

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
        </div>

        <button type="submit"
                class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="save" class="w-6 h-6"></i>
            Salvar configurações
        </button>
    </form>
</div>
@endsection

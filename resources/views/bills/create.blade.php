@extends('layouts.app')
@section('title', 'Nova Conta')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-6 h-6 text-foco-accent"></i>
        Nova Conta
    </h1>

    <form action="{{ route('bills.store') }}" method="POST" class="space-y-5"
          x-data="{ tipo: '{{ old('tipo','pagar') }}', recorrente: {{ old('recorrente') ? 'true' : 'false' }}, maisOpcoes: false }">
        @csrf

        {{-- Tipo --}}
        <div>
            <label class="block text-sm font-medium mb-2 text-foco-muted">Tipo</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="tipo" value="pagar" x-model="tipo" class="sr-only">
                    <div :class="tipo==='pagar' ? 'border-foco-saida bg-foco-saida/10 text-foco-saida' : 'border-foco-border text-foco-muted hover:border-foco-saida/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i> A Pagar
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="tipo" value="receber" x-model="tipo" class="sr-only">
                    <div :class="tipo==='receber' ? 'border-foco-entrada bg-foco-entrada/10 text-foco-entrada' : 'border-foco-border text-foco-muted hover:border-foco-entrada/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-down-circle" class="w-5 h-5"></i> A Receber
                    </div>
                </label>
            </div>
        </div>

        {{-- Descrição --}}
        <div>
            <label for="descricao" class="block text-sm font-medium mb-2 text-foco-muted">Descrição</label>
            <input type="text" id="descricao" name="descricao" maxlength="60"
                   value="{{ old('descricao') }}" placeholder="Ex: Aluguel, Freelance..."
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
                   autofocus required>
        </div>

        {{-- Valor --}}
        <div>
            <label for="valor" class="block text-sm font-medium mb-2 text-foco-muted">Valor (R$)</label>
            <input type="number" id="valor" name="valor" step="0.01" min="0.01"
                   value="{{ old('valor') }}" placeholder="0,00"
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
                   required>
        </div>

        {{-- Vencimento --}}
        <div>
            <label for="vencimento" class="block text-sm font-medium mb-2 text-foco-muted">Data de Vencimento</label>
            <input type="date" id="vencimento" name="vencimento"
                   value="{{ old('vencimento', date('Y-m-d')) }}"
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
                   required>
        </div>

        {{-- Mais opções --}}
        <button type="button" @click="maisOpcoes = !maisOpcoes"
                class="flex items-center gap-2 text-foco-accent text-sm font-medium hover:underline">
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="maisOpcoes ? 'rotate-180' : ''"></i>
            <span x-text="maisOpcoes ? 'Menos opções' : 'Mais opções (categoria, recorrência)'"></span>
        </button>

        <div x-show="maisOpcoes" x-cloak style="display:none" class="space-y-4">
            {{-- Categoria --}}
            <div>
                <label for="categoria_id" class="block text-sm font-medium mb-2 text-foco-muted">Categoria</label>
                <select id="categoria_id" name="categoria_id"
                        class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                    <option value="">— Sem categoria —</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Recorrente --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" id="recorrente" name="recorrente" value="1"
                       x-model="recorrente"
                       {{ old('recorrente') ? 'checked' : '' }}
                       class="w-5 h-5 rounded accent-foco-accent">
                <label for="recorrente" class="text-sm font-medium text-foco-text cursor-pointer">
                    Conta recorrente
                </label>
            </div>

            <div x-show="recorrente" x-cloak style="display:none">
                <label for="recorrencia" class="block text-sm font-medium mb-2 text-foco-muted">Frequência</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['mensal'=>'Mensal','semanal'=>'Semanal','anual'=>'Anual'] as $val => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="recorrencia" value="{{ $val }}"
                               {{ old('recorrencia','mensal') === $val ? 'checked' : '' }} class="sr-only peer">
                        <div class="border-2 border-foco-border peer-checked:border-foco-accent peer-checked:bg-foco-accent/10
                                    rounded-xl p-3 text-center text-sm font-semibold transition-colors
                                    text-foco-muted peer-checked:text-foco-accent hover:border-foco-accent/50">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit"
                class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="save" class="w-6 h-6"></i>
            Salvar conta
        </button>
    </form>
</div>
@endsection

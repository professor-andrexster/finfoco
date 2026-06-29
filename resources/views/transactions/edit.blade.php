@extends('layouts.app')
@section('title', 'Editar Lançamento')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="pencil" class="w-6 h-6 text-foco-accent"></i>
        Editar Lançamento
    </h1>

    <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Tipo --}}
        <div x-data="{ tipo: '{{ old('tipo', $transaction->tipo) }}' }">
            <label class="block text-sm font-medium mb-2 text-foco-muted">Tipo</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="tipo" value="saida" x-model="tipo" class="sr-only">
                    <div :class="tipo === 'saida'
                            ? 'border-foco-saida bg-foco-saida/10 text-foco-saida'
                            : 'border-foco-border text-foco-muted hover:border-foco-saida/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
                        Saída
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="tipo" value="entrada" x-model="tipo" class="sr-only">
                    <div :class="tipo === 'entrada'
                            ? 'border-foco-entrada bg-foco-entrada/10 text-foco-entrada'
                            : 'border-foco-border text-foco-muted hover:border-foco-entrada/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-down-circle" class="w-5 h-5"></i>
                        Entrada
                    </div>
                </label>
            </div>
        </div>

        {{-- Valor --}}
        <div>
            <label for="valor" class="block text-sm font-medium mb-2 text-foco-muted">Valor (R$)</label>
            <input type="number" id="valor" name="valor" step="0.01" min="0.01"
                   value="{{ old('valor', $transaction->valor) }}"
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        {{-- Descrição --}}
        <div>
            <label for="descricao" class="block text-sm font-medium mb-2 text-foco-muted">Descrição</label>
            <input type="text" id="descricao" name="descricao" maxlength="60"
                   value="{{ old('descricao', $transaction->descricao) }}"
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        {{-- Categoria --}}
        <div>
            <label for="categoria_id" class="block text-sm font-medium mb-2 text-foco-muted">Categoria</label>
            <select id="categoria_id" name="categoria_id"
                    class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                <option value="">— Sem categoria —</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}"
                            {{ old('categoria_id', $transaction->categoria_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Data --}}
        <div>
            <label for="data" class="block text-sm font-medium mb-2 text-foco-muted">Data</label>
            <input type="date" id="data" name="data"
                   value="{{ old('data', $transaction->data->format('Y-m-d')) }}"
                   class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        <div class="flex gap-3">
            <a href="{{ route('history.index') }}"
               class="flex-1 text-center py-4 rounded-2xl border border-foco-border text-foco-muted hover:text-foco-text hover:border-foco-text transition-colors font-semibold">
                Cancelar
            </a>
            <button type="submit"
                    class="btn-primary flex-1 bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
                <i data-lucide="save" class="w-5 h-5"></i>
                Salvar alterações
            </button>
        </div>
    </form>

    {{-- Excluir --}}
    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="mt-4"
          onsubmit="return confirm('Excluir este lançamento?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="w-full py-3 rounded-2xl border border-foco-saida/40 text-foco-saida hover:bg-foco-saida/10 transition-colors flex items-center justify-center gap-2 text-sm font-semibold">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            Excluir lançamento
        </button>
    </form>
</div>
@endsection

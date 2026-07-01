@extends('layouts.app')
@section('title', 'Novo Alerta')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="bell-plus" class="w-6 h-6 text-foco-accent"></i>
        Novo Alerta
    </h1>

    <form action="{{ route('alerts.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Categoria --}}
        <div>
            <label for="categoria_id" class="block text-sm font-medium mb-2 text-foco-muted">Categoria</label>
            <select id="categoria_id" name="categoria_id"
                    class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                <option value="">— Escolha uma categoria —</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Limite --}}
        <div>
            <label for="limite_valor" class="block text-sm font-medium mb-2 text-foco-muted">Limite (R$)</label>
            <input type="number" id="limite_valor" name="limite_valor" step="0.01" min="0.01"
                   value="{{ old('limite_valor') }}" placeholder="0,00"
                   class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        {{-- Período --}}
        <div>
            <label class="block text-sm font-medium mb-2 text-foco-muted">Período</label>
            <div class="grid grid-cols-3 gap-3">
                @foreach(['dia' => 'Por dia', 'semana' => 'Por semana', 'mes' => 'Por mês'] as $val => $label)
                <label class="relative cursor-pointer">
                    <input type="radio" name="periodo" value="{{ $val }}"
                           {{ old('periodo', 'mes') === $val ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="border-2 border-foco-border peer-checked:border-foco-accent peer-checked:bg-foco-accent/10
                                rounded-xl p-3 text-center text-sm font-semibold transition-colors
                                text-foco-muted peer-checked:text-foco-accent hover:border-foco-accent/50">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit"
                class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="save" class="w-6 h-6"></i>
            Salvar alerta
        </button>
    </form>
</div>
@endsection

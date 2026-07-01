@extends('layouts.app')
@section('title', 'Histórico')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="clock" class="w-6 h-6 text-foco-accent"></i>
        Histórico
    </h1>
    <a href="{{ route('transactions.create') }}"
       class="flex items-center gap-2 bg-foco-accent hover:bg-foco-accent/80 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Novo
    </a>
</div>

{{-- Busca rápida --}}
<form method="GET" action="{{ route('history.index') }}" class="flex gap-3 mb-6">
    <div class="relative flex-1">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-foco-muted"></i>
        <input type="text" name="busca" value="{{ $busca }}" placeholder="Buscar descrição..."
               class="w-full bg-foco-surface border border-foco-border rounded-xl pl-10 pr-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
    </div>
    <select name="tipo"
            class="card rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        <option value="">Todos</option>
        <option value="entrada" {{ $tipo === 'entrada' ? 'selected' : '' }}>Entradas</option>
        <option value="saida"   {{ $tipo === 'saida'   ? 'selected' : '' }}>Saídas</option>
    </select>
    <button type="submit"
            class="bg-foco-accent hover:bg-foco-accent/80 text-white px-5 py-3 rounded-xl font-semibold transition-colors flex items-center gap-2">
        <i data-lucide="search" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Buscar</span>
    </button>
</form>

@if($transactions->isEmpty())
    <div class="text-center py-16 text-foco-muted">
        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p>Nenhum lançamento encontrado.</p>
    </div>
@else
    <div class="card rounded-2xl overflow-hidden">
        <ul class="divide-y divide-foco-border">
            @foreach($transactions as $t)
            <li class="flex items-center justify-between px-5 py-4 hover:bg-foco-border/30 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $t->categoria?->cor ?? '#64748B' }}22;">
                        <i data-lucide="{{ $t->categoria?->icone ?? 'tag' }}"
                           class="w-4 h-4"
                           style="color: {{ $t->categoria?->cor ?? '#64748B' }};"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $t->descricao }}</p>
                        <p class="text-foco-muted text-xs">
                            {{ $t->categoria?->nome ?? '—' }}
                            · {{ $t->data->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-lg {{ $t->tipo === 'entrada' ? 'text-foco-entrada' : 'text-foco-saida' }}">
                        {{ $t->tipo === 'entrada' ? '+' : '-' }}R$ {{ number_format($t->valor, 2, ',', '.') }}
                    </span>
                    <a href="{{ route('transactions.edit', $t) }}"
                       class="text-foco-muted hover:text-foco-accent transition-colors">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Paginação --}}
    @if($transactions->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $transactions->links() }}
    </div>
    @endif
@endif
@endsection

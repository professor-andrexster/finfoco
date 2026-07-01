@extends('layouts.app')
@section('title', 'Categorias')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="tag" class="w-6 h-6 text-foco-accent"></i>
        Categorias
    </h1>
    <a href="{{ route('categories.create') }}"
       class="flex items-center gap-2 bg-foco-accent hover:bg-foco-accent/80 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nova categoria
    </a>
</div>

@if($categorias->isEmpty())
    <div class="text-center py-16 text-foco-muted">
        <i data-lucide="tag" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p>Nenhuma categoria criada.</p>
    </div>
@else
    <div class="grid gap-3">
        @foreach($categorias as $cat)
        <div class="card rounded-xl flex items-center justify-between px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color: {{ $cat->cor }}22;">
                    <i data-lucide="{{ $cat->icone }}" class="w-5 h-5" style="color: {{ $cat->cor }};"></i>
                </div>
                <div>
                    <p class="font-semibold">{{ $cat->nome }}</p>
                    <p class="text-foco-muted text-xs">
                        {{ match($cat->tipo) { 'entrada' => 'Entrada', 'saida' => 'Saída', default => 'Ambos' } }}
                        · {{ $cat->transactions_count }} lançamento(s)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.edit', $cat) }}"
                   class="text-foco-muted hover:text-foco-accent transition-colors p-2">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @if($cat->transactions_count === 0)
                <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                      onsubmit="return confirm('Excluir categoria {{ $cat->nome }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection

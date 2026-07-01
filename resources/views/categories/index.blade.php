@extends('layouts.app')
@section('title', 'Categorias')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-foco-text">Categorias</h1>
        <p class="text-sm text-foco-muted mt-0.5">Organize seus lançamentos por tipo</p>
    </div>
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nova
    </a>
</div>

@if($categorias->isEmpty())
<div class="card px-8 py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-foco-surface mx-auto mb-4 flex items-center justify-center">
        <i data-lucide="tag" class="w-7 h-7 text-foco-muted"></i>
    </div>
    <p class="font-semibold text-foco-text mb-1">Nenhuma categoria</p>
    <p class="text-sm text-foco-muted mb-5">Crie categorias para organizar seus lançamentos.</p>
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-xl"
       style="background:#6366F1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Criar categoria
    </a>
</div>
@else

{{-- Globais (user_id null) --}}
@php $globais = $categorias->whereNull('user_id'); $pessoais = $categorias->whereNotNull('user_id'); @endphp

@if($globais->count())
<div class="mb-5">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="globe" class="w-3.5 h-3.5 text-foco-muted"></i>
        <p class="text-xs font-semibold text-foco-muted uppercase tracking-wide">Padrão do sistema</p>
    </div>
    <div class="card overflow-hidden divide-y divide-foco-border">
        @foreach($globais as $cat)
        @php $tipo = match($cat->tipo) { 'entrada' => 'Entrada', 'saida' => 'Saída', default => 'Ambos' }; @endphp
        <div class="flex items-center gap-4 px-5 py-3.5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background:{{ $cat->cor }}18">
                <i data-lucide="{{ $cat->icone }}" class="w-4.5 h-4.5" style="color:{{ $cat->cor }}"></i>
            </div>
            <div class="w-2 h-2 rounded-full shrink-0" style="background:{{ $cat->cor }}"></div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-foco-text">{{ $cat->nome }}</p>
                <p class="text-xs text-foco-muted mt-0.5">{{ $tipo }} · {{ $cat->transactions_count }} lançamento(s)</p>
            </div>
            <span class="text-xs text-foco-muted px-2 py-0.5 rounded-full bg-foco-surface shrink-0">global</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($pessoais->count())
<div>
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="user" class="w-3.5 h-3.5 text-foco-muted"></i>
        <p class="text-xs font-semibold text-foco-muted uppercase tracking-wide">Minhas categorias</p>
    </div>
    <div class="card overflow-hidden divide-y divide-foco-border">
        @foreach($pessoais as $cat)
        @php $tipo = match($cat->tipo) { 'entrada' => 'Entrada', 'saida' => 'Saída', default => 'Ambos' }; @endphp
        <div class="flex items-center gap-4 px-5 py-3.5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background:{{ $cat->cor }}18">
                <i data-lucide="{{ $cat->icone }}" class="w-4 h-4" style="color:{{ $cat->cor }}"></i>
            </div>
            <div class="w-2 h-2 rounded-full shrink-0" style="background:{{ $cat->cor }}"></div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-foco-text">{{ $cat->nome }}</p>
                <p class="text-xs text-foco-muted mt-0.5">{{ $tipo }} · {{ $cat->transactions_count }} lançamento(s)</p>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('categories.edit', $cat) }}"
                   class="p-2 rounded-lg text-foco-muted hover:text-foco-accent hover:bg-foco-surface transition-colors">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @if($cat->transactions_count === 0)
                <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                      onsubmit="return confirm('Excluir {{ addslashes($cat->nome) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="p-2 rounded-lg text-foco-muted hover:text-foco-saida hover:bg-red-50 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($globais->isEmpty() && $pessoais->isEmpty())
<p class="text-center text-foco-muted py-8">Nenhuma categoria.</p>
@endif

@endif
@endsection

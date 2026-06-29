@extends('layouts.app')
@section('title', 'Editar Categoria')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="pencil" class="w-6 h-6 text-foco-accent"></i>
        Editar Categoria
    </h1>

    <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        @include('categories._form')

        <div class="flex gap-3">
            <a href="{{ route('categories.index') }}"
               class="flex-1 text-center py-4 rounded-2xl border border-foco-border text-foco-muted hover:text-foco-text transition-colors font-semibold">
                Cancelar
            </a>
            <button type="submit"
                    class="btn-primary flex-1 bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
                <i data-lucide="save" class="w-5 h-5"></i>
                Salvar alterações
            </button>
        </div>
    </form>
</div>
@endsection

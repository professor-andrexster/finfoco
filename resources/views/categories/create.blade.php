@extends('layouts.app')
@section('title', 'Nova Categoria')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-6 h-6 text-foco-accent"></i>
        Nova Categoria
    </h1>

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-5">
        @csrf

        @include('categories._form')

        <button type="submit"
                class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
            <i data-lucide="save" class="w-6 h-6"></i>
            Salvar categoria
        </button>
    </form>
</div>
@endsection

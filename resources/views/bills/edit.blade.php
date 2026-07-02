@extends('layouts.app')
@section('title', 'Editar Conta')
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2 text-foco-text">
        <i data-lucide="pencil" class="w-6 h-6 text-foco-accent"></i>
        Editar Conta
    </h1>

    <form action="{{ route('bills.update', $bill) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Descrição</label>
                <input type="text" name="descricao" maxlength="60" value="{{ old('descricao', $bill->descricao) }}"
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       autofocus required>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Valor (R$)</label>
                <input type="number" name="valor" step="0.01" min="0.01" value="{{ old('valor', $bill->valor) }}"
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       required>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Vencimento</label>
                <input type="date" name="vencimento" value="{{ old('vencimento', $bill->vencimento->format('Y-m-d')) }}"
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Categoria (opcional)</label>
            <select name="categoria_id" class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                <option value="">— Sem categoria —</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('categoria_id', $bill->categoria_id)==$cat->id?'selected':'' }}>{{ $cat->nome }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary w-full text-white py-4 rounded-2xl flex items-center justify-center gap-3"
                style="background:#6366F1;box-shadow:0 4px 14px rgba(99,102,241,.3)">
            <i data-lucide="save" class="w-5 h-5"></i> Salvar alterações
        </button>
    </form>
</div>
@push('scripts')
<script>setTimeout(() => lucide.createIcons(), 100);</script>
@endpush
@endsection

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

        @if(!$bill->isParcelado())
        {{-- Tipo de cobrança: editável só em contas sem parcelas --}}
        <div class="card p-5"
             x-data="{ cobranca: '{{ old('_cobranca', $bill->recorrente ? 'recorrente' : 'avulsa') }}' }">
            <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-3">Tipo de cobrança</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="_cobranca" value="avulsa" x-model="cobranca" class="sr-only">
                    <div :class="cobranca==='avulsa' ? 'border-foco-accent bg-indigo-50 text-foco-accent' : 'border-foco-border text-foco-muted'"
                         class="border-2 rounded-xl p-3 flex items-center justify-center gap-2 text-sm font-semibold transition-colors">
                        <i data-lucide="circle-check" class="w-4 h-4"></i> Avulsa
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="_cobranca" value="recorrente" x-model="cobranca" class="sr-only">
                    <div :class="cobranca==='recorrente' ? 'border-foco-accent bg-indigo-50 text-foco-accent' : 'border-foco-border text-foco-muted'"
                         class="border-2 rounded-xl p-3 flex items-center justify-center gap-2 text-sm font-semibold transition-colors">
                        <i data-lucide="repeat" class="w-4 h-4"></i> Recorrente
                    </div>
                </label>
            </div>
            <div x-show="cobranca==='recorrente'" x-cloak style="display:none" class="mt-3">
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Frequência</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['mensal'=>'Mensal','semanal'=>'Semanal','anual'=>'Anual'] as $val=>$label)
                    <label class="cursor-pointer">
                        <input type="radio" name="recorrencia" value="{{ $val }}"
                               {{ old('recorrencia', $bill->recorrencia ?? 'mensal')===$val?'checked':'' }}
                               :disabled="cobranca!=='recorrente'" class="sr-only peer">
                        <div class="border-2 border-foco-border peer-checked:border-foco-accent peer-checked:bg-indigo-50 peer-checked:text-foco-accent rounded-xl p-2.5 text-center text-sm font-semibold transition-colors text-foco-muted">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

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

@extends('layouts.app')
@section('title', 'Nova Conta')
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2 text-foco-text">
        <i data-lucide="plus-circle" class="w-6 h-6 text-foco-accent"></i>
        Nova Conta
    </h1>

    <form action="{{ route('bills.store') }}" method="POST" class="space-y-5"
          x-data="{ tipo: '{{ old('tipo','pagar') }}', modo: '{{ old('parcelas_total') ? 'parcelado' : (old('recorrente') ? 'recorrente' : 'avista') }}' }">
        @csrf

        <div class="card p-5">
            <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-3">Tipo</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="tipo" value="pagar" x-model="tipo" class="sr-only">
                    <div :class="tipo==='pagar' ? 'border-foco-saida bg-red-50 text-foco-saida' : 'border-foco-border text-foco-muted'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-semibold transition-colors">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i> A Pagar
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="tipo" value="receber" x-model="tipo" class="sr-only">
                    <div :class="tipo==='receber' ? 'border-foco-entrada bg-green-50 text-foco-entrada' : 'border-foco-border text-foco-muted'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-semibold transition-colors">
                        <i data-lucide="arrow-down-circle" class="w-5 h-5"></i> A Receber
                    </div>
                </label>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Descrição</label>
                <input type="text" name="descricao" maxlength="60" value="{{ old('descricao') }}"
                       placeholder="Ex: Aluguel, Freelance..."
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       autofocus required>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Valor (R$)</label>
                <input type="number" name="valor" step="0.01" min="0.01" value="{{ old('valor') }}" placeholder="0,00"
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       required>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Primeiro vencimento</label>
                <input type="date" name="vencimento" value="{{ old('vencimento', date('Y-m-d')) }}"
                       class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent"
                       required>
            </div>
        </div>

        <div class="card p-5">
            <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-3">Tipo de cobrança</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['avista'=>['label'=>'À vista','icon'=>'circle-check'],'parcelado'=>['label'=>'Parcelado','icon'=>'layers'],'recorrente'=>['label'=>'Recorrente','icon'=>'repeat']] as $val=>$opt)
                <label class="cursor-pointer">
                    <input type="radio" name="_modo" value="{{ $val }}" x-model="modo" class="sr-only">
                    <div :class="modo==='{{ $val }}' ? 'border-foco-accent bg-indigo-50 text-foco-accent' : 'border-foco-border text-foco-muted'"
                         class="border-2 rounded-xl p-3 flex flex-col items-center gap-1 transition-colors text-center">
                        <i data-lucide="{{ $opt['icon'] }}" class="w-4 h-4"></i>
                        <span class="text-xs font-semibold">{{ $opt['label'] }}</span>
                    </div>
                </label>
                @endforeach
            </div>

            <div x-show="modo==='parcelado'" x-cloak style="display:none" class="mt-4">
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Número de parcelas</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="parcelas_total" min="2" max="360" value="{{ old('parcelas_total', 12) }}"
                           class="w-28 border border-foco-border rounded-xl px-4 py-3 text-foco-text font-bold text-xl text-center focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                    <span class="text-foco-muted text-sm">parcelas mensais</span>
                </div>
                <p class="text-xs text-foco-muted mt-2">Todas as parcelas são criadas de uma vez com vencimentos mensais a partir da data informada.</p>
            </div>

            <div x-show="modo==='recorrente'" x-cloak style="display:none" class="mt-4">
                <input type="hidden" name="recorrente" value="1">
                <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Frequência</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['mensal'=>'Mensal','semanal'=>'Semanal','anual'=>'Anual'] as $val=>$label)
                    <label class="cursor-pointer">
                        <input type="radio" name="recorrencia" value="{{ $val }}" {{ old('recorrencia','mensal')===$val?'checked':'' }} class="sr-only peer">
                        <div class="border-2 border-foco-border peer-checked:border-foco-accent peer-checked:bg-indigo-50 peer-checked:text-foco-accent rounded-xl p-3 text-center text-sm font-semibold transition-colors text-foco-muted">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-foco-muted mb-2">Categoria (opcional)</label>
            <select name="categoria_id" class="w-full border border-foco-border rounded-xl px-4 py-3 text-foco-text bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                <option value="">— Sem categoria —</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('categoria_id')==$cat->id?'selected':'' }}>{{ $cat->nome }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary w-full text-white py-4 rounded-2xl flex items-center justify-center gap-3"
                style="background:#6366F1;box-shadow:0 4px 14px rgba(99,102,241,.3)">
            <i data-lucide="save" class="w-5 h-5"></i> Salvar conta
        </button>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Novo Lançamento')

@section('content')
@php
    $limiteImpulso = (float) \App\Models\Setting::get('limite_impulso', '150.00');
    $valorHora     = (float) \App\Models\Setting::get('valor_hora', '0');
@endphp

<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-6 h-6 text-foco-accent"></i>
        Novo Lançamento
    </h1>

    <div x-data="{
        tipo: '{{ old('tipo','saida') }}',
        valor: {{ old('valor', 0) }},
        mostrarPausa: false,
        limiteImpulso: {{ $limiteImpulso }},
        valorHora: {{ $valorHora }},

        get custoEmHoras() {
            if (this.valorHora <= 0 || this.valor <= 0) return null;
            const h = Math.round((this.valor / this.valorHora) * 10) / 10;
            return h;
        },

        tentarEnviar(e) {
            if (this.tipo === 'saida' && parseFloat(this.valor) > this.limiteImpulso) {
                e.preventDefault();
                this.mostrarPausa = true;
            }
        },

        confirmarEnvio() {
            this.mostrarPausa = false;
            this.$refs.formLancamento.submit();
        }
    }">

        {{-- Modal anti-impulso --}}
        <div x-show="mostrarPausa" x-cloak style="display:none"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div class="bg-foco-surface border border-foco-border rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-foco-alerta/20 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="pause-circle" class="w-8 h-8 text-foco-alerta"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Isso é necessário agora?</h3>
                <p class="text-foco-muted text-sm mb-6">
                    Gasto de R$ <span x-text="parseFloat(valor).toFixed(2).replace('.',',')"></span>.
                    Só um respiro antes de lançar. 🧘
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="mostrarPausa = false"
                            class="py-3 rounded-xl border border-foco-border text-foco-muted hover:text-foco-text hover:border-foco-text transition-colors font-semibold">
                        Vou esperar
                    </button>
                    <button @click="confirmarEnvio()"
                            class="py-3 rounded-xl bg-foco-accent hover:bg-foco-accent/80 text-white font-semibold transition-colors">
                        Sim, lançar
                    </button>
                </div>
            </div>
        </div>

        <form x-ref="formLancamento" action="{{ route('transactions.store') }}" method="POST" class="space-y-5"
              @submit="tentarEnviar($event)">
            @csrf

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-foco-muted">Tipo</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="saida" x-model="tipo" class="sr-only">
                        <div :class="tipo==='saida' ? 'border-foco-saida bg-foco-saida/10 text-foco-saida' : 'border-foco-border text-foco-muted hover:border-foco-saida/50'"
                             class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                            <i data-lucide="arrow-up-circle" class="w-5 h-5"></i> Saída
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="entrada" x-model="tipo" class="sr-only">
                        <div :class="tipo==='entrada' ? 'border-foco-entrada bg-foco-entrada/10 text-foco-entrada' : 'border-foco-border text-foco-muted hover:border-foco-entrada/50'"
                             class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5"></i> Entrada
                        </div>
                    </label>
                </div>
            </div>

            {{-- Valor --}}
            <div>
                <label for="valor" class="block text-sm font-medium mb-2 text-foco-muted">Valor (R$)</label>
                <input type="number" id="valor" name="valor" step="0.01" min="0.01"
                       x-model="valor"
                       value="{{ old('valor') }}" placeholder="0,00"
                       class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
                       autofocus>
                {{-- Custo em horas --}}
                <p x-show="tipo === 'saida' && custoEmHoras !== null" x-cloak style="display:none"
                   class="text-foco-muted text-sm mt-1 flex items-center gap-1">
                    <i data-lucide="clock" class="w-3 h-3"></i>
                    ≈ <span x-text="custoEmHoras"></span>h de trabalho
                </p>
            </div>

            {{-- Descrição --}}
            <div>
                <label for="descricao" class="block text-sm font-medium mb-2 text-foco-muted">Descrição</label>
                <input type="text" id="descricao" name="descricao" maxlength="60"
                       value="{{ old('descricao') }}" placeholder="Ex: Mercado, Salário..."
                       class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
            </div>

            {{-- Categoria --}}
            <div>
                <label for="categoria_id" class="block text-sm font-medium mb-2 text-foco-muted">Categoria</label>
                <select id="categoria_id" name="categoria_id"
                        class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                    <option value="">— Sem categoria —</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Data --}}
            <div>
                <label for="data" class="block text-sm font-medium mb-2 text-foco-muted">Data</label>
                <input type="date" id="data" name="data"
                       value="{{ old('data', date('Y-m-d')) }}"
                       class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
            </div>

            <button type="submit"
                    class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors shadow-lg">
                <i data-lucide="save" class="w-6 h-6"></i>
                Salvar lançamento
            </button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Editar Lançamento')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="pencil" class="w-6 h-6 text-foco-accent"></i>
        Editar Lançamento
    </h1>

    <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Tipo --}}
        <div x-data="{ tipo: '{{ old('tipo', $transaction->tipo) }}' }">
            <label class="block text-sm font-medium mb-2 text-foco-muted">Tipo</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="tipo" value="saida" x-model="tipo" class="sr-only">
                    <div :class="tipo === 'saida'
                            ? 'border-foco-saida bg-foco-saida/10 text-foco-saida'
                            : 'border-foco-border text-foco-muted hover:border-foco-saida/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
                        Saída
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="tipo" value="entrada" x-model="tipo" class="sr-only">
                    <div :class="tipo === 'entrada'
                            ? 'border-foco-entrada bg-foco-entrada/10 text-foco-entrada'
                            : 'border-foco-border text-foco-muted hover:border-foco-entrada/50'"
                         class="border-2 rounded-xl p-4 flex items-center justify-center gap-2 font-bold text-lg transition-colors">
                        <i data-lucide="arrow-down-circle" class="w-5 h-5"></i>
                        Entrada
                    </div>
                </label>
            </div>
        </div>

        {{-- Valor --}}
        <div>
            <label for="valor" class="block text-sm font-medium mb-2 text-foco-muted">Valor (R$)</label>
            <input type="number" id="valor" name="valor" step="0.01" min="0.01"
                   value="{{ old('valor', $transaction->valor) }}"
                   class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        {{-- Descrição --}}
        <div>
            <label for="descricao" class="block text-sm font-medium mb-2 text-foco-muted">Descrição</label>
            <input type="text" id="descricao" name="descricao" maxlength="60"
                   value="{{ old('descricao', $transaction->descricao) }}"
                   class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        {{-- Categoria com cor visual --}}
        @php
            $catAtual = $categorias->firstWhere('id', old('categoria_id', $transaction->categoria_id));
        @endphp
        <div x-data="{
                aberto: false,
                catId: '{{ $catAtual?->id ?? '' }}',
                catNome: '{{ addslashes($catAtual?->nome ?? '') }}',
                catCor: '{{ $catAtual?->cor ?? '' }}',
                catIcone: '{{ $catAtual?->icone ?? '' }}',
                selecionar(id, nome, cor, icone) {
                    this.catId = id; this.catNome = nome; this.catCor = cor; this.catIcone = icone;
                    this.aberto = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            }" @click.outside="aberto = false">
            <label class="block text-sm font-medium mb-2 text-foco-muted">Categoria</label>
            <input type="hidden" name="categoria_id" :value="catId">
            <button type="button" @click="aberto = !aberto"
                    class="w-full flex items-center gap-3 border border-foco-border rounded-xl px-4 py-3 bg-white text-left transition-colors hover:border-foco-accent/50 focus:outline-none focus:border-foco-accent">
                <template x-if="catId">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                         :style="'background:' + catCor + '20'">
                        <i :data-lucide="catIcone" class="w-3.5 h-3.5" :style="'color:' + catCor"></i>
                    </div>
                </template>
                <span x-text="catNome || '— Sem categoria —'"
                      :class="catNome ? 'text-foco-text' : 'text-foco-muted'"
                      class="flex-1 text-sm"></span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-foco-muted shrink-0"
                   :class="aberto ? 'rotate-180' : ''" style="transition:transform .2s"></i>
            </button>
            <div x-show="aberto" x-cloak style="display:none"
                 class="mt-1 rounded-xl border border-foco-border bg-white shadow-lg overflow-hidden z-20 relative"
                 style="max-height:260px;overflow-y:auto">
                <button type="button" @click="selecionar('','','','')"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm text-foco-muted hover:bg-foco-surface transition-colors text-left">
                    <div class="w-6 h-6 rounded-lg bg-foco-border shrink-0"></div>
                    Sem categoria
                </button>
                @foreach($categorias as $cat)
                <button type="button"
                        @click="selecionar('{{ $cat->id }}','{{ addslashes($cat->nome) }}','{{ $cat->cor }}','{{ $cat->icone }}')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-foco-surface transition-colors text-left"
                        :class="catId == '{{ $cat->id }}' ? 'bg-foco-surface font-semibold' : ''">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                         style="background:{{ $cat->cor }}20">
                        <i data-lucide="{{ $cat->icone }}" class="w-3.5 h-3.5" style="color:{{ $cat->cor }}"></i>
                    </div>
                    <span class="text-foco-text">{{ $cat->nome }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Data --}}
        <div>
            <label for="data" class="block text-sm font-medium mb-2 text-foco-muted">Data</label>
            <input type="date" id="data" name="data"
                   value="{{ old('data', $transaction->data->format('Y-m-d')) }}"
                   class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        </div>

        <div class="flex gap-3">
            <a href="{{ route('history.index') }}"
               class="flex-1 text-center py-4 rounded-2xl border border-foco-border text-foco-muted hover:text-foco-text hover:border-foco-text transition-colors font-semibold">
                Cancelar
            </a>
            <button type="submit"
                    class="btn-primary flex-1 bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors">
                <i data-lucide="save" class="w-5 h-5"></i>
                Salvar alterações
            </button>
        </div>
    </form>

    {{-- Excluir --}}
    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="mt-4"
          onsubmit="return confirm('Excluir este lançamento?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="w-full py-3 rounded-2xl border border-foco-saida/40 text-foco-saida hover:bg-foco-saida/10 transition-colors flex items-center justify-center gap-2 text-sm font-semibold">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            Excluir lançamento
        </button>
    </form>
</div>
@endsection

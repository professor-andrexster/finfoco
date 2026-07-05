@extends('layouts.app')
@section('title', 'Histórico')
@php use App\Helpers\DateHelper; @endphp

@section('content')
@php $temFiltroAvancado = $categoriaId || $dataInicio || $dataFim; @endphp

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="clock" class="w-6 h-6 text-foco-accent"></i>
        Histórico
    </h1>
    <a href="{{ route('transactions.create') }}"
       class="flex items-center gap-2 bg-foco-accent hover:bg-foco-accent/80 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Novo lançamento
    </a>
</div>

{{-- Busca + filtros --}}
<div x-data="{ filtros: {{ $temFiltroAvancado ? 'true' : 'false' }} }" class="mb-6">
    <form method="GET" action="{{ route('history.index') }}">
        <div class="flex gap-3">
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
            <button type="button" @click="filtros = !filtros"
                    :class="filtros ? 'text-foco-accent border-foco-accent' : 'text-foco-muted border-foco-border'"
                    class="px-4 py-3 rounded-xl border bg-white font-semibold transition-colors flex items-center gap-2"
                    title="Mais filtros">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                @if($temFiltroAvancado)<span class="w-2 h-2 rounded-full bg-foco-accent"></span>@endif
            </button>
            <button type="submit"
                    class="bg-foco-accent hover:bg-foco-accent/80 text-white px-5 py-3 rounded-xl font-semibold transition-colors flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Buscar</span>
            </button>
        </div>

        {{-- Filtros avançados (abrem só quando pedidos) --}}
        <div x-show="filtros" x-cloak x-transition.opacity style="display:none"
             class="card mt-3 p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-foco-muted mb-1.5">Categoria</label>
                <select name="categoria_id"
                        class="w-full border border-foco-border rounded-xl px-3 py-2.5 text-sm text-foco-text focus:outline-none focus:border-foco-accent">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoriaId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-foco-muted mb-1.5">De</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}"
                       class="w-full border border-foco-border rounded-xl px-3 py-2.5 text-sm text-foco-text focus:outline-none focus:border-foco-accent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-foco-muted mb-1.5">Até</label>
                <input type="date" name="data_fim" value="{{ $dataFim }}"
                       class="w-full border border-foco-border rounded-xl px-3 py-2.5 text-sm text-foco-text focus:outline-none focus:border-foco-accent">
            </div>
            @if($temFiltroAvancado)
            <div class="sm:col-span-3">
                <a href="{{ route('history.index') }}"
                   class="text-xs text-foco-saida font-medium hover:underline flex items-center gap-1 w-fit">
                    <i data-lucide="x" class="w-3 h-3"></i> Limpar filtros
                </a>
            </div>
            @endif
        </div>
    </form>
</div>

@if($transactions->isEmpty())
    <div class="text-center py-16 text-foco-muted">
        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p>Nenhum lançamento encontrado.</p>
        @if($busca || $tipo || $temFiltroAvancado)
        <a href="{{ route('history.index') }}" class="text-sm text-foco-accent font-medium hover:underline mt-2 inline-block">
            Limpar filtros
        </a>
        @endif
    </div>
@else
    <div x-data="{ selecionando: false, marcados: [] }">

        {{-- Barra de seleção em lote --}}
        <div class="flex items-center justify-between mb-3 gap-3">
            <p class="text-xs text-foco-muted">{{ $transactions->total() }} lançamento(s)</p>
            <a href="{{ route('history.export', request()->query()) }}"
               class="text-xs font-semibold text-foco-muted hover:text-foco-accent flex items-center gap-1.5 transition-colors ml-auto"
               title="Baixa os lançamentos filtrados em CSV">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Exportar CSV
            </a>
            <button type="button"
                    @click="selecionando = !selecionando; if (!selecionando) marcados = []"
                    :class="selecionando ? 'text-foco-accent' : 'text-foco-muted hover:text-foco-text'"
                    class="text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="list-checks" class="w-3.5 h-3.5"></i>
                <span x-text="selecionando ? 'Cancelar seleção' : 'Selecionar vários'"></span>
            </button>
        </div>

        {{-- Form da exclusão em lote (checkboxes referenciam via form=) --}}
        <form id="bulkForm" method="POST" action="{{ route('transactions.bulkDestroy') }}"
              onsubmit="return confirm('Excluir os lançamentos selecionados? Essa ação não pode ser desfeita.')">
            @csrf @method('DELETE')
        </form>

        <div class="card rounded-2xl overflow-hidden">
            <ul class="divide-y divide-foco-border">
                @foreach($transactions as $t)
                <li class="flex items-center justify-between px-5 py-4 hover:bg-foco-border/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="ids[]" value="{{ $t->id }}" form="bulkForm"
                               x-show="selecionando" x-cloak style="display:none"
                               x-model="marcados"
                               class="w-5 h-5 rounded-md shrink-0" style="accent-color:#DC2626">
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
                                · <span title="{{ $t->data->format('d/m/Y') }}">{{ DateHelper::formatarDataRelativa($t->data) }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-lg {{ $t->tipo === 'entrada' ? 'text-foco-entrada' : 'text-foco-saida' }}">
                            {{ $t->tipo === 'entrada' ? '+' : '-' }}R$ {{ number_format($t->valor, 2, ',', '.') }}
                        </span>
                        <form action="{{ route('transactions.repeat', $t) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-foco-muted hover:text-foco-entrada transition-colors p-1"
                                    title="Repetir com a data de hoje">
                                <i data-lucide="copy-plus" class="w-4 h-4"></i>
                            </button>
                        </form>
                        <a href="{{ route('transactions.edit', $t) }}"
                           class="text-foco-muted hover:text-foco-accent transition-colors p-1">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('transactions.destroy', $t) }}" method="POST"
                              onsubmit="return confirm('Excluir este lançamento?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Barra fixa de ação da seleção --}}
        <div x-show="selecionando && marcados.length > 0" x-cloak x-transition style="display:none"
             class="fixed bottom-5 left-1/2 -translate-x-1/2 z-40 bg-white rounded-2xl px-5 py-3 flex items-center gap-4"
             style="box-shadow:0 8px 30px rgba(30,27,75,.18), 0 0 0 1px rgba(99,102,241,.1)">
            <span class="text-sm font-semibold text-foco-text">
                <span x-text="marcados.length"></span> selecionado(s)
            </span>
            <button type="submit" form="bulkForm"
                    class="bg-foco-saida hover:bg-foco-saida/85 text-white text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                Excluir selecionados
            </button>
        </div>
    </div>

    {{-- Paginação --}}
    @if($transactions->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $transactions->links() }}
    </div>
    @endif
@endif
@endsection

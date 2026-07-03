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
        valor: '{{ old('valor') }}',
        continuar: false,
        mostrarPausa: false,
        countdown: 10,
        countdownTimer: null,
        limiteImpulso: {{ $limiteImpulso }},
        valorHora: {{ $valorHora }},

        get custoEmHoras() {
            const v = parseFloat(this.valor);
            if (this.valorHora <= 0 || !v || v <= 0) return null;
            return Math.round((v / this.valorHora) * 10) / 10;
        },

        tentarEnviar(e) {
            // guarda qual botão enviou (submit() nativo do modal não repassa o botão)
            this.continuar = e.submitter && e.submitter.name === 'continuar';
            if (this.tipo === 'saida' && parseFloat(this.valor) > this.limiteImpulso) {
                e.preventDefault();
                this.mostrarPausa = true;
                this.countdown = 10;
                this.countdownTimer = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) clearInterval(this.countdownTimer);
                }, 1000);
            }
        },

        fecharPausa() {
            clearInterval(this.countdownTimer);
            this.mostrarPausa = false;
        },

        confirmarEnvio() {
            clearInterval(this.countdownTimer);
            this.mostrarPausa = false;
            if (this.continuar) {
                const h = document.createElement('input');
                h.type = 'hidden'; h.name = 'continuar'; h.value = '1';
                this.$refs.formLancamento.appendChild(h);
            }
            this.$refs.formLancamento.submit();
        }
    }">

        {{-- Modal anti-impulso com countdown --}}
        <div x-show="mostrarPausa" x-cloak style="display:none"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div class="card rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
                {{-- Countdown ring --}}
                <div class="relative w-16 h-16 mx-auto mb-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center"
                         style="background: rgba(217,119,6,.12)">
                        <span x-text="countdown" class="text-2xl font-bold" style="color:#D97706"></span>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2">Isso é necessário agora?</h3>
                <p class="text-foco-muted text-sm mb-6">
                    Gasto de R$ <span x-text="parseFloat(valor).toFixed(2).replace('.',',')"></span>.
                    Só um respiro antes de lançar. 🧘
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="fecharPausa()"
                            class="py-3 rounded-xl border border-foco-border text-foco-muted hover:text-foco-text hover:border-foco-text transition-colors font-semibold">
                        Vou esperar
                    </button>
                    <button @click="confirmarEnvio()" :disabled="countdown > 0"
                            :class="countdown > 0 ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-80'"
                            class="py-3 rounded-xl bg-foco-accent text-white font-semibold transition-all">
                        <span x-text="countdown > 0 ? 'Aguarde ' + countdown + 's…' : 'Sim, lançar'"></span>
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
                       class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-2xl font-bold text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
                       autofocus>
                {{-- Custo em horas --}}
                <p x-show="tipo === 'saida' && custoEmHoras !== null" x-cloak style="display:none"
                   class="text-foco-muted text-sm mt-1 flex items-center gap-1">
                    <i data-lucide="clock" class="w-3 h-3"></i>
                    ≈ <span x-text="custoEmHoras"></span>h de trabalho
                </p>
            </div>

            {{-- Descrição (com sugestões do histórico) --}}
            <div>
                <label for="descricao" class="block text-sm font-medium mb-2 text-foco-muted">Descrição</label>
                <input type="text" id="descricao" name="descricao" maxlength="60" list="sugestoes-descricao"
                       value="{{ old('descricao') }}" placeholder="Ex: Mercado, Salário..."
                       class="w-full border border-foco-border rounded-xl px-4 py-3 bg-white text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                @if($sugestoes->isNotEmpty())
                <datalist id="sugestoes-descricao">
                    @foreach($sugestoes as $s)
                    <option value="{{ $s }}">
                    @endforeach
                </datalist>
                @endif
            </div>

            {{-- Categoria: chips sempre visíveis — 1 clique, nada escondido (memória zero) --}}
            <div x-data="{ catId: '{{ old('categoria_id') }}' }">
                <label class="block text-sm font-medium mb-2 text-foco-muted">Categoria <span class="opacity-70">(opcional)</span></label>
                <input type="hidden" name="categoria_id" :value="catId">
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="catId = ''"
                            :class="catId === '' ? 'border-foco-text bg-foco-surface text-foco-text font-semibold' : 'border-foco-border text-foco-muted hover:border-foco-text/40'"
                            class="border-2 rounded-full px-4 py-2 text-sm flex items-center gap-1.5 transition-colors">
                        Sem categoria
                    </button>
                    @foreach($categorias as $cat)
                    <button type="button" @click="catId = '{{ $cat->id }}'"
                            :class="catId == '{{ $cat->id }}' ? 'font-semibold' : ''"
                            :style="catId == '{{ $cat->id }}'
                                ? 'border-color:{{ $cat->cor }}; background:{{ $cat->cor }}18; color:{{ $cat->cor }}'
                                : 'border-color:#E4E4F0; color:#9794B8'"
                            class="border-2 rounded-full px-4 py-2 text-sm flex items-center gap-1.5 transition-colors">
                        <i data-lucide="{{ $cat->icone }}" class="w-3.5 h-3.5 shrink-0" style="color:{{ $cat->cor }}"></i>
                        {{ $cat->nome }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Data: chips rápidos + campo pra outras datas --}}
            <div x-data="{ data: '{{ old('data', date('Y-m-d')) }}', hoje: '{{ date('Y-m-d') }}', ontem: '{{ date('Y-m-d', strtotime('-1 day')) }}' }">
                <label for="data" class="block text-sm font-medium mb-2 text-foco-muted">Data</label>
                <div class="flex gap-2">
                    <button type="button" @click="data = hoje"
                            :class="data === hoje ? 'border-foco-accent bg-foco-accent/10 text-foco-accent font-semibold' : 'border-foco-border text-foco-muted'"
                            class="border-2 rounded-xl px-4 py-3 text-sm transition-colors">
                        Hoje
                    </button>
                    <button type="button" @click="data = ontem"
                            :class="data === ontem ? 'border-foco-accent bg-foco-accent/10 text-foco-accent font-semibold' : 'border-foco-border text-foco-muted'"
                            class="border-2 rounded-xl px-4 py-3 text-sm transition-colors">
                        Ontem
                    </button>
                    <input type="date" id="data" name="data" x-model="data"
                           class="flex-1 border border-foco-border rounded-xl px-4 py-3 bg-white text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
                </div>
            </div>

            <button type="submit"
                    class="btn-primary w-full bg-foco-accent hover:bg-foco-accent/80 text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors shadow-lg">
                <i data-lucide="save" class="w-6 h-6"></i>
                Salvar lançamento
            </button>

            {{-- Registrar vários em sequência sem sair do formulário --}}
            <button type="submit" name="continuar" value="1"
                    class="w-full py-3 rounded-2xl border-2 border-foco-accent/40 text-foco-accent hover:bg-foco-accent/5 transition-colors flex items-center justify-center gap-2 text-sm font-semibold">
                <i data-lucide="list-plus" class="w-4 h-4"></i>
                Salvar e lançar outro
            </button>
        </form>
    </div>
</div>
@endsection

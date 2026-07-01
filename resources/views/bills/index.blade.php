@extends('layouts.app')
@section('title', 'Contas')

@section('content')
@php use App\Helpers\DateHelper; @endphp

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="receipt" class="w-6 h-6 text-foco-accent"></i>
        Contas a Pagar / Receber
    </h1>
    <a href="{{ route('bills.create') }}"
       class="flex items-center gap-2 bg-foco-accent hover:bg-foco-accent/80 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nova conta
    </a>
</div>

<div x-data="{ aba: 'pagar' }">
    {{-- Abas --}}
    <div class="flex gap-2 mb-5">
        <button @click="aba = 'pagar'"
                :class="aba === 'pagar' ? 'bg-foco-saida text-white' : 'border border-foco-border text-foco-muted hover:border-foco-saida hover:text-foco-saida'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
            <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
            A Pagar ({{ $bills->where('tipo','pagar')->whereIn('status',['pendente','atrasado'])->count() }})
        </button>
        <button @click="aba = 'receber'"
                :class="aba === 'receber' ? 'bg-foco-entrada text-white' : 'border border-foco-border text-foco-muted hover:border-foco-entrada hover:text-foco-entrada'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
            <i data-lucide="arrow-down-circle" class="w-4 h-4"></i>
            A Receber ({{ $bills->where('tipo','receber')->whereIn('status',['pendente','atrasado'])->count() }})
        </button>
        <button @click="aba = 'pagas'"
                :class="aba === 'pagas' ? 'bg-foco-muted text-white' : 'border border-foco-border text-foco-muted hover:border-foco-muted'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Pagas
        </button>
    </div>

    @foreach([
        'pagar'   => $bills->whereIn('tipo', ['pagar'])->sortBy([['status','desc'],['vencimento','asc']]),
        'receber' => $bills->where('tipo', 'receber')->sortBy([['status','desc'],['vencimento','asc']]),
        'pagas'   => $bills->whereIn('status', ['pago','recebido'])->sortByDesc('pago_em'),
    ] as $tipoAba => $lista)
    <div x-show="aba === '{{ $tipoAba }}'" x-cloak style="display:none">
        @if($lista->isEmpty())
        <div class="text-center py-16 bg-foco-surface border border-foco-border rounded-2xl">
            <i data-lucide="check-circle-2" class="w-12 h-12 mx-auto mb-3 text-foco-entrada opacity-50"></i>
            <p class="text-foco-muted font-medium">Nenhuma conta cadastrada. Que tranquilidade! 🎯</p>
            <a href="{{ route('bills.create') }}"
               class="mt-4 inline-flex items-center gap-2 bg-foco-accent text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-foco-accent/80 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Adicionar conta
            </a>
        </div>
        @else
        <div class="card rounded-2xl overflow-hidden">
            <ul class="divide-y divide-foco-border">
                @foreach($lista as $bill)
                @php
                    $semaforo = DateHelper::semaforo($bill->vencimento);
                    $semaforoEmoji = match($semaforo) { 'red'=>'🔴', 'yellow'=>'🟡', default=>'🟢' };
                    $isPago = in_array($bill->status, ['pago','recebido']);
                @endphp
                <li class="flex items-center justify-between px-5 py-4 {{ $isPago ? 'opacity-60' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-lg leading-none shrink-0">{{ $semaforoEmoji }}</span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-sm">{{ $bill->descricao }}</p>
                                @if($bill->isParcelado())
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#EEF2FF;color:#6366F1">{{ $bill->parcela_atual }}/{{ $bill->parcelas_total }}</span>
                                    @if($bill->parcelasRestantes() > 0)<span class="text-xs text-foco-muted">(faltam {{ $bill->parcelasRestantes() }})</span>@endif
                                @elseif($bill->recorrente)
                                    <span class="text-xs bg-foco-accent/20 text-foco-accent px-2 py-0.5 rounded-full">
                                        {{ $bill->recorrencia }}
                                    </span>
                                @endif
                                @if($bill->categoria)
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                          style="background-color:{{ $bill->categoria->cor }}22; color:{{ $bill->categoria->cor }}">
                                        {{ $bill->categoria->nome }}
                                    </span>
                                @endif
                            </div>
                            @if($isPago)
                                <p class="text-xs text-foco-muted">
                                    Quitada em {{ $bill->pago_em?->format('d/m/Y') }}
                                </p>
                            @else
                                <p class="text-xs {{ $semaforo === 'red' ? 'text-foco-saida' : ($semaforo === 'yellow' ? 'text-foco-alerta' : 'text-foco-muted') }} font-semibold">
                                    Vence {{ DateHelper::formatarDataRelativa($bill->vencimento) }}
                                </p>
                                <p class="text-xs text-foco-muted">{{ $bill->vencimento->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 ml-4 shrink-0">
                        <span class="font-bold {{ $bill->tipo === 'pagar' ? 'text-foco-saida' : 'text-foco-entrada' }}">
                            R$ {{ number_format($bill->valor, 2, ',', '.') }}
                        </span>

                        @if(!$isPago)
                        <form action="{{ route('bills.marcarPago', $bill) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors
                                           {{ $bill->tipo === 'pagar' ? 'bg-foco-saida/20 text-foco-saida hover:bg-foco-saida/30' : 'bg-foco-entrada/20 text-foco-entrada hover:bg-foco-entrada/30' }}">
                                {{ $bill->tipo === 'pagar' ? 'Marcar como pago' : 'Marcar como recebido' }}
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('bills.destroy', $bill) }}" method="POST"
                              onsubmit="return confirm('Excluir esta conta?')">
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
        @endif
    </div>
    @endforeach

</div>
@endsection

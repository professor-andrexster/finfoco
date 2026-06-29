@extends('layouts.app')
@section('title', 'Alertas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="bell" class="w-6 h-6 text-foco-accent"></i>
        Alertas
    </h1>
    <a href="{{ route('alerts.create') }}"
       class="flex items-center gap-2 bg-foco-accent hover:bg-foco-accent/80 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Novo alerta
    </a>
</div>

<p class="text-foco-muted text-sm mb-6">
    Defina limites de gasto por categoria. Você verá um aviso no dashboard quando atingir o limite.
</p>

@if($alertas->isEmpty())
    <div class="text-center py-16 text-foco-muted">
        <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p>Nenhum alerta configurado.</p>
    </div>
@else
    <div class="grid gap-3">
        @foreach($alertas as $alerta)
        <div class="bg-foco-surface border {{ $alerta->ativo ? 'border-foco-border' : 'border-foco-border/40 opacity-60' }} rounded-xl flex items-center justify-between px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color: {{ $alerta->categoria->cor }}22;">
                    <i data-lucide="{{ $alerta->categoria->icone }}"
                       class="w-5 h-5" style="color: {{ $alerta->categoria->cor }};"></i>
                </div>
                <div>
                    <p class="font-semibold">{{ $alerta->categoria->nome }}</p>
                    <p class="text-foco-muted text-xs">
                        Limite: R$ {{ number_format($alerta->limite_valor, 2, ',', '.') }}
                        / {{ match($alerta->periodo) { 'dia' => 'dia', 'semana' => 'semana', default => 'mês' } }}
                        @if(!$alerta->ativo) · <span class="text-foco-alerta">pausado</span> @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form action="{{ route('alerts.toggle', $alerta) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="text-foco-muted hover:text-foco-alerta transition-colors p-2"
                            title="{{ $alerta->ativo ? 'Pausar' : 'Ativar' }}">
                        <i data-lucide="{{ $alerta->ativo ? 'bell' : 'bell-off' }}" class="w-4 h-4"></i>
                    </button>
                </form>
                <form action="{{ route('alerts.destroy', $alerta) }}" method="POST"
                      onsubmit="return confirm('Excluir este alerta?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-foco-muted hover:text-foco-saida transition-colors p-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection

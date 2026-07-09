@extends('layouts.app')
@section('title', 'Vendas (admin)')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="shield-check" class="w-6 h-6 text-foco-accent"></i>
        Painel de vendas
    </h1>

    {{-- Bloco 1 + 2: cards de resumo --}}
    <div class="grid sm:grid-cols-2 gap-5 mb-8">
        <div class="card rounded-2xl p-6">
            <p class="text-sm font-medium text-foco-muted flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-foco-entrada"></i>
                Assinantes ativos
            </p>
            <p class="text-4xl font-bold text-foco-text mt-2">{{ $totalAssinantesAtivos }}</p>
        </div>

        <div class="card rounded-2xl p-6">
            <p class="text-sm font-medium text-foco-muted flex items-center gap-2">
                <i data-lucide="hourglass" class="w-4 h-4 text-foco-alerta"></i>
                Em trial ativo
            </p>
            <p class="text-4xl font-bold text-foco-text mt-2">{{ $totalEmTrial }}</p>
        </div>
    </div>

    {{-- Bloco 3: tabela de assinaturas recentes --}}
    <div class="card rounded-2xl p-6">
        <h2 class="font-semibold text-foco-text flex items-center gap-2 mb-4">
            <i data-lucide="receipt" class="w-4 h-4 text-foco-accent"></i>
            Últimas assinaturas
        </h2>

        @if($ultimasAssinaturas->isEmpty())
            <p class="text-foco-muted text-sm py-6 text-center">Nenhuma assinatura ainda</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-foco-muted border-b border-foco-border">
                            <th class="py-2 pr-4 font-medium">Nome</th>
                            <th class="py-2 pr-4 font-medium">E-mail</th>
                            <th class="py-2 pr-4 font-medium">Data</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimasAssinaturas as $assinatura)
                        <tr class="border-b border-foco-border last:border-0">
                            <td class="py-3 pr-4 text-foco-text">{{ $assinatura->user->name ?? '—' }}</td>
                            <td class="py-3 pr-4 text-foco-text">{{ $assinatura->user->email ?? '—' }}</td>
                            <td class="py-3 pr-4 text-foco-muted">{{ $assinatura->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-4">
                                <span class="text-xs font-semibold px-2 py-1 rounded-lg
                                    {{ $assinatura->stripe_status === 'active' ? 'text-foco-entrada bg-green-50' : 'text-foco-muted bg-foco-surface' }}">
                                    {{ $assinatura->stripe_status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

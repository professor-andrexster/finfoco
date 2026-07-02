@extends('layouts.app')
@section('title', 'Assinatura')
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2 text-foco-text">
        <i data-lucide="credit-card" class="w-6 h-6 text-foco-accent"></i>
        Assinatura
    </h1>

    @if($vitalicio)
        {{-- Estado: acesso vitalício --}}
        <div class="card rounded-2xl p-6 space-y-4" style="background:#EEF2FF;box-shadow:0 1px 4px rgba(99,102,241,.08),0 0 0 1px rgba(99,102,241,.15)">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 bg-foco-accent">
                    <i data-lucide="crown" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-foco-text">Acesso vitalício ativado</p>
                    <p class="text-sm text-foco-muted mt-1">
                        Você tem acesso permanente ao FinFoco, sem mensalidade.
                    </p>
                </div>
            </div>
        </div>

    @elseif($assinante)
        {{-- Estado: assinante ativo --}}
        <div class="card rounded-2xl p-6 space-y-4" style="background:#F0FDF4;box-shadow:0 1px 4px rgba(22,163,74,.08),0 0 0 1px rgba(22,163,74,.15)">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" style="background:#16A34A">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-foco-text">Assinatura ativa</p>
                    <p class="text-sm text-foco-muted mt-1">
                        Você tem acesso completo ao FinFoco. Obrigado por assinar!
                    </p>
                </div>
            </div>

            <form action="{{ route('billing.portal') }}" method="POST">
                @csrf
                <button type="submit"
                        class="btn-primary w-full text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors"
                        style="background:#16A34A">
                    <i data-lucide="settings-2" class="w-5 h-5"></i>
                    Gerenciar assinatura
                </button>
            </form>
        </div>

    @elseif($emTrial && $diasRestantes > 3)
        {{-- Estado: trial tranquilo --}}
        <div class="card rounded-2xl p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 bg-foco-surface">
                    <i data-lucide="clock" class="w-6 h-6 text-foco-accent"></i>
                </div>
                <div>
                    <p class="font-semibold text-foco-text">Você está no período gratuito</p>
                    <p class="text-sm text-foco-muted mt-1">
                        Faltam {{ $diasRestantes }} dias para o fim do seu teste grátis.
                    </p>
                </div>
            </div>

            <form action="{{ route('billing.checkout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full border-2 border-foco-accent text-foco-accent font-bold py-4 rounded-2xl flex items-center justify-center gap-3 hover:bg-indigo-50 transition-colors">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    Assinar agora
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-foco-border">
                <form action="{{ route('billing.redeem') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="codigo" placeholder="Tem um código de acesso?"
                           class="flex-1 border border-foco-border rounded-xl px-3 py-2 text-sm text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                    <button type="submit"
                            class="text-sm font-semibold px-4 py-2 rounded-xl text-foco-accent border border-foco-accent hover:bg-indigo-50 transition-colors whitespace-nowrap">
                        Resgatar
                    </button>
                </form>
            </div>
        </div>

    @elseif($emTrial)
        {{-- Estado: trial acabando (<= 3 dias) --}}
        <div class="card rounded-2xl p-6 space-y-4 border-2" style="background:#FFFBEB;border-color:#D97706">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" style="background:#D97706">
                    <i data-lucide="alarm-clock" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-foco-text">Seu período gratuito está acabando</p>
                    <p class="text-sm text-foco-muted mt-1">
                        Faltam {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'dia' : 'dias' }} para o fim do seu teste grátis. Assine agora para não perder o acesso.
                    </p>
                </div>
            </div>

            <form action="{{ route('billing.checkout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="btn-primary w-full text-white py-4 rounded-2xl flex items-center justify-center gap-3 transition-colors"
                        style="background:#D97706">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    Assinar agora
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-foco-border">
                <form action="{{ route('billing.redeem') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="codigo" placeholder="Tem um código de acesso?"
                           class="flex-1 border border-foco-border rounded-xl px-3 py-2 text-sm text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                    <button type="submit"
                            class="text-sm font-semibold px-4 py-2 rounded-xl text-foco-accent border border-foco-accent hover:bg-indigo-50 transition-colors whitespace-nowrap">
                        Resgatar
                    </button>
                </form>
            </div>
        </div>

    @else
        {{-- Estado: trial expirado / bloqueado --}}
        <div class="card rounded-2xl p-6 space-y-4 border-2" style="background:#FEF2F2;border-color:#DC2626">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" style="background:#DC2626">
                    <i data-lucide="lock" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-foco-text">Seu período gratuito acabou</p>
                    <p class="text-sm text-foco-muted mt-1">
                        Assine o FinFoco para continuar controlando suas finanças sem perder seus dados.
                    </p>
                </div>
            </div>

            <form action="{{ route('billing.checkout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="btn-primary w-full text-white py-4 rounded-2xl flex items-center justify-center gap-3"
                        style="background:#6366F1;box-shadow:0 4px 14px rgba(99,102,241,.3)">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    Assinar agora — R$ 19,98/mês
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-foco-border">
                <form action="{{ route('billing.redeem') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="codigo" placeholder="Tem um código de acesso?"
                           class="flex-1 border border-foco-border rounded-xl px-3 py-2 text-sm text-foco-text focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-foco-accent">
                    <button type="submit"
                            class="text-sm font-semibold px-4 py-2 rounded-xl text-foco-accent border border-foco-accent hover:bg-indigo-50 transition-colors whitespace-nowrap">
                        Resgatar
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@push('scripts')
<script>setTimeout(() => lucide.createIcons(), 100);</script>
@endpush
@endsection

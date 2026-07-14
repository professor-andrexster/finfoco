<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinFoco — @yield('title', 'Painel')</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon.svg">
    <meta name="theme-color" content="#6366F1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="FinFoco">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..700;1,14..32,300..700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'foco-bg':      '#FFFFFF',
                        'foco-surface': '#F7F7FD',
                        'foco-border':  '#E4E4F0',
                        'foco-entrada': '#16A34A',
                        'foco-saida':   '#DC2626',
                        'foco-alerta':  '#D97706',
                        'foco-text':    '#1E1B4B',
                        'foco-muted':   '#9794B8',
                        'foco-accent':  '#6366F1',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                },
            },
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        body { font-size: 16px; background-color: #FFFFFF; color: #1E1B4B; }
        .btn-primary { font-size: 18px; font-weight: 700; }
        [x-cloak] { display: none !important; }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(99,102,241,.08), 0 0 0 1px rgba(99,102,241,.06);
        }
        .card-hover:hover {
            box-shadow: 0 4px 16px rgba(99,102,241,.12), 0 0 0 1px rgba(99,102,241,.1);
        }

        /* Input padrão */
        input[type="text"], input[type="number"], input[type="date"],
        input[type="time"], input[type="url"],
        input[type="email"], textarea, select {
            background: #fff !important;
            border-color: #E4E4F0 !important;
            color: #1E1B4B !important;
        }
        input::placeholder, textarea::placeholder { color: #9794B8; }

        /* Item ativo da sidebar (desktop) */
        .side-active {
            background: #EEF2FF;
            color: #6366F1 !important;
            font-weight: 700;
        }

        /* Barra inferior (mobile): respeita o recorte do iPhone */
        .tabbar { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-foco-bg text-foco-text font-sans min-h-screen">

    {{-- Toast sucesso --}}
    @if(session('sucesso'))
    <div x-data="{ show: true }" x-show="show" x-transition x-cloak
         x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-5 right-5 z-[70] bg-foco-entrada text-white px-5 py-3 rounded-2xl shadow-lg flex items-center gap-2 text-sm font-semibold">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
        {{ session('sucesso') }}
    </div>
    @endif

    {{-- Erros de validação --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="fixed top-5 right-5 z-[70] bg-foco-saida text-white px-5 py-3 rounded-2xl shadow-lg max-w-sm">
        <div class="flex items-start gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <ul class="text-sm space-y-0.5 flex-1">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
            <button @click="show = false" class="opacity-60 hover:opacity-100 shrink-0 ml-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @endif

    @php
        $navDia = [
            ['route'=>'dashboard',     'pat'=>'dashboard',  'icon'=>'layout-dashboard','label'=>'Painel'],
            ['route'=>'agenda.index',  'pat'=>'agenda*',    'icon'=>'calendar-days',   'label'=>'Agenda'],
            ['route'=>'foco.index',    'pat'=>'foco*',      'icon'=>'zap',             'label'=>'Hiperfoco'],
            ['route'=>'routines.index','pat'=>'routines*',  'icon'=>'repeat',          'label'=>'Rotinas'],
        ];
        $navDinheiro = [
            ['route'=>'transactions.create','pat'=>'transactions*','icon'=>'plus-circle', 'label'=>'Lançar'],
            ['route'=>'bills.index',        'pat'=>'bills*',       'icon'=>'receipt',     'label'=>'Contas'],
            ['route'=>'history.index',      'pat'=>'history*',     'icon'=>'clock',       'label'=>'Histórico'],
            ['route'=>'reports.index',      'pat'=>'reports*',     'icon'=>'bar-chart-3', 'label'=>'Relatórios'],
            ['route'=>'categories.index',   'pat'=>'categories*',  'icon'=>'tag',         'label'=>'Categorias'],
            ['route'=>'alerts.index',       'pat'=>'alerts*',      'icon'=>'bell',        'label'=>'Alertas'],
        ];
    @endphp

    {{-- ═══ SIDEBAR (desktop) ═══════════════════════════════════════════════ --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 flex-col bg-white z-40"
           style="border-right:1px solid #E4E4F0">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-6 h-16 shrink-0"
           style="border-bottom:1px solid #F3F3FB">
            <svg width="28" height="28" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="26" fill="none" stroke="#E0DFFA" stroke-width="5"/>
                <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                <circle cx="32" cy="32" r="7" fill="#22C55E"/>
            </svg>
            <span class="font-bold text-lg tracking-tight">
                <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
            <div>
                <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-foco-muted">Meu dia</p>
                <div class="space-y-0.5">
                    @foreach($navDia as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                              {{ request()->routeIs($item['pat']) ? 'side-active' : 'text-foco-text hover:bg-foco-surface' }}">
                        <i data-lucide="{{ $item['icon'] }}" style="width:18px;height:18px" class="shrink-0"></i>
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-foco-muted">Dinheiro</p>
                <div class="space-y-0.5">
                    @foreach($navDinheiro as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                              {{ request()->routeIs($item['pat']) ? 'side-active' : 'text-foco-text hover:bg-foco-surface' }}">
                        <i data-lucide="{{ $item['icon'] }}" style="width:18px;height:18px" class="shrink-0"></i>
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>
        </nav>

        {{-- Usuário (desktop) --}}
        <div class="px-3 py-4 space-y-0.5" style="border-top:1px solid #F3F3FB">
            <a href="{{ route('settings.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('settings*') ? 'side-active' : 'text-foco-text hover:bg-foco-surface' }}">
                <i data-lucide="settings-2" style="width:18px;height:18px" class="shrink-0"></i>
                Configurações
            </a>
            <a href="{{ route('billing.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-foco-text hover:bg-foco-surface transition-colors">
                <i data-lucide="credit-card" style="width:18px;height:18px" class="shrink-0"></i>
                Assinatura
                @if(auth()->user()->onTrial())
                    <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded" style="color:#D97706;background:#D9770618">TRIAL</span>
                @endif
            </a>
            <div class="flex items-center gap-3 px-3 pt-3">
                <span class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                      style="background:#6366F1">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-foco-muted truncate">{{ auth()->user()->email }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-foco-muted hover:text-foco-saida transition-colors" title="Sair da conta">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══ TOPO (mobile) ═══════════════════════════════════════════════════ --}}
    <header class="lg:hidden bg-white sticky top-0 z-40" style="border-bottom:1px solid #E4E4F0">
        <div class="px-4 h-14 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <svg width="26" height="26" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="26" fill="none" stroke="#E0DFFA" stroke-width="5"/>
                    <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                    <circle cx="32" cy="32" r="7" fill="#22C55E"/>
                </svg>
                <span class="font-bold text-base tracking-tight">
                    <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
                </span>
            </a>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold"
                        style="background:#6366F1">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 top-11 w-52 bg-white rounded-xl py-1 z-50"
                     style="box-shadow:0 8px 24px rgba(99,102,241,.15),0 0 0 1px rgba(99,102,241,.08)">
                    <div class="px-4 py-2.5" style="border-bottom:1px solid #E4E4F0">
                        <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-foco-muted truncate mt-0.5">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('billing.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-foco-surface transition-colors">
                        <i data-lucide="credit-card" class="w-4 h-4 text-foco-muted"></i>
                        Assinatura
                        @if(auth()->user()->onTrial())
                            <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded" style="color:#D97706;background:#D9770618">TRIAL</span>
                        @endif
                    </a>
                    <a href="{{ route('settings.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-foco-surface transition-colors">
                        <i data-lucide="settings-2" class="w-4 h-4 text-foco-muted"></i> Configurações
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-foco-saida hover:bg-red-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Sair da conta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ═══ CONTEÚDO ════════════════════════════════════════════════════════ --}}
    <main class="lg:pl-64">
        <div class="max-w-6xl mx-auto px-4 lg:px-10 py-6 lg:py-10 pb-28 lg:pb-12">
            @yield('content')
        </div>
    </main>

    {{-- ═══ BARRA INFERIOR (mobile) ═════════════════════════════════════════ --}}
    <div x-data="{ mais: false }">
        {{-- Sheet "Mais" --}}
        <div x-show="mais" x-cloak @click="mais = false"
             class="lg:hidden fixed inset-0 bg-black/30 z-50" x-transition.opacity></div>
        <div x-show="mais" x-cloak
             x-transition:enter="transition transform ease-out duration-200"
             x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition transform ease-in duration-150"
             x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="lg:hidden fixed bottom-0 inset-x-0 bg-white rounded-t-3xl z-50 p-5 tabbar"
             style="box-shadow:0 -8px 30px rgba(30,27,75,.12)">
            <div class="w-10 h-1 rounded-full bg-foco-border mx-auto mb-4"></div>
            <div class="grid grid-cols-3 gap-3">
                @foreach([
                    ['route'=>'routines.index',   'icon'=>'repeat',      'label'=>'Rotinas'],
                    ['route'=>'bills.index',      'icon'=>'receipt',     'label'=>'Contas'],
                    ['route'=>'history.index',    'icon'=>'clock',       'label'=>'Histórico'],
                    ['route'=>'reports.index',    'icon'=>'bar-chart-3', 'label'=>'Relatórios'],
                    ['route'=>'categories.index', 'icon'=>'tag',         'label'=>'Categorias'],
                    ['route'=>'alerts.index',     'icon'=>'bell',        'label'=>'Alertas'],
                ] as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex flex-col items-center gap-1.5 py-4 rounded-2xl bg-foco-surface text-foco-text font-semibold text-xs">
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 text-foco-accent"></i>
                    {{ $item['label'] }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- Barra --}}
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white z-40 tabbar"
             style="border-top:1px solid #E4E4F0" aria-label="Navegação principal">
            <div class="grid grid-cols-5 h-16">
                @php
                    $tabs = [
                        ['route'=>'dashboard',    'pat'=>'dashboard', 'icon'=>'layout-dashboard','label'=>'Painel'],
                        ['route'=>'agenda.index', 'pat'=>'agenda*',   'icon'=>'calendar-days',   'label'=>'Agenda'],
                    ];
                @endphp
                @foreach($tabs as $t)
                <a href="{{ route($t['route']) }}"
                   class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs($t['pat']) ? 'text-foco-accent' : 'text-foco-muted' }}">
                    <i data-lucide="{{ $t['icon'] }}" style="width:22px;height:22px"></i>
                    <span class="text-[10px] font-semibold">{{ $t['label'] }}</span>
                </a>
                @endforeach

                {{-- Lançar: o botão central, sempre à mão --}}
                <a href="{{ route('transactions.create') }}" class="flex items-center justify-center" title="Lançar entrada ou saída">
                    <span class="w-14 h-14 -mt-7 rounded-full flex items-center justify-center text-white"
                          style="background:#6366F1; box-shadow:0 6px 18px rgba(99,102,241,.4)">
                        <i data-lucide="plus" style="width:26px;height:26px"></i>
                    </span>
                </a>

                <a href="{{ route('foco.index') }}"
                   class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('foco*') ? 'text-foco-accent' : 'text-foco-muted' }}">
                    <i data-lucide="zap" style="width:22px;height:22px"></i>
                    <span class="text-[10px] font-semibold">Foco</span>
                </a>

                <button @click="mais = true"
                        class="flex flex-col items-center justify-center gap-0.5 text-foco-muted">
                    <i data-lucide="layout-grid" style="width:22px;height:22px"></i>
                    <span class="text-[10px] font-semibold">Mais</span>
                </button>
            </div>
        </nav>
    </div>

    <script>lucide.createIcons();</script>

    @auth
    @if(config('services.webpush.public_key'))
    {{-- Web Push: registra o service worker e assina quando a permissão existir --}}
    <script>
        window.finfocoAssinarPush = async function () {
            try {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
                if (Notification.permission !== 'granted') return;

                const reg = await navigator.serviceWorker.register('/sw.js');
                let sub = await reg.pushManager.getSubscription();

                if (!sub) {
                    const b64 = '{{ config('services.webpush.public_key') }}'.replace(/-/g, '+').replace(/_/g, '/');
                    const raw = atob(b64.padEnd(b64.length + (4 - b64.length % 4) % 4, '='));
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: Uint8Array.from(raw, c => c.charCodeAt(0)),
                    });
                }

                await fetch('{{ route('push.assinar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(sub.toJSON ? sub.toJSON() : sub),
                });
            } catch (e) { /* push é extra — nunca pode quebrar a página */ }
        };
        window.finfocoAssinarPush();
    </script>
    @endif
    @endauth

    @stack('scripts')
</body>
</html>

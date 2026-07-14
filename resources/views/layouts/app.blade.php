<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Norte — @yield('title', 'Painel')</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon.svg">
    <meta name="theme-color" content="#6366F1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Norte">

    {{-- Tema: aplica antes do primeiro paint pra não piscar --}}
    <script>
        (function () {
            var t = localStorage.getItem('finfoco_tema');
            if (t === 'escuro' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
        function finfocoAlternarTema() {
            var r = document.documentElement;
            r.classList.toggle('dark');
            localStorage.setItem('finfoco_tema', r.classList.contains('dark') ? 'escuro' : 'claro');
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..700;1,14..32,300..700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'foco-bg':      'var(--c-bg)',
                        'foco-surface': 'var(--c-surface)',
                        'foco-border':  'var(--c-border)',
                        'foco-entrada': 'var(--c-entrada)',
                        'foco-saida':   'var(--c-saida)',
                        'foco-alerta':  'var(--c-alerta)',
                        'foco-text':    'var(--c-text)',
                        'foco-muted':   'var(--c-muted)',
                        'foco-accent':  'var(--c-accent)',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                },
            },
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        /* ── Tokens de tema (claro/escuro) ─────────────────────────────── */
        :root {
            --c-bg:      #FFFFFF;
            --c-card:    #FFFFFF;
            --c-surface: #F7F7FD;
            --c-border:  #E4E4F0;
            --c-border2: #F3F3FB;
            --c-text:    #1E1B4B;
            --c-muted:   #7C78A0;
            --c-accent:  #6366F1;
            --c-entrada: #16A34A;
            --c-saida:   #DC2626;
            --c-alerta:  #D97706;
            --c-sombra:  rgba(99,102,241,.08);
            --c-anel:    rgba(99,102,241,.06);
        }
        .dark {
            --c-bg:      #0F0F13;
            --c-card:    #1A1A22;
            --c-surface: #22222C;
            --c-border:  #2A2A38;
            --c-border2: #232330;
            --c-text:    #F1F5F9;
            --c-muted:   #8E97AB;
            --c-accent:  #818CF8;
            --c-entrada: #22C55E;
            --c-saida:   #EF4444;
            --c-alerta:  #F59E0B;
            --c-sombra:  rgba(0,0,0,.45);
            --c-anel:    rgba(255,255,255,.07);
        }

        :root { color-scheme: light; }
        .dark { color-scheme: dark; }

        /* Transição suave entre páginas (View Transitions API — degrada em silêncio) */
        @view-transition { navigation: auto; }
        ::view-transition-old(root), ::view-transition-new(root) { animation-duration: .18s; }

        body {
            font-size: 16px;
            background-color: var(--c-bg);
            color: var(--c-text);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.011em;
        }

        :focus-visible { outline: 2px solid var(--c-accent); outline-offset: 2px; border-radius: 4px; }
        .btn-primary { font-size: 18px; font-weight: 700; }
        [x-cloak] { display: none !important; }

        .card {
            background: var(--c-card);
            border-radius: 16px;
            box-shadow: 0 1px 4px var(--c-sombra), 0 0 0 1px var(--c-anel);
        }
        .card-hover { transition: box-shadow .15s ease, transform .15s ease; }
        .card-hover:hover {
            box-shadow: 0 4px 16px var(--c-sombra), 0 0 0 1px var(--c-anel);
            transform: translateY(-1px);
        }

        /* Input padrão */
        input[type="text"], input[type="number"], input[type="date"],
        input[type="time"], input[type="url"],
        input[type="email"], textarea, select {
            background: var(--c-card) !important;
            border-color: var(--c-border) !important;
            color: var(--c-text) !important;
        }
        input::placeholder, textarea::placeholder { color: var(--c-muted); }
        .dark input[type="date"], .dark input[type="time"] { color-scheme: dark; }

        /* Item ativo da sidebar (desktop) */
        .side-active {
            background: rgba(99,102,241,.12);
            color: var(--c-accent) !important;
            font-weight: 700;
        }

        /* Barra inferior (mobile): respeita o recorte do iPhone */
        .tabbar { padding-bottom: env(safe-area-inset-bottom); }

        /* ── Modo escuro: cobre cores fixas herdadas das views ─────────── */
        .dark .bg-white { background-color: var(--c-card) !important; }
        .dark [style*="#E4E4F0"] { border-color: var(--c-border) !important; }
        .dark [style*="#F3F3FB"] { border-color: var(--c-border2) !important; }
        .dark [style*="background:#EEF2FF"], .dark [style*="background:#E0E7FF"] { background: rgba(99,102,241,.18) !important; }
        .dark [style*="background:#FEF2F2"], .dark [style*="background: #FEF2F2"] { background: rgba(239,68,68,.14) !important; }
        .dark [style*="background:#FFFBEB"], .dark [style*="background: #FFFBEB"] { background: rgba(245,158,11,.14) !important; }
        .dark [style*="color:#1E1B4B"] { color: var(--c-text) !important; }
        .dark circle[stroke="#E0DFFA"] { stroke: #2A2A38; }
        .dark circle[stroke="#E4E4F0"] { stroke: var(--c-border); }
        .dark .bg-green-50\/50 { background: rgba(34,197,94,.08) !important; }
        .dark .hover\:bg-red-50:hover { background: rgba(239,68,68,.12) !important; }

        /* Tecla de atalho */
        kbd {
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            color: var(--c-muted);
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-bottom-width: 2px;
            border-radius: 5px;
            padding: 1px 5px;
        }
        .so-escuro { display: none; }
        .dark .so-escuro { display: inline-flex; }
        .dark .so-claro { display: none; }

        /* ── Micro-animações (dopamina visual, sem exagero) ────────────── */
        @keyframes surgir { from { opacity: 0; transform: translateY(5px); } }
        .card { animation: surgir .22s ease-out both; }
        button { transition: transform .12s ease, background-color .15s ease, color .15s ease, border-color .15s ease; }
        button:active { transform: scale(.94); }
        @keyframes pop { 40% { transform: scale(1.18); } }
        button[title^="Concluir"]:active, button[title^="Desmarcar"]:active { animation: pop .25s ease; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
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
            ['route'=>'dashboard',     'pat'=>'dashboard',  'icon'=>'layout-dashboard','label'=>'Painel',   'kbd'=>'P'],
            ['route'=>'agenda.index',  'pat'=>'agenda*',    'icon'=>'calendar-days',   'label'=>'Agenda',   'kbd'=>'A'],
            ['route'=>'foco.index',    'pat'=>'foco*',      'icon'=>'zap',             'label'=>'Hiperfoco','kbd'=>'F'],
            ['route'=>'routines.index','pat'=>'routines*',  'icon'=>'repeat',          'label'=>'Rotinas',  'kbd'=>'R'],
            ['route'=>'conquistas.index','pat'=>'conquistas*','icon'=>'award',         'label'=>'Conquistas','kbd'=>'Q'],
        ];
        $navDinheiro = [
            ['route'=>'transactions.create','pat'=>'transactions*','icon'=>'plus-circle', 'label'=>'Lançar',   'kbd'=>'L'],
            ['route'=>'bills.index',        'pat'=>'bills*',       'icon'=>'receipt',     'label'=>'Contas',   'kbd'=>'C'],
            ['route'=>'history.index',      'pat'=>'history*',     'icon'=>'clock',       'label'=>'Histórico','kbd'=>'H'],
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
                <circle cx="32" cy="32" r="26" fill="none" stroke="#6366F1" stroke-width="4.5"/><path d="M32 16 L40 42.5 L32 37.5 L24 42.5 Z" fill="#6366F1"/>
            </svg>
            <span class="font-bold text-lg tracking-tight">
                <span style="color:#1E1B4B">Norte</span>
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
                        @if(!empty($item['kbd']))<kbd class="ml-auto">{{ $item['kbd'] }}</kbd>@endif
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
                        @if(!empty($item['kbd']))<kbd class="ml-auto">{{ $item['kbd'] }}</kbd>@endif
                    </a>
                    @endforeach
                </div>
            </div>
        </nav>

        {{-- Usuário (desktop) --}}
        <div class="px-3 py-4 space-y-0.5" style="border-top:1px solid #F3F3FB">
            <button onclick="finfocoAlternarTema()"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-foco-text hover:bg-foco-surface transition-colors">
                <span class="so-claro inline-flex"><i data-lucide="moon" style="width:18px;height:18px"></i></span>
                <span class="so-escuro"><i data-lucide="sun" style="width:18px;height:18px"></i></span>
                <span class="so-claro">Modo escuro</span>
                <span class="so-escuro">Modo claro</span>
            </button>
            <button onclick="document.getElementById('modal-atalhos').style.display='flex'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-foco-text hover:bg-foco-surface transition-colors">
                <i data-lucide="keyboard" style="width:18px;height:18px" class="shrink-0"></i>
                Atalhos
                <kbd class="ml-auto">?</kbd>
            </button>
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
                    <circle cx="32" cy="32" r="26" fill="none" stroke="#6366F1" stroke-width="4.5"/><path d="M32 16 L40 42.5 L32 37.5 L24 42.5 Z" fill="#6366F1"/>
                </svg>
                <span class="font-bold text-base tracking-tight">
                    <span style="color:#1E1B4B">Norte</span>
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
                    <button onclick="finfocoAlternarTema()" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-foco-surface transition-colors">
                        <span class="so-claro inline-flex"><i data-lucide="moon" class="w-4 h-4 text-foco-muted"></i></span>
                        <span class="so-escuro"><i data-lucide="sun" class="w-4 h-4 text-foco-muted"></i></span>
                        <span class="so-claro">Modo escuro</span>
                        <span class="so-escuro">Modo claro</span>
                    </button>
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
                    ['route'=>'conquistas.index', 'icon'=>'award',       'label'=>'Conquistas'],
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

    {{-- Modal de atalhos de teclado --}}
    <div id="modal-atalhos" style="display:none" class="fixed inset-0 z-[80] items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('modal-atalhos').style.display='none'"></div>
        <div class="card relative w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold flex items-center gap-2">
                    <i data-lucide="keyboard" class="w-5 h-5 text-foco-accent"></i> Atalhos de teclado
                </h2>
                <button onclick="document.getElementById('modal-atalhos').style.display='none'"
                        class="text-foco-muted hover:text-foco-text p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <ul class="space-y-2.5 text-sm">
                @foreach([
                    ['P', 'Painel'], ['A', 'Agenda'], ['S', 'Agenda da semana'],
                    ['F', 'Modo Hiperfoco'], ['R', 'Rotinas'], ['Q', 'Conquistas'], ['L', 'Lançar entrada/saída'],
                    ['C', 'Contas'], ['H', 'Histórico'], ['E', 'Modo escuro/claro'], ['?', 'Esta ajuda'],
                ] as [$tecla, $acao])
                <li class="flex items-center justify-between">
                    <span class="text-foco-muted">{{ $acao }}</span>
                    <kbd>{{ $tecla }}</kbd>
                </li>
                @endforeach
            </ul>
            <p class="text-xs text-foco-muted mt-4">Funcionam em qualquer tela, menos quando você está digitando.</p>
        </div>
    </div>

    <script>lucide.createIcons();</script>

    {{-- Navegação instantânea: pré-renderiza a próxima página no hover (Speculation Rules) --}}
    <script type="speculationrules">
    {
        "prerender": [{
            "where": {
                "and": [
                    { "href_matches": "/*" },
                    { "not": { "href_matches": "/telegram/*" } },
                    { "not": { "href_matches": "/agenda/feed/*" } },
                    { "not": { "href_matches": "/historico/exportar*" } }
                ]
            },
            "eagerness": "moderate"
        }]
    }
    </script>

    {{-- Atalhos de teclado (desktop) --}}
    <script>
        (function () {
            const mapa = {
                p: '{{ route('dashboard') }}',
                a: '{{ route('agenda.index') }}',
                s: '{{ route('agenda.semana') }}',
                f: '{{ route('foco.index') }}',
                r: '{{ route('routines.index') }}',
                q: '{{ route('conquistas.index') }}',
                l: '{{ route('transactions.create') }}',
                c: '{{ route('bills.index') }}',
                h: '{{ route('history.index') }}',
            };
            const modal = document.getElementById('modal-atalhos');

            document.addEventListener('keydown', (e) => {
                if (e.metaKey || e.ctrlKey || e.altKey) return;
                const el = document.activeElement;
                if (el && (['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName) || el.isContentEditable)) return;

                if (e.key === 'Escape') { modal.style.display = 'none'; return; }
                if (e.key === '?') { modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex'; return; }

                const k = e.key.toLowerCase();
                if (k === 'e') { finfocoAlternarTema(); return; }
                if (mapa[k]) window.location.href = mapa[k];
            });
        })();
    </script>

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

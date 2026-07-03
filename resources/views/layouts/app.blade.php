<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinFoco — @yield('title', 'Dashboard')</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">

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

        /* Nav underline ativo */
        .nav-active { color: #6366F1 !important; }
        .nav-active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 8px;
            right: 8px;
            height: 2px;
            background: #6366F1;
            border-radius: 2px 2px 0 0;
        }

        /* Input padrão */
        input[type="text"], input[type="number"], input[type="date"],
        input[type="email"], textarea, select {
            background: #fff !important;
            border-color: #E4E4F0 !important;
            color: #1E1B4B !important;
        }
        input::placeholder, textarea::placeholder { color: #9794B8; }
    </style>
</head>
<body class="bg-foco-bg text-foco-text font-sans min-h-screen">

    {{-- Toast sucesso --}}
    @if(session('sucesso'))
    <div x-data="{ show: true }" x-show="show" x-transition x-cloak
         x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-5 right-5 z-50 bg-foco-entrada text-white px-5 py-3 rounded-2xl shadow-lg flex items-center gap-2 text-sm font-semibold">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
        {{ session('sucesso') }}
    </div>
    @endif

    {{-- Erros de validação --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="fixed top-5 right-5 z-50 bg-foco-saida text-white px-5 py-3 rounded-2xl shadow-lg max-w-sm">
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

    {{-- Navbar --}}
    <nav class="bg-white sticky top-0 z-40" style="border-bottom: 1px solid #E4E4F0;">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-14">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <svg width="26" height="26" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="26" fill="none" stroke="#E0DFFA" stroke-width="5"/>
                    <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                    <circle cx="32" cy="32" r="7" fill="#22C55E"/>
                </svg>
                <span class="font-bold text-base tracking-tight hidden sm:inline">
                    <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
                </span>
            </a>

            {{-- Nav items --}}
            <div class="flex items-center">
                @php
                    $nav = [
                        ['route'=>'dashboard',           'pat'=>'dashboard',    'icon'=>'layout-dashboard','label'=>'Dashboard'],
                        ['route'=>'transactions.create', 'pat'=>'transactions*','icon'=>'plus-circle',     'label'=>'Lançar'],
                        ['route'=>'bills.index',         'pat'=>'bills*',       'icon'=>'receipt',         'label'=>'Contas'],
                        ['route'=>'history.index',       'pat'=>'history*',     'icon'=>'clock',           'label'=>'Histórico'],
                        ['route'=>'reports.index',       'pat'=>'reports*',     'icon'=>'bar-chart-3',     'label'=>'Relatórios'],
                        ['route'=>'categories.index',    'pat'=>'categories*',  'icon'=>'tag',             'label'=>'Categorias'],
                        ['route'=>'alerts.index',        'pat'=>'alerts*',      'icon'=>'bell',            'label'=>'Alertas'],
                    ];
                @endphp
                @foreach($nav as $item)
                @php $active = request()->routeIs($item['pat']); @endphp
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                   class="group relative flex flex-col items-center gap-0.5 px-2.5 py-3 transition-colors
                          {{ $active ? 'nav-active' : 'text-foco-muted hover:text-foco-text' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4.5 h-4.5 shrink-0" style="width:18px;height:18px"></i>
                    <span class="text-[10px] font-medium leading-none hidden md:block">{{ $item['label'] }}</span>
                    {{-- tooltip mobile --}}
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-foco-text text-white text-xs px-2 py-1 rounded-md whitespace-nowrap
                                 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity md:hidden">
                        {{ $item['label'] }}
                    </span>
                </a>
                @endforeach

                {{-- Avatar / dropdown --}}
                <div x-data="{ open: false }" class="relative ml-1">
                    <button @click="open = !open" @click.outside="open = false"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 ml-1"
                            style="background:#6366F1;letter-spacing:-.02em">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </button>
                    <div x-show="open" x-cloak style="display:none"
                         class="absolute right-0 top-10 w-52 bg-white rounded-xl py-1 z-50"
                         style="box-shadow:0 8px 24px rgba(99,102,241,.15),0 0 0 1px rgba(99,102,241,.08)">
                        <div class="px-4 py-2.5 border-b border-foco-border">
                            <p class="text-sm font-semibold text-foco-text truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-foco-muted truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('billing.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-foco-text hover:bg-foco-surface transition-colors">
                            <i data-lucide="credit-card" class="w-4 h-4 text-foco-muted"></i>
                            Assinatura
                            @if(auth()->user()->onTrial())
                                <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded" style="color:#D97706;background:#D9770618">TRIAL</span>
                            @endif
                        </a>
                        <a href="{{ route('settings.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-foco-text hover:bg-foco-surface transition-colors">
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
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <script>lucide.createIcons();</script>
    @stack('scripts')
</body>
</html>

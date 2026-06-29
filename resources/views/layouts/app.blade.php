<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinFoco — @yield('title', 'Dashboard')</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'foco-bg':      '#0F0F13',
                        'foco-surface': '#1A1A22',
                        'foco-border':  '#2A2A38',
                        'foco-entrada': '#22C55E',
                        'foco-saida':   '#EF4444',
                        'foco-alerta':  '#F59E0B',
                        'foco-text':    '#F1F5F9',
                        'foco-muted':   '#64748B',
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
        body { font-size: 16px; background-color: #0F0F13; color: #F1F5F9; }
        .btn-primary { font-size: 18px; font-weight: 700; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-foco-bg text-foco-text font-sans min-h-screen">

    {{-- Toast sucesso --}}
    @if(session('sucesso'))
    <div x-data="{ show: true }" x-show="show" x-transition x-cloak
         x-init="setTimeout(() => show = false, 2000)"
         class="fixed top-4 right-4 z-50 bg-foco-entrada text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 text-sm font-semibold">
        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        {{ session('sucesso') }}
    </div>
    @endif

    {{-- Erros de validação --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="fixed top-4 right-4 z-50 bg-foco-saida text-white px-5 py-3 rounded-xl shadow-lg max-w-sm">
        <div class="flex items-start gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
            <ul class="text-sm space-y-0.5 flex-1">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
            <button @click="show = false" class="opacity-70 hover:opacity-100 shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- Navbar --}}
    <nav class="bg-foco-surface border-b border-foco-border sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-16">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <svg width="34" height="34" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="26" fill="none" stroke="#2A2A38" stroke-width="5"/>
                    <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                    <circle cx="32" cy="32" r="7" fill="#22C55E"/>
                </svg>
                <span class="font-bold text-xl hidden sm:block">
                    <span class="text-foco-text">Fin</span><span class="text-foco-accent">Foco</span>
                </span>
            </a>

            <div class="flex items-center gap-0.5 sm:gap-1 overflow-x-auto">
                @php
                    $nav = [
                        ['route'=>'dashboard',           'pat'=>'dashboard',    'icon'=>'layout-dashboard','label'=>'Dashboard'],
                        ['route'=>'transactions.create', 'pat'=>'transactions*','icon'=>'plus-circle',     'label'=>'Lançar'],
                        ['route'=>'bills.index',         'pat'=>'bills*',       'icon'=>'receipt',         'label'=>'Contas'],
                        ['route'=>'history.index',       'pat'=>'history*',     'icon'=>'clock',           'label'=>'Histórico'],
                        ['route'=>'categories.index',    'pat'=>'categories*',  'icon'=>'tag',             'label'=>'Categorias'],
                        ['route'=>'alerts.index',        'pat'=>'alerts*',      'icon'=>'bell',            'label'=>'Alertas'],
                        ['route'=>'settings.show',       'pat'=>'settings*',    'icon'=>'settings-2',      'label'=>'Config'],
                    ];
                @endphp
                @foreach($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-1 px-2 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap
                          {{ request()->routeIs($item['pat']) ? 'bg-foco-accent text-white' : 'text-foco-muted hover:text-foco-text hover:bg-foco-border' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 shrink-0"></i>
                    <span class="hidden xl:inline">{{ $item['label'] }}</span>
                </a>
                @endforeach
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

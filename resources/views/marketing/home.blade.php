<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ─── SEO ─────────────────────────────────────────────────────────── --}}
    <title>FinFoco — Controle financeiro simples para quem tem TDAH</title>
    <meta name="description" content="Registre gastos em menos de 3 cliques, veja seu saldo na hora e receba alertas antes de estourar o orçamento. Feito para cérebros com TDAH. Teste grátis por 7 dias.">
    <link rel="canonical" href="https://finfoco.nexialabs.com.br/">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="XBiTmaa-B-fDn0VqbYbBKhopfXqkPBJXbFiOkl7ejeU">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://finfoco.nexialabs.com.br/">
    <meta property="og:site_name" content="FinFoco">
    <meta property="og:title" content="FinFoco — Controle financeiro simples para quem tem TDAH">
    <meta property="og:description" content="Registre gastos em menos de 3 cliques, veja seu saldo na hora e receba alertas antes de estourar o orçamento. Teste grátis por 7 dias.">
    <meta property="og:image" content="https://finfoco.nexialabs.com.br/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="FinFoco — Controle financeiro para cérebros com TDAH">
    <meta property="og:locale" content="pt_BR">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="FinFoco — Controle financeiro simples para quem tem TDAH">
    <meta name="twitter:description" content="Registre gastos em menos de 3 cliques, veja seu saldo na hora e receba alertas antes de estourar o orçamento. Teste grátis por 7 dias.">
    <meta name="twitter:image" content="https://finfoco.nexialabs.com.br/og-image.png">

    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon.svg">
    <meta name="theme-color" content="#6366F1">

    {{-- Dados estruturados: aplicativo + FAQ --}}
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "FinFoco",
        "url": "https://finfoco.nexialabs.com.br/",
        "description": "Controlador financeiro pessoal projetado para pessoas com TDAH. Lançamentos em menos de 3 cliques, dashboard visual e alertas de gasto.",
        "applicationCategory": "FinanceApplication",
        "operatingSystem": "Web",
        "inLanguage": "pt-BR",
        "offers": {
            "@type": "Offer",
            "price": "19.98",
            "priceCurrency": "BRL",
            "description": "Assinatura mensal com 7 dias de teste grátis"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "O FinFoco é só para quem tem TDAH?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Não. O FinFoco foi projetado para reduzir a carga cognitiva ao máximo, o que ajuda quem tem TDAH — mas qualquer pessoa que abandona planilhas e apps complicados se beneficia da mesma simplicidade."
                }
            },
            {
                "@type": "Question",
                "name": "Quanto custa o FinFoco?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "R$ 19,98 por mês, com 7 dias de teste grátis. Você pode cancelar quando quiser, direto no app, sem burocracia."
                }
            },
            {
                "@type": "Question",
                "name": "Preciso conectar minha conta bancária?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Não. O FinFoco funciona com lançamentos manuais ultrarrápidos — menos de 3 cliques por registro. Seus dados bancários nunca são acessados."
                }
            },
            {
                "@type": "Question",
                "name": "Funciona no celular?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sim. O FinFoco é um app web responsivo: funciona no navegador do celular, tablet e computador, sem precisar instalar nada."
                }
            },
            {
                "@type": "Question",
                "name": "Como funciona o teste grátis?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Ao criar sua conta você tem 7 dias de acesso completo, sem cartão de crédito. Só assina se gostar."
                }
            }
        ]
    }
    </script>
    @endverbatim

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    <script defer src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        body { font-size: 16px; background-color: #FFFFFF; color: #1E1B4B; }
        html { scroll-behavior: smooth; }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(99,102,241,.08), 0 0 0 1px rgba(99,102,241,.06);
        }
        details summary { cursor: pointer; list-style: none; }
        details summary::-webkit-details-marker { display: none; }
        details[open] .faq-chevron { transform: rotate(180deg); }
        .faq-chevron { transition: transform .2s ease; }
    </style>
</head>
<body class="bg-foco-bg text-foco-text font-sans min-h-screen antialiased">

    {{-- ─── Cabeçalho ───────────────────────────────────────────────────── --}}
    <header class="border-b border-foco-border">
        <nav aria-label="Navegação principal" class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2" aria-label="FinFoco — página inicial">
                <img src="/logo.svg" alt="Logotipo do FinFoco" width="32" height="32" class="w-8 h-8">
                <span class="text-xl font-bold">Fin<span class="text-foco-accent">Foco</span></span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="bg-foco-accent hover:bg-indigo-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors inline-flex items-center gap-2">
                        <i data-lucide="layout-dashboard" class="w-4 h-4" aria-hidden="true"></i>
                        Abrir painel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-foco-muted hover:text-foco-text px-3 py-2">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-foco-accent hover:bg-indigo-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors">
                        Criar conta grátis
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        {{-- ─── Hero ────────────────────────────────────────────────────── --}}
        <section aria-labelledby="hero-titulo" class="max-w-5xl mx-auto px-4 pt-16 pb-20 text-center">
            <p class="inline-flex items-center gap-2 bg-foco-surface border border-foco-border text-foco-accent text-sm font-semibold px-4 py-1.5 rounded-full mb-6">
                <i data-lucide="brain" class="w-4 h-4" aria-hidden="true"></i>
                Projetado para cérebros com TDAH
            </p>
            <h1 id="hero-titulo" class="text-4xl md:text-5xl font-bold leading-tight max-w-3xl mx-auto">
                Controle financeiro que <span class="text-foco-accent">não exige força de vontade</span>
            </h1>
            <p class="text-lg text-foco-muted max-w-2xl mx-auto mt-6">
                Registre um gasto em menos de 3 cliques, veja seu saldo na hora e receba um alerta
                antes de estourar o orçamento. Sem planilhas, sem categorias infinitas, sem culpa.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-10">
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}"
                   class="bg-foco-accent hover:bg-indigo-600 text-white text-lg font-bold px-8 py-4 rounded-xl transition-colors inline-flex items-center gap-2 w-full sm:w-auto justify-center">
                    <i data-lucide="rocket" class="w-5 h-5" aria-hidden="true"></i>
                    @auth Abrir meu painel @else Começar teste grátis de 7 dias @endauth
                </a>
                <a href="#como-funciona"
                   class="text-foco-accent font-semibold text-lg px-6 py-4 inline-flex items-center gap-2">
                    Ver como funciona
                    <i data-lucide="arrow-down" class="w-5 h-5" aria-hidden="true"></i>
                </a>
            </div>
            <p class="text-sm text-foco-muted mt-4">Sem cartão de crédito no teste. Cancele quando quiser.</p>
        </section>

        {{-- ─── Problema ────────────────────────────────────────────────── --}}
        <section aria-labelledby="problema-titulo" class="bg-foco-surface border-y border-foco-border py-16">
            <div class="max-w-5xl mx-auto px-4 text-center">
                <h2 id="problema-titulo" class="text-3xl font-bold">Planilhas e apps complicados não foram feitos para você</h2>
                <p class="text-foco-muted max-w-2xl mx-auto mt-4">
                    Quem tem TDAH não falha por falta de disciplina — falha porque as ferramentas exigem
                    memória, etapas e atenção demais. O FinFoco elimina cada uma dessas barreiras.
                </p>
                <div class="grid sm:grid-cols-3 gap-6 mt-10 text-left">
                    <article class="card p-6">
                        <i data-lucide="timer" class="w-8 h-8 text-foco-accent mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold text-lg">Menos de 3 cliques</h3>
                        <p class="text-foco-muted text-sm mt-2">Abrir, digitar o valor, salvar. Se o registro demora, ele não acontece — por isso o FinFoco é rápido de verdade.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="eye" class="w-8 h-8 text-foco-accent mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold text-lg">Tudo visual</h3>
                        <p class="text-foco-muted text-sm mt-2">Verde é entrada, vermelho é saída, amarelo é atenção. Você entende sua situação num relance, sem ler relatórios.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="bell-ring" class="w-8 h-8 text-foco-accent mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold text-lg">Memória zero</h3>
                        <p class="text-foco-muted text-sm mt-2">Alertas de gasto e lembretes de contas avisam você — nada depende de lembrar de abrir o app.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ─── Como funciona ───────────────────────────────────────────── --}}
        <section id="como-funciona" aria-labelledby="como-titulo" class="max-w-5xl mx-auto px-4 py-20">
            <h2 id="como-titulo" class="text-3xl font-bold text-center">Como funciona</h2>
            <ol class="grid sm:grid-cols-3 gap-8 mt-12">
                <li class="text-center">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-foco-accent text-white text-xl font-bold" aria-hidden="true">1</span>
                    <h3 class="font-bold text-lg mt-4">Crie sua conta</h3>
                    <p class="text-foco-muted text-sm mt-2">Só e-mail e senha. Você já entra com 7 dias grátis e categorias prontas para usar.</p>
                </li>
                <li class="text-center">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-foco-accent text-white text-xl font-bold" aria-hidden="true">2</span>
                    <h3 class="font-bold text-lg mt-4">Registre no momento</h3>
                    <p class="text-foco-muted text-sm mt-2">Gastou? Abriu, digitou, salvou. Entradas e saídas em segundos, do celular ou computador.</p>
                </li>
                <li class="text-center">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-foco-accent text-white text-xl font-bold" aria-hidden="true">3</span>
                    <h3 class="font-bold text-lg mt-4">Deixe o app vigiar</h3>
                    <p class="text-foco-muted text-sm mt-2">Dashboard com saldo do dia e da semana, alertas por categoria e aviso de contas a vencer.</p>
                </li>
            </ol>
        </section>

        {{-- ─── Recursos ────────────────────────────────────────────────── --}}
        <section aria-labelledby="recursos-titulo" class="bg-foco-surface border-y border-foco-border py-20">
            <div class="max-w-5xl mx-auto px-4">
                <h2 id="recursos-titulo" class="text-3xl font-bold text-center">Tudo que você precisa, nada que atrapalhe</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">
                    <article class="card p-6">
                        <i data-lucide="zap" class="w-7 h-7 text-foco-entrada mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Lançamento rápido</h3>
                        <p class="text-foco-muted text-sm mt-2">Entrada ou saída em menos de 3 cliques, com repetição de lançamentos frequentes.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="layout-dashboard" class="w-7 h-7 text-foco-accent mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Dashboard visual</h3>
                        <p class="text-foco-muted text-sm mt-2">Saldo, gastos do dia e da semana em cores fixas que o cérebro entende sem esforço.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="bell" class="w-7 h-7 text-foco-alerta mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Alertas de gasto</h3>
                        <p class="text-foco-muted text-sm mt-2">Defina um limite por categoria e seja avisado antes de passar do ponto.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="calendar-clock" class="w-7 h-7 text-foco-saida mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Contas e parcelas</h3>
                        <p class="text-foco-muted text-sm mt-2">Contas a pagar e receber, com parcelamento e aviso por e-mail antes do vencimento.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="tags" class="w-7 h-7 text-foco-accent mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Categorias com cores</h3>
                        <p class="text-foco-muted text-sm mt-2">Poucas categorias, cada uma com cor e ícone próprios. Identificação instantânea.</p>
                    </article>
                    <article class="card p-6">
                        <i data-lucide="bar-chart-3" class="w-7 h-7 text-foco-entrada mb-3" aria-hidden="true"></i>
                        <h3 class="font-bold">Relatórios e CSV</h3>
                        <p class="text-foco-muted text-sm mt-2">Evolução mês a mês e exportação do histórico completo quando você precisar.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ─── Preço ───────────────────────────────────────────────────── --}}
        <section id="preco" aria-labelledby="preco-titulo" class="max-w-5xl mx-auto px-4 py-20 text-center">
            <h2 id="preco-titulo" class="text-3xl font-bold">Um preço só. Sem pegadinha.</h2>
            <div class="card max-w-md mx-auto mt-10 p-8">
                <p class="text-foco-muted font-semibold">Plano mensal</p>
                <p class="mt-2">
                    <span class="text-5xl font-bold">R$ 19,98</span>
                    <span class="text-foco-muted">/mês</span>
                </p>
                <ul class="text-left space-y-3 mt-6 text-sm">
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-5 h-5 text-foco-entrada shrink-0" aria-hidden="true"></i>
                        7 dias de teste grátis, sem cartão
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-5 h-5 text-foco-entrada shrink-0" aria-hidden="true"></i>
                        Todos os recursos incluídos
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-5 h-5 text-foco-entrada shrink-0" aria-hidden="true"></i>
                        Cancele quando quiser, direto no app
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                   class="block bg-foco-accent hover:bg-indigo-600 text-white text-lg font-bold px-8 py-4 rounded-xl transition-colors mt-8">
                    Começar teste grátis
                </a>
            </div>
        </section>

        {{-- ─── FAQ ─────────────────────────────────────────────────────── --}}
        <section id="faq" aria-labelledby="faq-titulo" class="bg-foco-surface border-t border-foco-border py-20">
            <div class="max-w-3xl mx-auto px-4">
                <h2 id="faq-titulo" class="text-3xl font-bold text-center">Perguntas frequentes</h2>
                <div class="space-y-4 mt-10">
                    <details class="card p-5">
                        <summary class="flex items-center justify-between font-bold">
                            O FinFoco é só para quem tem TDAH?
                            <i data-lucide="chevron-down" class="w-5 h-5 faq-chevron shrink-0" aria-hidden="true"></i>
                        </summary>
                        <p class="text-foco-muted text-sm mt-3">Não. O FinFoco foi projetado para reduzir a carga cognitiva ao máximo, o que ajuda quem tem TDAH — mas qualquer pessoa que abandona planilhas e apps complicados se beneficia da mesma simplicidade.</p>
                    </details>
                    <details class="card p-5">
                        <summary class="flex items-center justify-between font-bold">
                            Quanto custa?
                            <i data-lucide="chevron-down" class="w-5 h-5 faq-chevron shrink-0" aria-hidden="true"></i>
                        </summary>
                        <p class="text-foco-muted text-sm mt-3">R$ 19,98 por mês, com 7 dias de teste grátis. Você pode cancelar quando quiser, direto no app, sem burocracia.</p>
                    </details>
                    <details class="card p-5">
                        <summary class="flex items-center justify-between font-bold">
                            Preciso conectar minha conta bancária?
                            <i data-lucide="chevron-down" class="w-5 h-5 faq-chevron shrink-0" aria-hidden="true"></i>
                        </summary>
                        <p class="text-foco-muted text-sm mt-3">Não. O FinFoco funciona com lançamentos manuais ultrarrápidos — menos de 3 cliques por registro. Seus dados bancários nunca são acessados.</p>
                    </details>
                    <details class="card p-5">
                        <summary class="flex items-center justify-between font-bold">
                            Funciona no celular?
                            <i data-lucide="chevron-down" class="w-5 h-5 faq-chevron shrink-0" aria-hidden="true"></i>
                        </summary>
                        <p class="text-foco-muted text-sm mt-3">Sim. O FinFoco é um app web responsivo: funciona no navegador do celular, tablet e computador, sem precisar instalar nada.</p>
                    </details>
                    <details class="card p-5">
                        <summary class="flex items-center justify-between font-bold">
                            Como funciona o teste grátis?
                            <i data-lucide="chevron-down" class="w-5 h-5 faq-chevron shrink-0" aria-hidden="true"></i>
                        </summary>
                        <p class="text-foco-muted text-sm mt-3">Ao criar sua conta você tem 7 dias de acesso completo, sem cartão de crédito. Só assina se gostar.</p>
                    </details>
                </div>
            </div>
        </section>

        {{-- ─── CTA final ───────────────────────────────────────────────── --}}
        <section aria-labelledby="cta-titulo" class="max-w-5xl mx-auto px-4 py-20 text-center">
            <h2 id="cta-titulo" class="text-3xl font-bold">Pronto para parar de brigar com planilhas?</h2>
            <p class="text-foco-muted mt-4">Crie sua conta em menos de um minuto e teste grátis por 7 dias.</p>
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 bg-foco-accent hover:bg-indigo-600 text-white text-lg font-bold px-8 py-4 rounded-xl transition-colors mt-8">
                <i data-lucide="rocket" class="w-5 h-5" aria-hidden="true"></i>
                Começar teste grátis de 7 dias
            </a>
        </section>
    </main>

    {{-- ─── Rodapé ──────────────────────────────────────────────────────── --}}
    <footer class="border-t border-foco-border py-10">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-foco-muted">
            <p class="flex items-center gap-2">
                <img src="/logo.svg" alt="" width="20" height="20" class="w-5 h-5" aria-hidden="true">
                © {{ date('Y') }} FinFoco — controle financeiro para cérebros com TDAH.
            </p>
            <nav aria-label="Links do rodapé" class="flex items-center gap-6">
                <a href="#como-funciona" class="hover:text-foco-text">Como funciona</a>
                <a href="#preco" class="hover:text-foco-text">Preço</a>
                <a href="{{ route('login') }}" class="hover:text-foco-text">Entrar</a>
                <a href="{{ route('register') }}" class="hover:text-foco-text">Criar conta</a>
            </nav>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>

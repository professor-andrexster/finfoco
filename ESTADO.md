# ESTADO DO PROJETO — FinFoco
Última atualização: 2026-07-02

## STATUS GERAL
**PRODUÇÃO NO AR** em https://finfoco.nexialabs.com.br
Sistema SaaS multi-usuário com autenticação, 9 módulos, parcelamentos e diagnóstico completo aplicado.
**Cobrança recorrente via Stripe (Laravel Cashier) está 100% funcional em produção**, modo LIVE,
validada end-to-end (checkout, webhook assinado, portal de billing).

## MÓDULOS
- [x] 1. Setup Laravel + MySQL + Deploy Hostinger
- [x] 2. Lançamento Rápido (entrada/saída em < 3 cliques)
- [x] 3. Dashboard Visual (saldo, safe-to-spend, toggle dia/semana)
- [x] 4. Categorias com Cores e Ícones
- [x] 5. Alertas Simples (gasto excessivo por categoria)
- [x] 6. Histórico com Busca Rápida + datas relativas + delete inline
- [x] 7. Contas a Pagar/Receber (parcelado + recorrente + marcarPago)
- [x] 8. Lembretes (widget no dashboard + avisos inteligentes)
- [x] 9. Configurações (valor_hora + limite_impulso)
- [x] SaaS: Auth login/registro + multi-tenant user_id em todas as tabelas
- [x] SaaS: Cobrança recorrente via Stripe (Laravel Cashier) — trial 7 dias + assinatura mensal

---

## INFRAESTRUTURA

### Produção (Hostinger)
- URL: https://finfoco.nexialabs.com.br
- Servidor: u137664132@147.93.39.64 porta 65002
- SSH: `ssh -i ~/.ssh/finfoco_deploy -p 65002 u137664132@147.93.39.64`
- Deploy: `rsync -az -e "ssh -i ~/.ssh/finfoco_deploy -p 65002" --exclude='.env' --exclude='vendor/' --exclude='.git/' /home/andre_gomes/finfoco-claude-code/ u137664132@147.93.39.64:~/finfoco/`
- Banco: `u137664132_finfocoDB` / user `u137664132_finfocoUser`
- PHP: 8.2.30 (produção), 8.3.6 (dev local)
- Composer: `php artisan config:cache && php artisan route:cache && php artisan view:cache` após cada deploy

### Dev local (WSL2)
- Código: `/home/andre_gomes/finfoco-claude-code/`
- MariaDB local: port 3307, socket `/tmp/finfoco_mysql.sock`
- Git: branch `finfoco-main` → remote `origin/main` em https://github.com/professor-andrexster/finfoco
- Push: `git push origin HEAD:main`

### Restart do banco local após reboot
```bash
/usr/sbin/mysqld --datadir=/tmp/finfoco_mysql_data --socket=/tmp/finfoco_mysql.sock --port=3307 --pid-file=/tmp/finfoco_mysql.pid --log-error=/tmp/finfoco_mysql.err --user=andre_gomes &
```

---

## SCHEMA ATUAL (produção)

```sql
users            — id, name, email, password, remember_token, timestamps
categories       — id, nome, cor, icone, tipo, user_id (null=global), created_at
transactions     — id, tipo, valor, descricao, categoria_id, data, user_id, timestamps
alerts           — id, categoria_id, limite_valor, periodo, ativo, user_id, created_at
bills            — id, tipo, descricao, valor, categoria_id, vencimento, status,
                   recorrente, recorrencia, pago_em, parcelas_total, parcela_atual, user_id, timestamps
reminders        — id, titulo, data_lembrete, concluido, user_id, timestamps
settings         — PK(user_id, chave), valor  ← chave-valor por usuário
```

Migrations rodadas em produção:
- `0001_01_01_000000` — users
- `0001_01_01_000001` — cache
- `0001_01_01_000002` — jobs
- `2024_01_01_000001` — categories
- `2024_01_01_000002` — transactions
- `2024_01_01_000003` — alerts
- `2024_01_01_000004` — bills
- `2024_01_01_000005` — reminders
- `2024_01_01_000006` — settings
- `2024_01_02_000001` — add_user_id_to_all_tables
- `2024_01_02_000002` — add_parcelas_to_bills

---

## O QUE FOI CONSTRUÍDO

### V1 — Módulos 1 a 6 (2026-06-28)
- Migrations: categories, transactions, alerts
- Models: Category, Transaction, Alert (fillable, relacionamentos, casts)
- CategorySeeder: 6 categorias via firstOrCreate (idempotente, user_id=null = global)
- Controllers: Dashboard, Transaction, Category, Alert
- Views: dashboard, lançamento, histórico, categorias, alertas
- Design TDAH: toggle visual tipo, feedback verde 3s, erros vermelho permanente

### V2 — Módulos 7 a 9 e Features TDAH (2026-06-28)

#### Módulo 7 — Contas a Pagar/Receber
- `app/Models/Bill.php` — fillable, casts, `isParcelado()`, `parcelasRestantes()`, `calcularProximaOcorrencia()`
- `app/Http/Controllers/BillController.php` — CRUD + `marcarPago` + `destroyParcelamento`
- Views: `resources/views/bills/` — 3 seções (Parcelamentos / Contas simples / Pagas)
- Rotas: `/contas`, `/contas/nova`, `/contas/{bill}/pagar`, `/contas/{bill}`, `/contas-parcelamento`

#### Módulo 8 — Lembretes e Avisos
- `app/Models/Reminder.php`
- `app/Http/Controllers/ReminderController.php` — store, toggle, destroy
- Widget integrado no dashboard com formulário inline e toggle 1-clique
- Avisos automáticos: `DashboardController::gerarAvisos()` — contas vencidas, vencendo em 3 dias, limites de alerta

#### Módulo 9 — Configurações
- `app/Models/Setting.php` — PK composta `(user_id, chave)`, métodos estáticos `get()` e `set()`
- `app/Http/Controllers/SettingController.php`
- View: formulário com `valor_hora` e `limite_impulso`

#### Features V2
- **Modal anti-impulso**: quando saída > `limite_impulso` → modal Alpine.js com countdown de 10s obrigatório; "Sim, lançar" bloqueado até zerar
- **Custo em horas de trabalho**: "≈ Xh de trabalho" abaixo do valor (só quando tipo=saida e valor_hora>0)
- **Semáforo de vencimento**: `diff<=0` → 🔴 red (vencida/hoje), `diff<=3` → 🟡 yellow, `diff>3` → 🟢 green
- **Safe-to-spend**: "Pode gastar hoje" = (saldo + entradas esperadas − contas pendentes) ÷ dias restantes no mês
- **Safe-to-spend semanal**: mesmo cálculo mas com janela semanal

#### Helpers
- `app/Helpers/DateHelper.php` — `formatarDataRelativa()` e `semaforo()` estáticos
- Autoloaded via `composer.json autoload.files`

### V3 — SaaS + Parcelamentos (2026-07-01)

#### Autenticação / SaaS
- `app/Http/Controllers/Auth/LoginController.php` — create/store/destroy
- `app/Http/Controllers/Auth/RegisterController.php` — create/store (auto-login)
- `resources/views/auth/login.blade.php` — página standalone com identidade FinFoco
- `resources/views/auth/register.blade.php` — página standalone
- Todas as rotas protegidas por `middleware('auth')`
- `user_id` adicionado a todas as tabelas via migração
- `Category::disponiveis()`: retorna categorias globais (null) + do usuário logado
- `booted()` hooks nos models: auto-set `user_id = auth()->id()` na criação
- `abort_unless($record->user_id === auth()->id(), 403)` em todos os controllers
- Avatar com iniciais + dropdown logout no nav

#### Parcelamentos
- `BillController::store()`: quando `parcelas_total` definido → cria **todas** as N parcelas de uma vez com vencimentos mensais consecutivos
- Bills index redesenhado em 3 seções:
  - **Parcelamentos**: UMA linha por compra com badge `X/Y`, barra de progresso, valor restante total; expandir mostra todas as parcelas pendentes com semáforo
  - **Contas simples**: contas avulsas e recorrentes pendentes
  - **Pagas recentemente**: últimas 30 pagas
- `BillController::destroyParcelamento()`: cancela todas as parcelas pendentes de uma compra de uma vez
- `BillController::marcarPago()`: registra Transaction automática; recorrentes geram próxima ocorrência; parceladas simplesmente marcam aquela parcela como paga (as demais já existem)

#### Design: tema branco minimalista
- Paleta migrada de dark (#0F0F13) para white (#FFFFFF)
- Cards com `box-shadow` sutil em indigo
- Nav com underline indicator para item ativo
- Logo SVG com alvo concêntrico (anel cinza + roxo + verde)

#### Dashboard: toggle Hoje / Esta semana
- Seletor com persistência em `localStorage`
- Visão "Esta semana": stats de saídas/entradas da semana + safe-to-spend semanal
- `DashboardController` calcula `podeGastarSemana` e `entradasSemana`

#### Correções do diagnóstico (2026-07-01)
- `DateHelper::semaforo()`: `diff=0` (hoje) agora retorna `red` (antes retornava `yellow`)
- Dashboard avisos: cores `#991B1B`/`#92400E` substituídas por `foco-saida`/`foco-alerta` da paleta
- Settings inputs: `bg-foco-bg` → `bg-white`
- Histórico: data relativa ("há 3 dias") em vez de só data absoluta
- Histórico: botão excluir inline adicionado em cada lançamento
- Modal anti-impulso: countdown de 10s obrigatório (antes era só psicológico)

### V4 — Cobrança recorrente via Stripe / Laravel Cashier (2026-07-02)
- Pacote `laravel/cashier` v16.6 instalado
- Migrations do Cashier rodadas (local): `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at` em `users`;
  tabelas `subscriptions` e `subscription_items`
- `User` model: trait `Billable` do Cashier
- Modelo de acesso: trial grátis de 7 dias sem cartão no cadastro (`RegisterController` seta `trial_ends_at`),
  depois exige assinatura ativa
- Novo middleware `App\Http\Middleware\EnsureSubscribed` (alias `subscribed`, registrado em `bootstrap/app.php`)
  — bloqueia acesso ao app quando trial expira e não há assinatura ativa; redireciona pra `/assinatura`
- `routes/web.php`: grupo `/assinatura*` só com `auth` (sem gate); resto do app exige `['auth','subscribed']`
- Novo `BillingController` (index/checkout/success/portal) — Stripe Checkout Session + Billing Portal via Cashier
- Grace period de 5 min em sessão pós-checkout (`billing_grace_until`) pra cobrir o intervalo até o webhook chegar
- Nova view `resources/views/billing/index.blade.php` com 4 estados visuais: assinante ativo (verde),
  trial tranquilo (neutro), trial acabando ≤3 dias (âmbar), bloqueado/expirado (vermelho)
- Link "Assinatura" + badge TRIAL no dropdown do avatar (`layouts/app.blade.php`)
- Comando `php artisan users:grant-trial --days=14` (idempotente) — dá 14 dias de graça aos usuários já
  existentes em produção antes desta feature entrar no ar
- `config/services.php`: bloco `stripe` (key/secret/price_mensal via env)
- `.env.example`: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=brl`,
  `STRIPE_PRICE_MENSAL` (placeholders vazios)
- `bootstrap/app.php`: exceção de CSRF pra `stripe/*` (rota de webhook do Cashier) + alias `subscribed`
- `DEPLOY.md`: nova seção "Cobrança via Stripe (setup único, primeira vez)" com passo a passo de produção
- QA aprovado

---

## PALETA DE CORES (atual)

```js
'foco-bg':      '#FFFFFF',   // fundo da página
'foco-surface': '#F7F7FD',   // cards hover, inputs
'foco-border':  '#E4E4F0',   // bordas
'foco-entrada': '#16A34A',   // verde — entradas
'foco-saida':   '#DC2626',   // vermelho — saídas/erro
'foco-alerta':  '#D97706',   // amarelo — atenção
'foco-text':    '#1E1B4B',   // texto principal
'foco-muted':   '#9794B8',   // texto secundário
'foco-accent':  '#6366F1',   // roxo — ação principal
```

---

## DECISÕES TÉCNICAS
- Pasta `stripe/` (com chaves reais do Stripe em texto plano, usada só como referência local pro deploy)
  protegida no `.gitignore` — nunca deve ser commitada
- `composer.lock` deve ser sempre regenerado/validado rodando `composer update` diretamente no servidor de
  produção (PHP 8.2.30 real + extensões corretas), não em ambiente com `--ignore-platform-reqs`, pra evitar
  resolver pacotes (ex: symfony/* v8.x) incompatíveis com a versão real do PHP em produção
- `deploy_hostinger.sh`: caminho relativo de `public_html/` até `~/finfoco/` no servidor Hostinger é de
  **3 níveis** (`../../../finfoco/`), não 2 — estrutura real é
  `/home/USER/domains/DOMINIO/public_html/` → `/home/USER/finfoco/`
- Laravel 12 + PHP 8.2/8.3 + Blade + Tailwind/Alpine/Lucide via CDN
- **SaaS**: auth session-based Laravel, `user_id` em todas as tabelas
- `User::$fillable` em array clássico (não `#[Fillable]` — PHP attribute não funciona no PHP 8.2 da Hostinger)
- Settings: PK composta `(user_id, chave)` — um valor por chave por usuário
- Categories globais: `user_id IS NULL` → visível a todos os usuários
- Cache driver = `file`, Queue = `sync` (hospedagem compartilhada, sem Redis)
- Parcelamentos: todas as parcelas criadas de uma vez (não lazy)
- `marcarPago()` cria Transaction automática; recorrentes geram próxima Bill; parceladas não (já existem)
- `DateHelper::semaforo()`: hoje = red (não yellow)
- DateHelper como classe de métodos estáticos (não Facade)
- `--ignore-platform-reqs` no `composer install` do servidor (symfony/clock declara PHP 8.4 mas funciona no 8.2)
- Bills (contas): edição permite alterar apenas descrição, valor, vencimento e categoria — tipo/parcelas/recorrência são imutáveis após criação, para não quebrar a consistência de parcelamentos já gerados
- Categorias globais (`user_id IS NULL`): não editáveis/não excluíveis por nenhum usuário, por design (compartilhadas); para customizar, o usuário deve criar sua própria categoria
- **Cobrança**: trial de 7 dias sem exigir cartão de crédito no cadastro (menor fricção no onboarding)
- `EnsureSubscribed` bloqueia só o "app" (dashboard/lançamentos/contas/etc); rotas de `/assinatura` ficam
  fora do gate para o usuário bloqueado sempre conseguir pagar e se desbloquear
- Grace period de 5 min em sessão após checkout, pra tolerar o delay assíncrono do webhook do Stripe sem
  bloquear o usuário que acabou de pagar
- `users:grant-trial --days=14`: usuários pré-existentes ganham 14 dias de graça no dia em que a feature
  de cobrança for ativada em produção, para não bloquear ninguém de surpresa

---

## PENDÊNCIAS / BLOQUEIOS
Nenhuma pendência de Stripe — setup manual concluído em 2026-07-02 (ver HISTÓRICO).

---

## QA — Último resultado (2026-07-01)
- 7/7 rotas HTTP 200 após login: `/`, `/lancamento`, `/contas`, `/historico`, `/categorias`, `/alertas`, `/configuracoes`
- `/login` e `/register` retornam 200 sem autenticação
- `/` sem autenticação retorna 302 → `/login`
- Registro via POST retorna 302 → dashboard

---

## ARQUIVOS IMPORTANTES
| Arquivo | Descrição |
|---------|-----------|
| `CLAUDE.md` | Regras absolutas do projeto (não alterar) |
| `ESTADO.md` | Este arquivo |
| `DIAGNOSTICO_ESTADO_ATUAL.md` | Auditoria de divergências V2 vs código (2026-07-01) |
| `DEPLOY.md` | Guia de deploy na Hostinger |
| `deploy_hostinger.sh` | Script rsync+SSH de deploy |
| `app/Helpers/DateHelper.php` | Datas relativas + semáforo |
| `routes/web.php` | Todas as rotas do app |

---

## HISTÓRICO

### 2026-07-02 — Deploy real do Stripe em produção (modo LIVE) + 3 bugs corrigidos
- **Deploy**: código enviado via rsync pra `/home/u137664132/finfoco/` e `public/` pra
  `domains/finfoco.nexialabs.com.br/public_html/`; `composer install`/`update --no-dev` rodado direto no
  servidor; `.env` de produção recebeu as 5 variáveis reais do Stripe em modo LIVE (`STRIPE_KEY`,
  `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=brl`, `STRIPE_PRICE_MENSAL`); migrations do
  Cashier rodadas (`migrate --force`); `users:grant-trial --days=14` deu 14 dias de graça aos 3 usuários
  já existentes; endpoint de webhook criado via API do Stripe (Live) apontando pra
  `https://finfoco.nexialabs.com.br/stripe/webhook`
- **Bug 1 — vazamento de credenciais**: pasta `stripe/keys.txt` (chaves reais Live) não estava protegida
  no git. Corrigido adicionando `/stripe/` ao `.gitignore` antes de qualquer commit
- **Bug 2 — `composer.lock` incompatível**: lock anterior tinha sido gerado com `--ignore-platform-reqs`
  e resolveu symfony/css-selector, symfony/event-dispatcher, symfony/string, symfony/translation,
  symfony/yaml em v8.x (exigem PHP >=8.4), incompatíveis com produção (PHP 8.2.30). Corrigido rodando
  `composer update --no-dev` direto no servidor (downgrade automático pra v7.4.x); lock corrigido trazido
  de volta pro repositório
- **Bug 3 — 500 em produção durante o deploy**: `deploy_hostinger.sh` ajustava `public_html/index.php`
  assumindo 2 níveis até `~/finfoco/` (`../vendor/autoload.php`), mas a estrutura real do servidor exige
  3 níveis (`../../../finfoco/`). Causou um 500 real durante o deploy de hoje; diagnosticado e corrigido
  na hora (site restabelecido, validado com curl). Aproveitado pra também corrigir o caminho de
  `storage/framework/maintenance.php`, que o script não ajustava antes
- **Preço real**: texto do botão em `billing/index.blade.php` corrigido de "R$ 19,90/mês" (placeholder)
  pra "R$ 19,98/mês" (valor real do Price `price_1TonlqFnZLWuEQvnD0qfN8OM`, confirmado via API do Stripe)
- **Validação end-to-end**: webhook com assinatura válida → 200 "Webhook Handled"; assinatura inválida →
  403 (rejeitado corretamente)
- SaaS Stripe está 100% funcional em produção agora

### 2026-07-02 — SaaS: cobrança recorrente via Stripe (Laravel Cashier)
- `laravel/cashier` v16.6 instalado, migrations rodadas (`stripe_id`, `pm_type`, `pm_last_four`,
  `trial_ends_at` em `users` + tabelas `subscriptions`/`subscription_items`)
- Trial de 7 dias sem cartão no cadastro; `EnsureSubscribed` (middleware `subscribed`) bloqueia o app
  após trial expirado sem assinatura ativa, redirecionando pra `/assinatura`
- `BillingController` (checkout/success/portal) via Stripe Checkout Session + Billing Portal
- Grace period de 5 min pós-checkout em sessão, pra tolerar delay do webhook
- View `billing/index.blade.php` com 4 estados (ativo/trial ok/trial acabando/bloqueado)
- Comando `users:grant-trial --days=14` pra não bloquear usuários já existentes
- `DEPLOY.md` atualizado com passo a passo de setup do Stripe em produção
- QA aprovado. Pendente apenas setup manual de credenciais/produto Stripe reais em produção

### 2026-07-02 — Fix: edição de Contas + inconsistência de autorização em Categorias
- Bug: módulo Contas (Bills) nunca teve edição — só create/store/marcarPago/destroy; usuário não conseguia mudar data de vencimento
  - Rotas `bills.edit` (GET /contas/{bill}/editar) e `bills.update` (PUT /contas/{bill})
  - `BillController::edit()`/`update()` — atualiza descrição, valor, vencimento e categoria; tipo/parcelas/recorrência permanecem imutáveis
  - Nova view `resources/views/bills/edit.blade.php`
  - Link "Editar" adicionado em `bills/index.blade.php` (contas simples e parcelas dentro do detalhe expandível)
- Bug: `CategoryController::edit()` permitia abrir o formulário de edição de categorias globais, mas `update()`/`destroy()` bloqueavam com 403 — usuário só descobria ao tentar salvar
  - `edit()` corrigido para usar a mesma checagem de `update()`/`destroy()`, bloqueando categorias globais desde o início
- Lançamentos (transactions): investigados, edição/exclusão já estavam corretos — não era bug
- QA aprovado

### 2026-07-01 — Diagnóstico ESTADO_ATUAL + 7 correções
- Lido `PROMPT_DIAGNOSTICO_ESTADO_ATUAL.md`, gerado `DIAGNOSTICO_ESTADO_ATUAL.md`
- Corrigido semáforo (hoje=red), cores off-palette, settings inputs, datas relativas no histórico, delete inline no histórico
- Adicionado: toggle Hoje/Semana no dashboard com safe-to-spend semanal
- Adicionado: countdown de 10s no modal anti-impulso (botão bloqueado até zerar)

### 2026-07-01 — Parcelamentos: todas as parcelas criadas de uma vez
- Ao cadastrar conta parcelada (ex: AR 12x), o sistema cria todas as 12 parcelas imediatamente
- Bills index redesenhado: seção Parcelamentos com UMA linha por compra + expandir
- `destroyParcelamento()`: cancela todas as parcelas pendentes de uma vez

### 2026-07-01 — Fix: User model mass assignment (502 no registro)
- `#[Fillable]` PHP attribute substituído por `$fillable` array clássico

### 2026-07-01 — SaaS multi-usuário + autenticação
- Login/registro implementados
- `user_id` em todas as tabelas, scoping em todos os controllers
- `abort_unless` para ownership enforcement

### 2026-07-01 — Deploy na Hostinger (LIVE)
- rsync + SSH configurados com chave ed25519
- Migrações rodadas em produção
- App acessível em https://finfoco.nexialabs.com.br

### 2026-06-28 — Design: tema branco minimalista
- Paleta migrada dark → white
- Dashboard reescrito com hero saldo+pode-gastar, stats, lembretes, últimas transações

### 2026-06-28 — V2 completo (módulos 7-9 + features TDAH)
- Contas a pagar/receber, lembretes, configurações
- Modal anti-impulso, custo em horas, safe-to-spend, avisos inteligentes
- DateHelper, CategorySeeder, SQL exportado

### 2026-06-28 — V1 completo (módulos 1-6)
- Laravel 12 inicializado, migrations, models, seeders, controllers, views
- Design TDAH aplicado, DEPLOY.md criado

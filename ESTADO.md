# ESTADO DO PROJETO — FinFoco
Última atualização: 2026-07-05 (V10: reset de senha, avisos por e-mail, meta dia a dia, evolução 6 meses, repetir lançamento, recorrência editável, onboarding, CSV)

## STATUS GERAL
**PRODUÇÃO NO AR** em https://finfoco.nexialabs.com.br
Sistema SaaS multi-usuário com autenticação, 9 módulos, parcelamentos e diagnóstico completo aplicado.
**Cobrança recorrente via Stripe (Laravel Cashier) está 100% funcional em produção**, modo LIVE,
testada de ponta a ponta com fluxo real de trial em produção (registro real via HTTP, dashboard/`/assinatura`
acessíveis durante o trial, bloqueio correto após expiração) — não é só "deployada", é validada com uso real.

## MÓDULOS
- [x] 1. Setup Laravel + MySQL + Deploy Hostinger
- [x] 2. Lançamento Rápido (entrada/saída em < 3 cliques)
- [x] 3. Dashboard Visual (saldo, safe-to-spend, toggle dia/semana/mês, gastos recorrentes)
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
- `2026_07_02_183440` — add_updated_at_to_bills_table (guarda `Schema::hasColumn`, corrige schema drift)

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

### V5 — Código de resgate para acesso vitalício (2026-07-02)
- Migration `add_lifetime_access_to_users_table`: coluna `users.lifetime_access` (boolean, default false)
- `User::casts()` inclui `'lifetime_access' => 'boolean'`; **não** está em `$fillable` (só via atribuição
  direta + `save()`, nunca mass assignment)
- `config('services.stripe.lifetime_access_code')` lê `LIFETIME_ACCESS_CODE` do `.env` — código fixo
  definido pelo dono do app (não gerado por usuário)
- `EnsureSubscribed`: libera acesso se `lifetime_access` for true, além de assinatura ativa/trial/grace period
- `BillingController::redeem()` (rota `POST /assinatura/resgatar`, nome `billing.redeem`) — valida o código
  com `hash_equals()` (evita timing attack); se bater, seta `lifetime_access = true` no usuário logado
- View `billing/index.blade.php`: novo estado "Acesso vitalício ativado" (cor accent/roxo, ícone crown, sem
  CTA — informativo), com prioridade sobre os outros 3 estados; formulário discreto de resgate de código
  aparece nos 3 estados que ainda pedem assinatura (trial tranquilo, trial acabando, bloqueado)
- **Deploy em produção concluído**: migration rodada, `.env` de produção com `LIFETIME_ACCESS_CODE` real
  preenchido (código informado ao usuário fora do repositório/chat de código — nunca commitado)
- Conta pessoal (`andrexster@gmail.com`) teve `trial_ends_at` forçado pra ontem propositalmente, a pedido
  do usuário, pra validar a tela de bloqueio/paywall na prática antes de resgatar o próprio código
- QA aprovado

### V10 — 8 melhorias (2026-07-05)
1. **Recuperação de senha**: `PasswordResetController` (request/email/reset/update), rotas
   `password.*` (nomes exigidos pelo notification), views standalone, link "Esqueci minha
   senha" no login, e-mail pt-BR via `ResetPassword::toMailUsing` no AppServiceProvider,
   resposta neutra (não revela e-mails), throttle 5/min. Fluxo E2E testado
2. **Aviso diário de contas por e-mail**: `finfoco:avisar-vencimentos` — um e-mail por
   usuário com atrasadas + vence hoje + vence amanhã (view HTML pura em
   `emails/aviso-vencimentos`, sem markdown → sem dependência de ext-dom). Disparado 1x/dia
   pelo tráfego via `rodarRotinaDiaria()` (helper extraído; backup usa o mesmo)
3. **Meta do dia a dia**: setting `meta_dia_a_dia` (3º campo em Configurações) + barra de
   progresso no dashboard (gasto manual do mês — `bill_id IS NULL` — vs meta; verde <80%,
   âmbar 80–99%, vermelho ≥100%)
4. **Evolução 6 meses nos Relatórios**: barras CSS entrou×saiu (verde/vermelho fixos), meses
   clicáveis navegam o relatório; uma query agrupada por `DATE_FORMAT('%Y-%m')`
5. **Repetir lançamento**: `POST /lancamento/{t}/repetir` duplica com data de hoje; botão
   copy-plus no histórico
6. **Recorrência editável** em contas não parceladas: `_cobranca` (avulsa|recorrente) +
   frequência no bills/edit; parcelas seguem imutáveis
7. **Onboarding**: card de 3 passos no dashboard enquanto faltar conta fixa ou lançamento
8. **Exportar CSV**: `GET /historico/exportar` respeitando os filtros da tela (query
   compartilhada via `queryHistorico()`); BOM UTF-8 + `;` pro Excel pt-BR

**Mail em produção configurado**: `MAIL_MAILER=sendmail` (`/usr/sbin/sendmail -t -i`),
from `noreply@finfoco.nexialabs.com.br`. Teste real enviado pra andrexster@gmail.com sem
exceção — deliverability (inbox vs spam) a confirmar pelo usuário.

**Dev local**: PHP 8.3 local NÃO tem ext-dom (php8.3-xml) — e-mails markdown do Laravel
(reset de senha) não renderizam localmente; view HTML pura renderiza normal. Produção tem
ext-dom. Instalar local: `sudo apt-get install -y php8.3-xml`.

QA V10: fluxo completo de reset (token→nova senha→login novo), avisos renderizando com
assunto/valores certos, meta 500 com gasto 75 → "cabem 425", CSV com BOM+";", recorrência
alterada e persistida, onboarding aparece pra usuário novo em produção e some após os passos.

### V9 — 5 melhorias de qualidade (2026-07-03)
- **Lançamento em sequência**: chips "Hoje/Ontem" no campo data + botão secundário
  "Salvar e lançar outro" (`continuar=1` → redirect de volta pro form). Funciona também
  quando o modal anti-impulso intercepta (`e.submitter` capturado em `tentarEnviar`,
  hidden input recriado em `confirmarEnvio` porque `form.submit()` nativo não repassa o botão)
- **Sugestões de descrição**: `TransactionController::create` envia as 10 descrições mais
  recentes distintas do usuário → `<datalist>` no input (menos digitação)
- **Relatórios com contexto**: "▲/▼ X% vs mês anterior" nos cards Entrou/Saiu; cores
  semânticas (gastar menos = verde; receber menos = âmbar); null-safe quando mês anterior vazio
- **PWA-lite**: `public/manifest.json` + theme-color + apple-touch-icon — instalável na tela
  inicial do celular. O manifest precisa ser copiado pro public_html no deploy (docroot separado)
- **Backup diário automático**: `php artisan finfoco:backup` (dump gzipado em
  `storage/app/backups`, retenção `--keep=14`). Hostinger bloqueia `shell_exec`/`exec` e não
  dá `crontab` via SSH → comando usa `proc_open` (permitido) com binário por caminho direto
  (`/usr/bin/mysqldump`) e gzip em PHP; agendamento via `terminating()` no AppServiceProvider:
  primeira requisição do dia em produção dispara o backup após a resposta (lock atômico em
  cache file, marca `backup_diario_em`). Validado em produção: backup gerado no primeiro hit
- QA em produção com usuário descartável (removido): manifest 200, "Salvar e lançar outro"
  redireciona pro form, sugestões presentes, relatórios 200

### V8 — Fixo × Dia a dia + Pagamento parcial + Totais (2026-07-03)

#### Separação fixo × variável (base fixa mensal vs "gasto besta")
- `transactions.bill_id` (FK nullable, nullOnDelete): cada pagamento registra de qual conta
  veio — permite classificar saídas como fixas (conta recorrente), contas/parcelas ou dia a dia
- `Bill::custoFixoMensal($uid)`: cálculo centralizado (era privado do DashboardController) —
  soma normalizada mensal das recorrentes (semanal ×52/12, anual ÷12), dedup por descrição.
  Retorna total + qtd + collection das contas. Usado por dashboard, contas e relatórios
- `Bill::valorMensalNormalizado()`: equivalente mensal de uma conta individual
- `/contas` reorganizada: seção **"Contas fixas mensais"** (recorrentes, com badge do total
  R$/mês no header e "≈ R$/mês" nas semanais/anuais) separada de **"Contas avulsas"**;
  linha de conta extraída pro partial `bills/_linha_conta.blade.php`
- `/relatorios`: seção **"Fixo × Dia a dia"** — barra proporcional com 3 segmentos
  (Contas fixas roxo / Contas e parcelas âmbar / Dia a dia vermelho), valores, % e a base
  fixa cadastrada. Classificação via `bill_id` + `bill.recorrente`

#### Pagamento parcial ("ir pagando a dívida e abatendo valor")
- Coluna `bills.valor_pago` (decimal, default 0, guarda hasColumn) — `restante() = valor − valor_pago`
- `POST /contas/{bill}/abater` (`bills.pagarParcial`): valida valor, cria Transaction
  "(parcial)" ligada à conta, acumula em valor_pago; abater ≥ restante quita a conta
  automaticamente (mesma lógica de conclusão do Marcar pago, extraída pra `concluirConta()`)
- `marcarPago` paga só o restante (não duplica o que já foi abatido); transação criada
  via `registrarPagamento()` compartilhado
- Linha da conta: botão circle-minus abre form inline "Abater valor" com restante visível;
  quando há abatimento mostra "R$ restante de R$ total · já abatido R$ X"
- Dashboard e totais usam `valor − valor_pago` nas contas pendentes

#### Totais na tela Contas
- Strip de 3 cards no topo: **Total a pagar (pendente)** (vermelho, inclui parcelas,
  desconta abatimentos), **Total a receber** (verde), **Custo fixo mensal** (roxo, com qtd)

#### Fix regressão (mesma data): "não consigo excluir uma dívida"
- A validação nova de `destroyParcelamento` passou a exigir `valor`, mas o form
  "Excluir tudo" em bills/index não enviava o campo → exclusão de parcelamento falhava
  silenciosamente (só um toast de erro). Hidden input `valor` adicionado ao form

#### QA V8 (local + produção)
- Abater 30 + 70 em conta de 100 → quita com 2 transactions somando exatamente 100 ✔
- Excluir parcelamento e conta simples ✔
- Produção: conta fixa criada → aparece em "Contas fixas mensais" com R$ 1.000,00/mês;
  abater 250 → mostra "já abatido R$ 250,00", restante 750 ✔ (usuário QA descartável removido)
- Migrations `bill_id` e `valor_pago` rodadas em produção ✔
- Obs: usuários reais já recadastraram 139 contas com o form corrigido no mesmo dia

### V7 — Auditoria completa + Relatórios + UX TDAH (2026-07-03)

#### Bugs críticos corrigidos
- **Toda conta nova virava parcelamento 12x**: o form `bills/create` mantinha
  `parcelas_total=12` e `recorrente=1` no DOM (x-show só esconde visualmente) e o POST
  enviava tudo. Produção tinha 397/397 contas parceladas e 0 recorrentes por causa disso.
  Correção dupla: `:disabled` nos inputs de modos inativos (input disabled não é enviado)
  + guarda server-side no `BillController::store()` que só honra os campos do `_modo` escolhido
- **Setting quebrado**: model com `$primaryKey = null` + `updateOrCreate` não consegue fazer
  UPDATE (não sabe montar o WHERE); e `$timestamps = true` sem colunas na migration
  (produção tem as colunas por drift). `set()` reescrito com `upsert()` do query builder;
  migration de settings alinhada ao schema real de produção (chave 60, valor TEXT, timestamps)
- **`migrate:fresh` quebrava em ambiente limpo**: linhas legadas single-user de `settings`
  impediam a PK composta NOT NULL; migration agora limpa antes do ALTER
- **`/relatorios` dava 500**: rota apontava para `ReportController` inexistente (trabalho
  inacabado de sessão anterior) — controller e view criados
- **Drift em `reminders`**: produção tem `updated_at`, migration não criava — criar lembrete
  quebrava em ambiente novo. Migration corrigida para `timestamps()`

#### Bugs altos corrigidos
- `marcarPago()`: guarda de status contra duplo clique (duplicava Transaction e, em
  recorrentes, gerava duas próximas ocorrências)
- `categoria_id` cross-tenant: validação `exists` simples aceitava categoria de outro
  usuário. Nova regra `Controller::categoriaDisponivel()` (globais + próprias) aplicada
  em transactions, bills e alerts
- Dashboard: stats de semana/mês sem limite superior de data — lançamentos futuros contavam
  no período atual. Agora sempre com whereDate >= e <=
- Lembretes vencidos sumiam do dashboard (query só pegava `data_lembrete >= hoje`) — agora
  pendentes NUNCA somem e vencidos aparecem destacados em vermelho com ícone

#### Bugs médios corrigidos
- `destroyParcelamento` não considerava `valor` (podia excluir parcelamento homônimo errado)
- Login/registro sem rate limit → `throttle:10,1`
- Alertas duplicados (mesma categoria+período) bloqueados com mensagem amigável
- Leitura morta de `visao_padrao` removida do SettingController (dashboard usa localStorage)

#### Módulo novo: Relatórios (`/relatorios`)
- `ReportController::index` — mês navegável (?mes=Y-m validado por regex), totais de
  entrada/saída/resultado, saídas agrupadas por categoria ordenadas por valor
- View com navegação ‹ mês ›, 3 cards de resumo e barras por categoria (cor da categoria)
- Link "Relatórios" no nav principal (ícone bar-chart-3)

#### Passe UX TDAH
- **Categoria em chips visíveis** no lançamento (create e edit): substitui o dropdown de
  2 cliques — todas as opções sempre à vista (memória zero), 1 clique, cor/ícone da categoria
- Campo Valor não inicia mais com "0" (x-model inicializava com 0 — obrigava apagar)
- Histórico: filtros avançados escondidos atrás de botão "sliders" (não pesam a tela);
  seleção em lote com barra flutuante "Excluir selecionados"
- Cores de significado fixo: "Pode gastar" positivo agora verde (era roxo accent)
- Botões verbo+substantivo: "Salvar lembrete", "Nova categoria", "Marcar pago"/"Marcar
  recebido", "Resgatar código", "Novo lançamento" (histórico)
- Toast de sucesso: 3s (spec) em vez de 2.5s
- Lembretes vencidos destacados em vermelho no dashboard

#### QA V7 (2026-07-03, local end-to-end com servidor real + MariaDB)
- 14 rotas autenticadas 200; guest: / → 302, login/register 200
- Conta à vista com payload "sujo" (parcelas+recorrente no POST) → cria 1 conta simples ✔
- Conta recorrente → recorrente=1 mensal ✔; parcelada 3x → 3 parcelas ✔
- Duplo POST em marcarPago → 1 transaction, 1 próxima ocorrência ✔
- Lançamento com categoria de outro usuário → rejeitado, nada criado ✔
- Settings salvos 2x → update funciona (era o caminho quebrado) ✔
- Exclusão em lote via /historico/lote → 302 e registros removidos ✔
- migrate:fresh --seed → 0 FAIL ✔

#### Pendência conhecida (dados de produção)
- As 397 contas existentes em produção foram criadas com o bug (todas parceladas 12x,
  nenhuma recorrente de verdade). O código novo impede casos novos, mas os dados antigos
  continuam como estão — reparo exige decisão do usuário (não dá pra inferir a intenção
  original de cada conta).

### V6 — Dashboard: visão mensal + gastos recorrentes (2026-07-02)
- Toggle "Hoje / Esta semana" no dashboard ganhou terceira opção **"Este mês"** (persistência em `localStorage`)
- Card "Pode gastar" na visão mensal mostra o total seguro pra gastar no resto do mês, sem dividir por dias
  restantes (diferente da visão "Hoje"), com legenda própria: "saldo + a receber − contas a pagar do mês"
- Novo grid de 4 stats na visão mensal: Entrou no mês, Saiu no mês, **Gastos recorrentes** (novo), Contas
  pendentes
- `DashboardController::calcularGastosRecorrentes()`: soma normalizada mensal de todas as `bills` marcadas
  como recorrentes. Deduplica com `unique('descricao')` pegando só a ocorrência mais recente de cada conta
  (uma recorrente paga gera automaticamente a próxima linha, criando histórico duplicado por descrição) e
  normaliza pra equivalente mensal (semanal ×52/12, anual ×1/12, mensal ×1)
- **Bug real de schema drift corrigido**: migration original de `bills` nunca criou `updated_at`, mas o
  banco de produção já tinha essa coluna (drift de um import SQL anterior via phpMyAdmin, divergente do
  arquivo de migration commitado). Isso quebrava `Bill::create()` com `SQLSTATE[42S22]` em qualquer ambiente
  criado do zero a partir das migrations (local, staging, ou um futuro `migrate:fresh`) — só não quebrava em
  produção por acidente do drift. Corrigido com `database/migrations/2026_07_02_183440_add_updated_at_to_bills_table.php`,
  que usa `Schema::hasColumn('bills','updated_at')` como guarda: cria a coluna onde falta, não faz nada
  (só marca como rodada) onde já existe via drift
- QA aprovado em 2 rodadas
- **Deploy em produção concluído**: migration rodada, site validado (home 302, login 200)

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
- **CRÍTICO**: `User::casts()` DEVE incluir `'trial_ends_at' => 'datetime'`. Sem esse cast, o Eloquent
  retorna a coluna como string, e o Cashier (`onGenericTrial()`, chamado internamente por `onTrial()`)
  quebra com erro fatal `Call to a member function isFuture() on string` ao tentar comparar a data. Como
  `EnsureSubscribed` chama `onTrial()` em toda rota protegida, a ausência desse cast derruba o app inteiro
  com HTTP 500 para qualquer usuário em trial. Se esse cast for removido/alterado no futuro por engano,
  este é o sintoma a procurar.
- **Acesso vitalício**: `lifetime_access` fica de fora de `$fillable` de propósito — só pode ser setado por
  código de servidor (`redeem()` via atribuição direta + `save()`), nunca por input de formulário direto,
  pra impedir que um usuário malicioso tente injetar `lifetime_access=1` num POST qualquer
- `LIFETIME_ACCESS_CODE` é um segredo de infraestrutura (como `STRIPE_SECRET`): vive só no `.env` do
  servidor, nunca em código, `ESTADO.md` ou histórico de commit; comparação sempre com `hash_equals()`
- **Gastos recorrentes**: deduplicação por `unique('descricao')` (não por um campo de "grupo recorrente"
  dedicado) — assume que a descrição da conta recorrente não muda entre ocorrências; se isso deixar de ser
  verdade no futuro, revisar `calcularGastosRecorrentes()`
- Migrations que alteram tabelas já existentes em produção devem sempre checar `Schema::hasColumn()` antes
  de adicionar coluna, pois o schema de produção pode ter divergido do arquivo de migration commitado
  (drift histórico via import SQL manual no phpMyAdmin) — ver bug do `updated_at` em `bills` (V6)

---

## PENDÊNCIAS / BLOQUEIOS
Nenhuma pendência de Stripe — setup manual concluído em 2026-07-02 (ver HISTÓRICO).

---

## QA — Último resultado (2026-07-02)
- Dashboard: visão mensal + gastos recorrentes aprovados em 2 rodadas de QA
- Pós-deploy: home 302 (redireciona pra login sem sessão), login 200

## QA — Resultado anterior (2026-07-01)
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

### 2026-07-02 — Dashboard: visão mensal + gastos recorrentes + fix de schema drift em `bills`
- Terceira opção "Este mês" no toggle do dashboard (além de Hoje/Esta semana)
- Card "Pode gastar" na visão mensal: total seguro sem dividir por dias restantes, legenda própria
- Grid de 4 stats mensais, incluindo novo card "Gastos recorrentes" (`calcularGastosRecorrentes()`),
  que normaliza contas recorrentes (semanal/mensal/anual) pra equivalente mensal, deduplicando por descrição
- Bug de schema drift encontrado em QA: `bills.updated_at` faltava no arquivo de migration mas existia em
  produção por drift de import SQL manual antigo; `Bill::create()` quebrava com `SQLSTATE[42S22]` em
  qualquer ambiente criado do zero. Corrigido com migration nova usando `Schema::hasColumn()` como guarda
- QA aprovado em 2 rodadas; deploy e migration aplicados em produção; site validado (302/200)

### 2026-07-02 — Feature: código de resgate para acesso vitalício (bypass da assinatura Stripe)
- Nova coluna `users.lifetime_access` (boolean, default false) via migration
- `EnsureSubscribed` libera acesso quando `lifetime_access = true`, sem depender de Stripe
- `BillingController::redeem()` (`POST /assinatura/resgatar`) valida código fixo do `.env`
  (`LIFETIME_ACCESS_CODE`) com `hash_equals()` e ativa o acesso vitalício do usuário logado
- `billing/index.blade.php`: novo estado visual "Acesso vitalício ativado" (prioridade sobre os outros
  3 estados) + formulário discreto de resgate nos estados que ainda pedem assinatura
- Deployado e configurado em produção: migration rodada, `.env` de produção com o código real (não
  documentado em nenhum arquivo do repo, só informado ao usuário fora do código)
- A pedido do usuário, `trial_ends_at` da conta `andrexster@gmail.com` foi forçado pra ontem, de propósito,
  pra ele testar a tela de paywall/bloqueio na prática antes de resgatar o próprio código
- QA aprovado

### 2026-07-02 — HOTFIX CRÍTICO pós-deploy: cast de `trial_ends_at` ausente derrubava app com 500 pra usuários em trial
- **Como foi encontrado**: teste de ponta a ponta do fluxo de trial em produção real (registro via HTTP real
  com usuário de teste descartável, criado e removido logo depois — sem rastro em produção). O registro
  funcionou, mas o dashboard e `/assinatura` retornaram 500.
- **Causa raiz**: `app/Models/User.php` não tinha `trial_ends_at` no método `casts()`. Sem o cast, o Eloquent
  devolvia a coluna como string em vez de instância `Carbon`, e o Cashier (`onGenericTrial()`, chamado
  internamente por `onTrial()`) quebrava com `Call to a member function isFuture() on string`. Como o
  middleware `EnsureSubscribed` chama `onTrial()` em toda rota protegida do app, isso derrubava com HTTP 500
  qualquer usuário em trial — incluindo os 3 usuários reais graduados com trial via `users:grant-trial` no
  deploy de hoje mesmo, que ficaram bloqueados por um período curto até a detecção.
- **Correção**: adicionado `'trial_ends_at' => 'datetime'` ao array de `casts()` em `app/Models/User.php`
  (uma linha). Deploy do fix imediato (scp do arquivo + `config:clear`/`config:cache` em produção).
- **Validação pós-fix**: dashboard e `/assinatura` voltaram a 200 pro usuário de teste; trial forçado a
  expirar via tinker confirmou bloqueio correto (302 → `/assinatura`, tela "período gratuito acabou"); os
  3 usuários reais conferidos via tinker (`onTrial()` true sem erro, `trial_ends_at` correto) — nenhum dado
  real perdido, nenhuma conta real afetada além do período curto de instabilidade, já sanado.
- Registrado como decisão técnica permanente (ver seção DECISÕES) para não se repetir se o cast for
  removido por engano no futuro.

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

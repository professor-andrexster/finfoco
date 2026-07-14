# ESTADO DO PROJETO — FinFoco
Última atualização: 2026-07-14 (V20 — atalhos de teclado + modo escuro + micro-animações — deployada em produção)

## STATUS GERAL
**PRODUÇÃO NO AR** em https://finfoco.nexialabs.com.br
Sistema SaaS multi-usuário com autenticação, 13 módulos, parcelamentos e diagnóstico completo aplicado.
**Remodelagem TDAH COMPLETA (fases 1–3)**: de controlador financeiro para assistente completo
para pessoas com TDAH — fase 1 = Agenda, fase 2 = Rotinas com streak, fase 3 = e-mail matinal
"Seu dia hoje" + micro-passos, todas concluídas e deployadas em 2026-07-13.
**V17 (2026-07-13)**: reposicionamento "TDAH como superpoder" — Modo Hiperfoco (/foco) +
landing reescrita com novo posicionamento e SEO ("Seu TDAH não é defeito. É um superpoder sem manual.").
Raiz `/` agora é landing page pública de divulgação (SEO completo); o app vive em `/painel`.
**V18 (2026-07-13)**: 4 integrações — visão semanal da agenda, Google Agenda DENTRO do FinFoco
(import ICS com parser próprio e cache), alertas via Telegram (bot + webhook) e Web Push
(notificação com navegador fechado), com comando unificado `finfoco:alertas` disparado por
tráfego a cada 60s. Falta só o Andre criar o bot no @BotFather pra ativar o Telegram.
**V19 (2026-07-14)**: UX como maior força — experiências distintas por dispositivo: desktop com
sidebar fixa (grupos MEU DIA / DINHEIRO), mobile com barra inferior de 5 alvos + FAB Lançar +
bottom sheet "Mais"; painel agora abre com o dia (agenda, rotinas, hiperfoco) antes do dinheiro;
agenda em 2 colunas no desktop. Próxima etapa combinada: continuar melhorias de UX/layout.
**V20 (2026-07-14)**: atalhos de teclado no desktop (P/A/S/F/R/L/C/H/E/?/Esc com modal de ajuda
e dicas `<kbd>` na sidebar), modo escuro completo via tokens CSS `--c-*` (toggle persistido em
localStorage + prefers-color-scheme, sem flash; views ganham dark de graça pelas cores foco-*)
e micro-animações (cards surgem, botões :active scale, pop nos checks), com
prefers-reduced-motion respeitado — tudo só no layout, nenhuma view individual tocada.
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
- [x] 10. Agenda TDAH (fase 1) — visão do dia, linha do AGORA, alertas no navegador, feed iCal
- [x] 11. Rotinas recorrentes com streak (fase 2 TDAH)
- [x] 12. E-mail matinal Seu dia hoje + micro-passos (fase 3 TDAH)
- [x] 13. Modo Hiperfoco + reposicionamento superpoder TDAH (landing/SEO)
- [x] 14. Integrações: semana + Google Agenda import + Telegram + Web Push
- [x] 15. UX desktop ≠ mobile (sidebar + tab bar + painel centro do dia)
- [x] 16. Atalhos de teclado + modo escuro + micro-animações

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
- **Docroot público REAL**: `~/domains/finfoco.nexialabs.com.br/public_html/` — é dele que os
  arquivos estáticos são servidos (robots.txt, sitemap.xml, index.php, og-image.png etc.).
  O rsync para `~/finfoco/public/` NÃO atualiza os estáticos servidos: deploy de estáticos
  exige rsync direto para esse public_html. Há cache de borda (LiteSpeed/CDN) que pode servir
  versão antiga por um tempo — validar com query string `?v=2`

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
appointments     — id, user_id FK cascade, titulo varchar(80), data date,
                   hora time NULL (null = dia todo), lembrete_min default 30,
                   concluido boolean, timestamps, index (user_id, data)
routines         — id, user_id FK cascade, titulo varchar(80), hora time NULL
                   (null = qualquer hora), dias char(7) default '1111111'
                   (posições seg..dom, '1' = ativo), timestamps, index user_id
routine_checks   — id, routine_id FK cascade, data date, unique(routine_id, data)
appointment_steps — id, appointment_id FK cascade, titulo varchar(80),
                   concluido boolean, timestamps
push_subscriptions — id, user_id, endpoint text, endpoint_hash sha256 unique,
                   p256dh, auth, timestamps
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
- `2026_07_09_132208` — add_is_admin_to_users_table
- `2026_07_13_000001` — create_appointments_table (guarda `Schema::hasTable`)
- `2026_07_13_000002` — create_routines_tables (routines + routine_checks, guardas `Schema::hasTable`)
- `2026_07_13_000003` — create_appointment_steps_table (guarda `Schema::hasTable`)
- `2026_07_13_000004` — create_push_subscriptions_table ([11] Ran em produção)

---

## O QUE FOI CONSTRUÍDO

### V20 — Atalhos de teclado + modo escuro + micro-animações (2026-07-14, commit `d09400a`, deployada em produção)
Tudo em `resources/views/layouts/app.blade.php` — nenhuma view individual tocada.

#### Atalhos de teclado (desktop)
- Teclas: P (Painel), A (Agenda), S (Semana), F (Hiperfoco), R (Rotinas),
  L (Lançar), C (Contas), H (Histórico), E (alterna tema), ? (abre/fecha modal
  de ajuda), Esc (fecha modal)
- Ignorados quando o foco está em INPUT/TEXTAREA/SELECT/contentEditable ou com
  meta/ctrl/alt pressionados
- Dicas visuais: `<kbd>` ao lado dos itens da sidebar (P, A, F, R, L, C, H) e
  botão "Atalhos" no rodapé da sidebar; modal `#modal-atalhos` (display
  flex/none via JS puro, overlay clicável, lista completa)

#### Modo escuro
- Sistema de tokens: `:root` com variáveis `--c-bg/card/surface/border/border2/
  text/muted/accent/entrada/saida/alerta/sombra/anel`; classe `.dark` no
  `<html>` troca os valores (paleta escura original do CLAUDE.md: bg #0F0F13,
  card #1A1A22, border #2A2A38, text #F1F5F9; accent clareado pra #818CF8 no
  escuro)
- As cores tailwind `foco-*` agora apontam pra `var(--c-*)` no tailwind.config
  — TODAS as views que usam classes foco-* ganham dark de graça. Nota:
  modificadores de opacidade tipo `bg-foco-accent/80` degradam pra cor cheia
  (aceitável)
- Script inline no `<head>` ANTES do CSS aplica o tema salvo
  (`localStorage.finfoco_tema`) ou o `prefers-color-scheme` — sem flash de
  tema errado
- Toggle: botão "Modo escuro/claro" no rodapé da sidebar (desktop) e no
  dropdown do avatar (mobile); ícones sun/moon trocados via classes
  `.so-claro`/`.so-escuro`
- Overrides p/ cores fixas herdadas das views (sem tocar nas views):
  `.dark .bg-white`, `[style*="#E4E4F0"]`→border-color, `[style*="#F3F3FB"]`,
  `background:#EEF2FF/#E0E7FF`→rgba accent, `#FEF2F2`/`#FFFBEB`→rgba
  vermelho/âmbar, `color:#1E1B4B`→text, `circle[stroke=...]` pro anel do
  timer e logo, `color-scheme: dark` nos inputs date/time
- Views standalone (login/registro/landing) continuam claras — dark é do app
  logado

#### Micro-animações
- `.card { animation: surgir .22s }` (fade + translateY 5px), hover de
  `.card-hover` eleva 1px
- Botões: `transition transform .12s` + `:active scale(.94)`; checks
  circulares (selecionados por `button[title^="Concluir"],
  [title^="Desmarcar"]`) ganham keyframe `pop` (scale 1.18) ao clicar —
  dopamina visual sem tocar nas views
- `@media (prefers-reduced-motion: reduce)` desliga TODAS as
  animações/transições (acessibilidade)

#### QA (tudo passou)
- view:cache OK; 8 telas autenticadas 200; HTML verificado
  (4× finfocoAlternarTema, modal-atalhos, keyframes surgir/pop,
  prefers-reduced-motion, kbd P, tokens --c-bg)

#### Deploy em produção (2026-07-14)
- rsync do layout + view:cache em produção; /painel 200

Checklist binário de aceitação:
- [x] Atalhos navegam e não disparam digitando
- [x] Modal ? com lista completa
- [x] Tema escuro sem flash, persistido, com toggle nos dois layouts
- [x] Cores fixas herdadas cobertas no escuro
- [x] Animações com prefers-reduced-motion respeitado
- [x] Produção no ar

### V19 — UX como maior força: desktop ≠ mobile + painel centro do dia (2026-07-14, commit `6e6c315`, deployada em produção)
Contexto: Andre reclamou que usar no computador parecia "um app de celular
esticado". Pedido: experiências distintas por dispositivo, UX como a maior
força do app. Próxima etapa combinada depois desta: continuar melhorias de
UX/layout.

#### Layout novo (`resources/views/layouts/app.blade.php` reescrito)
- **Desktop (lg+)**: sidebar fixa de 264px (w-64) à esquerda com logo, dois
  grupos de navegação — "MEU DIA" (Painel, Agenda, Hiperfoco, Rotinas) e
  "DINHEIRO" (Lançar, Contas, Histórico, Relatórios, Categorias, Alertas) —
  e rodapé com Configurações, Assinatura (badge TRIAL) e card do usuário com
  logout. Item ativo = classe `.side-active` (bg #EEF2FF + roxo + bold).
  Conteúdo com `lg:pl-64`, wrapper `max-w-6xl px-4 lg:px-10`
- **Mobile (<lg)**: topo mínimo sticky (logo + avatar com dropdown de
  Assinatura/Configurações/Sair) + **barra inferior fixa de 5 alvos**
  (grid-cols-5 h-16): Painel, Agenda, **Lançar como FAB central** (w-14 h-14
  roxo elevado -mt-7 com sombra), Foco, e "Mais" que abre **bottom sheet**
  deslizante (x-transition translate-y-full) com grade 3×2: Rotinas, Contas,
  Histórico, Relatórios, Categorias, Alertas.
  `padding-bottom: env(safe-area-inset-bottom)` (classe .tabbar) e
  `viewport-fit=cover` p/ iPhone. Conteúdo com pb-28 no mobile pra barra não
  cobrir
- Toasts subiram pra z-[70] (acima do sheet z-50). Estilos de input ganharam
  type=time e type=url
- Navbar horizontal antiga (9 itens apertados) foi eliminada

#### Painel = centro do dia (`DashboardController` + `dashboard/index.blade.php`)
- Controller agora carrega: `$compromissosHoje`, `$proximoCompromisso`
  (próximo não concluído com hora >= agora, senão o primeiro sem hora
  pendente), `$rotinasHoje` (com checks de hoje), `$rotinasFeitasHoje`
- View abre com "Bom dia/Boa tarde/Boa noite, {primeiro nome} 👋" + data por
  extenso + link "Abrir agenda", e 3 cards clicáveis ANTES do dinheiro:
  **Agora na agenda** (hora+título do próximo compromisso, "+N para hoje",
  ou "Dia livre 🎈"), **Rotinas de hoje** (x de y feitas + mini barra de
  progresso verde; vazio → CTA "Crie sua primeira"), **Entrar em hiperfoco**
  (ícone zap em fundo roxo). Resto do dashboard financeiro mantido intacto
  abaixo

#### Agenda em 2 colunas no desktop (`agenda/index.blade.php`)
- Wrapper `max-w-2xl lg:max-w-6xl`; grid
  `lg:grid-cols-[minmax(0,1fr)_340px]`: coluna principal = progresso do dia +
  quick-add + timeline com linha do AGORA; coluna lateral = Rotinas do dia +
  botão de alertas do navegador + box Google Agenda. No mobile empilha
  (rotinas agora vêm DEPOIS da timeline — mudança consciente de ordem).
  Estado vazio de rotinas virou card CTA bonito

#### QA (tudo passou)
- view:cache OK; login via HTTP e as 13 telas autenticadas todas 200
  (painel, agenda, agenda/semana, foco, rotinas, configuracoes, contas,
  historico, relatorios, categorias, alertas, lancamento, assinatura);
  markup verificado no HTML (sidebar hidden lg:flex fixed, barra grid-cols-5
  h-16, saudação, 3 cards do dia, side-active ×2, grid 2 colunas da agenda)
- Verificado que as telas de auth são standalone (não usam layouts.app — que
  agora pressupõe usuário logado); billing usa e é autenticado, OK

#### Deploy em produção (2026-07-14)
- rsync 4 arquivos + caches. Smoke produção: /painel 200 (via -L), landing 200

Checklist binário de aceitação:
- [x] Sidebar desktop com grupos e item ativo
- [x] Barra inferior mobile com FAB Lançar e sheet Mais
- [x] Painel abre com o dia (3 cards) antes do dinheiro
- [x] Agenda 2 colunas no desktop, empilhada no mobile
- [x] 13 telas autenticadas renderizando 200
- [x] Deploy em produção OK

### V18 — 4 integrações: visão semanal, Google Agenda dentro do FinFoco, Telegram e Web Push (2026-07-13, commit `b5fcafc`, deployada em produção)

#### 1) Visão semanal
- `AgendaController@semana` + rota GET `/agenda/semana` + view `agenda/semana.blade.php`:
  grade de 7 dias (lg:grid-cols-7), hoje com ring roxo e badge HOJE, compromissos com
  hora, contador de rotinas feitas/total por dia, eventos do Google com marcador "· G",
  navegação semana anterior/próxima, links Dia↔Semana nas duas telas

#### 2) Google Agenda DENTRO do FinFoco (direção que faltava — antes só exportava via iCal)
- Campo "Endereço secreto em iCal" em Configurações (setting `google_ics_url`,
  validação url+https; salvar limpa o cache `gcal_{userId}`)
- `app/Services/GoogleAgendaService.php`: baixa o ICS (Http timeout 6s), cache 15 min,
  parser RFC 5545 (unfold de linhas, VEVENT, DTSTART dia-todo/TZID/UTC-Z, SUMMARY com
  unescape, STATUS:CANCELLED ignorado, EXDATE, RRULE aproximado: DAILY/WEEKLY(BYDAY)/
  MONTHLY/YEARLY com INTERVAL e UNTIL). Nunca lança exceção — falha = lista vazia
- Eventos aparecem: na timeline do dia (li read-only com badge GOOGLE, entram na
  ordenação por hora, na linha do AGORA e nos alertas do navegador com aviso 30min)
  e na visão semanal
- **BUG descoberto e corrigido**: objeto Carbon dentro de array cacheado no driver
  file volta como `__PHP_Incomplete_Class` — guardar datas como string no cache e
  re-parsear

#### 3) Telegram
- `app/Services/TelegramService.php` (sendMessage via Bot API, silencioso sem token)
- `TelegramController`: `conectar` (gera token em setting `telegram_connect_token` e
  redireciona para t.me/{bot}?start={token}), `desconectar`, `webhook`
  (POST `/telegram/webhook/{segredo}`, público, hash_equals com
  `TELEGRAM_WEBHOOK_SECRET`, CSRF exception em bootstrap/app.php; /start {token} →
  grava `telegram_chat_id` via upsert direto — Setting::set usa auth() e webhook não
  tem sessão — e responde confirmação no chat)
- Configurações: card "Alertas no Telegram" (só aparece se `TELEGRAM_BOT_USERNAME`
  configurado) com conectar/desconectar
- `config/services.php`: bloco `telegram` (TELEGRAM_BOT_TOKEN, TELEGRAM_BOT_USERNAME,
  TELEGRAM_WEBHOOK_SECRET)
- **PENDENTE DO ANDRE para ativar**: criar bot no @BotFather, colocar as 3 variáveis
  no .env de produção, rodar config:cache, e registrar o webhook:
  `curl "https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://finfoco.nexialabs.com.br/telegram/webhook/<SEGREDO>"`

#### 4) Web Push (notificação com navegador fechado)
- Pacote `minishlink/web-push` ^9 (composer.json/lock commitados; produção rodou
  `composer install --no-dev --optimize-autoloader`; local sem gmp/bcmath/zip →
  instalar com `--ignore-platform-reqs` e `--prefer-source`; composer local é
  `php ~/composer`)
- Chaves VAPID geradas e adicionadas aos .env local e de produção (backup
  `.env.bak-v18` criado no servidor antes do append). `config/services.php` bloco
  `webpush`
- Migration `2026_07_13_000004_create_push_subscriptions_table.php`:
  push_subscriptions (user_id, endpoint text, endpoint_hash sha256 unique, p256dh, auth)
- `PushController@assinar/desassinar` (fetch JSON, retorna 204 — exceção consciente à
  regra "controller nunca retorna JSON": endpoint de máquina)
- `public/sw.js` (push → showNotification; notificationclick → foca/abre /agenda) —
  **copiado também para o docroot** `~/domains/finfoco.nexialabs.com.br/public_html/sw.js`
  (estáticos!)
- Layout: função global `finfocoAssinarPush()` registra SW e assina quando permissão
  granted (roda no load e após o botão "Ativar alertas" da agenda conceder)

#### 5) Comando unificado finfoco:alertas
- `app/Console/Commands/EnviarAlertas.php`: para usuários com push subscription ou
  telegram_chat_id, avisa compromissos com hora não concluídos dentro da janela
  [hora - lembrete_min, hora] e rotinas com hora não feitas (janela 10 min), dedupe
  diário via `Cache::add("alerta_c{id}_{data}")`, envia push (WebPushService com
  limpeza de assinaturas mortas 404/410) + Telegram
- Disparo: novo `rodarRotinaFrequente('alertas_minuto','finfoco:alertas',60)` no
  AppServiceProvider (terminating, máx 1x/60s, lock)

#### QA local (tudo passou)
- lint em 11 arquivos; migrate; rotas; parser ICS validado via tinker (evento com hora
  TZID, dia-todo, recorrente semanal na segunda certa; cache 2ª chamada OK; amanhã 0;
  próxima segunda 1); agenda renderiza 3 eventos GOOGLE; semana 200; push/assinar 204
  e grava; webhook segredo errado 403, certo 204; finfoco:alertas enviou 1 na janela
  e 0 na repetição (dedupe)
- Armadilha de QA local: testar URL ICS apontando pro próprio `artisan serve`
  deadlocka (single-thread) — aquecer o cache via tinker

#### Deploy em produção (2026-07-13)
- rsync 19 caminhos + composer install + VAPID no .env + migrate [11] Ran + caches +
  sw.js no docroot
- Smoke: /agenda/semana 200 via login, sw.js 200, webhook 403 sem segredo configurado
  (esperado), landing 200, finfoco:alertas rodou (0)

Checklist binário de aceitação:
- [x] Visão semanal em produção
- [x] Import Google Agenda com recorrência e cache funcionando
- [x] Push subscriptions gravando e comando enviando com dedupe
- [x] Webhook Telegram protegido e vinculando chat
- [x] Disparo por tráfego a cada 60s
- [x] Nada existente quebrou

### V17 — TDAH como superpoder: Modo Hiperfoco + landing reposicionada com SEO (2026-07-13, commit `07f74d4`)
Contexto: Andre (que tem TDAH) pediu para reposicionar o FinFoco usando as FORÇAS
do TDAH a favor do usuário, não só compensando déficits. Pesquisa embasou: estudos
associam TDAH a mais criatividade, pensamento divergente e hiperfoco, e usar essas
forças melhora saúde mental e qualidade de vida (fontes: ScienceDaily 2025 sobre
forças psicológicas do TDAH, ADDA sobre hiperfoco).

#### A) Modo Hiperfoco (/foco)
- `app/Http/Controllers/FocoController.php`: index carrega compromissos de hoje
  não concluídos como sugestões
- Rota GET `/foco` (auth+subscribed), item "Foco" (ícone zap) na navbar após Agenda
- `resources/views/foco/index.blade.php`: 3 etapas em Alpine (escolha → foco →
  fim). Escolha: 1 campo "No que você vai focar agora?" + chips dos compromissos
  de hoje + duração 15/25/45 min. Foco: anel SVG gigante (r=88, stroke-dashoffset
  por progresso) com relógio central, título da aba mostra o countdown, botão
  "Parar o foco (sem culpa — recomeçar também é foco)". Fim: 🎉 + notificação do
  navegador + botão verde "Marcar como feito na agenda" (POST concluir se veio de
  compromisso) + "Focar em outra coisa"
- Armadilha evitada: ícones Lucide dentro de `<template x-if>` não renderizam
  (createIcons roda antes do Alpine montar) — usar `x-show` em elemento real

#### B) Landing reposicionada (marketing/home.blade.php reescrita)
- Novo posicionamento: "Seu TDAH não é defeito. É um superpoder sem manual." (H1).
  CTA final: "Ativar meu superpoder"
- Seção #superpoderes: 4 forças mapeadas a ferramentas — Hiperfoco→Modo Hiperfoco,
  Criatividade/pensamento divergente→micro-passos, Energia→rotinas com streak,
  Coragem/espontaneidade→modal anti-impulso. Com disclaimer honesto (não
  romantiza, não substitui tratamento)
- Seção dores→respostas: cegueira temporal→linha do AGORA, esquecimento→lembretes
  em camadas, paralisia→micro-passos, impulso→proteção 10s
- Recursos atualizados (agenda, hiperfoco, rotinas, Google Agenda, e-mail matinal,
  finanças anti-impulso). FAQ novo com 7 perguntas
- SEO: title "FinFoco — O app que transforma seu TDAH em superpoder"; description
  e keywords com "app para TDAH, agenda para TDAH, hiperfoco, cegueira temporal,
  TDAH adulto"; OG/Twitter atualizados; JSON-LD WebApplication com featureList +
  audience "Adultos com TDAH" e FAQPage espelhando o FAQ visível (validados com
  json.loads); mantida google-site-verification
- `public/sitemap.xml`: lastmod 2026-07-13. `public/robots.txt`: Disallow
  /agenda, /rotinas, /foco, /passos, /admin

#### Descoberta de infra IMPORTANTE
O docroot público real da Hostinger é `~/domains/finfoco.nexialabs.com.br/public_html/`
(arquivos estáticos: robots.txt, sitemap.xml, index.php, og-image.png etc.) — o rsync
para `~/finfoco/public/` NÃO atualiza estáticos servidos. Deploy de estáticos = rsync
direto para esse public_html. Há cache de borda (LiteSpeed/CDN) que pode servir versão
antiga por um tempo (validar com `?v=2`).

#### QA (tudo passou)
- view:cache OK; landing local 200 com todas as âncoras/keywords; JSON-LD parseado
  válido (WebApplication + FAQPage); /foco logado 200 com sugestões, chips
  15/25/45, botões
- Produção: landing com 14 menções a "superpoder", /foco 200 (via login),
  robots/sitemap atualizados no docroot

Checklist binário de aceitação:
- [x] Modo Hiperfoco funcional em produção com sugestões do dia
- [x] Item Foco na navbar
- [x] Landing nova no ar com posicionamento superpoder
- [x] JSON-LD válido (WebApplication + FAQPage espelhado)
- [x] robots.txt e sitemap.xml atualizados no docroot real
- [x] Nada existente quebrou

### V16 — E-mail matinal "Seu dia hoje" + micro-passos (fase 3 da remodelagem TDAH, 2026-07-13, commits `bfbe9d3` e `6722e9c`)
Fecha a remodelagem TDAH iniciada na V14 (Agenda) e V15 (Rotinas). Fase 3 =
lembrete externo logo cedo (e-mail) + quebra de tarefas em micro-passos
(estilo Goblin Tools), os dois últimos pilares da pesquisa.

#### A) E-mail matinal "Seu dia hoje"
- `app/Mail/AgendaDoDia.php` + `resources/views/emails/agenda-do-dia.blade.php`
  (HTML puro, mesmo padrão do aviso-vencimentos): saudação "Bom dia", tabela de
  compromissos (hora ou "Dia todo"), tabela de rotinas do dia com streak 🔥,
  botão "Abrir minha agenda", tom acolhedor ("Um passo de cada vez. Você
  consegue. 💜"). Assunto: "☀️ Seu dia hoje — N compromissos e M rotinas"
- `app/Console/Commands/EnviarAgendaDoDia.php` (`finfoco:agenda-do-dia`): um
  e-mail por usuário que tenha compromissos NÃO concluídos ou rotinas agendadas
  hoje; quem não tem nada não recebe; falha de um endereço não bloqueia os demais
- **Disparo**: NÃO há crontab na Hostinger compartilhada — o disparo diário é
  pelo tráfego via `rodarRotinaDiaria()` no `AppServiceProvider::boot()`
  (terminating callback, lock atômico em cache file, 1x/dia). Adicionada a
  linha `rodarRotinaDiaria('agenda_do_dia', 'finfoco:agenda-do-dia')` junto de
  backup_diario e avisos_vencimento

#### B) Micro-passos nos compromissos
- Migration `2026_07_13_000003_create_appointment_steps_table.php` (guarda
  hasTable): `appointment_steps` — id, appointment_id FK cascade, titulo
  varchar(80), concluido boolean, timestamps
- Model `app/Models/AppointmentStep.php`; `Appointment` ganhou relação `steps()`
- `AgendaController`: `storePasso` (valida titulo), `togglePasso`,
  `destroyPasso` — autorização via `$step->appointment->user_id`; index
  eager-loada `with('steps')`
- Rotas (auth+subscribed): POST `/agenda/{appointment}/passos`,
  POST `/passos/{step}/toggle`, DELETE `/passos/{step}`
- View agenda: card do compromisso virou coluna — linha principal + botão
  "Passos" (ícone list-tree; vira chip roxo "N/M" quando há passos) que abre
  painel x-show com os passos (check pequeno com feedback Alpine, excluir com
  x) e formulário inline de 1 campo "Um passo pequeno. Ex.: separar os
  documentos" + botão "Adicionar passo"

#### QA local (tudo passou)
- `php -l` em todos os arquivos; migrate OK; 3 rotas de passos no route:list;
  view:cache OK
- Mailable renderizado via tinker com asserts (saudação, rotina, streak 🔥 2,
  botão, "Dia todo" — todos OK); comando com MAIL_MAILER=log enviou 1 resumo
- Fluxo HTTP logado: criar passo 302, toggle 302, UI mostra chip "1/1" e
  "Separar documentos", concluido=1 no banco

#### Deploy em produção (2026-07-13)
- rsync cirúrgico (9 arquivos da fase + AppServiceProvider),
  `migrate --force` [10] Ran, caches reconstruídos
- `php artisan list` confirma o comando registrado;
  `php artisan finfoco:agenda-do-dia` rodado em produção → "Resumos do dia
  enviados: 0" (esperado, ninguém tem agenda ainda). Landing e /agenda 200

Checklist binário de aceitação:
- [x] Tabela appointment_steps em produção
- [x] Criar/marcar/excluir micro-passo funcionando
- [x] Chip N/M no card do compromisso
- [x] E-mail renderiza com compromissos, rotinas e streak
- [x] Comando só envia pra quem tem algo no dia
- [x] Disparo diário automático via rodarRotinaDiaria (sem cron)
- [x] Nada existente quebrou (landing 200)

### V15 — Rotinas recorrentes com streak (fase 2 da remodelagem TDAH, 2026-07-13, commit `328b20f`)
Continuação da remodelagem TDAH (fase 1 = V14 Agenda). Entrega rotinas/hábitos
recorrentes com recompensa imediata (streak 🔥), pilar de dopamina identificado
na pesquisa.

- **Migration** `2026_07_13_000002_create_routines_tables.php` (guardas
  `hasTable`): tabela `routines` — id, user_id FK cascade, titulo varchar(80),
  hora time nullable (null = qualquer hora), dias char(7) default '1111111'
  (posições seg..dom, '1' = ativo), timestamps, index user_id; tabela
  `routine_checks` — id, routine_id FK cascade, data date,
  unique(routine_id, data)
- **Models**: `app/Models/Routine.php` — scope `doDia(Carbon)` via
  `SUBSTRING(dias, dayOfWeekIso, 1) = '1'`, `agendadaEm()`, `feitaEm()`,
  `streak()` (conta de trás pra frente só dias agendados; hoje ainda pendente
  NÃO quebra a sequência; limite 366 iterações); `app/Models/RoutineCheck.php`
- **Controller** `app/Http/Controllers/RoutineController.php`: index (rotinas
  com checks dos últimos 400 dias), store (validação pt_BR: titulo, hora
  opcional H:i, dias[] 1-7 min 1 — monta a string de 7 chars), destroy, check
  (toggle do dia via firstOrCreate/delete, param `data` com fallback hoje,
  param `voltar=rotinas` decide o redirect)
- **Rotas** (grupo auth+subscribed): GET/POST `/rotinas`,
  POST `/rotinas/{routine}/check`, DELETE `/rotinas/{routine}`
- **View** `resources/views/routines/index.blade.php`: criar rotina com 3
  campos (título, hora opcional, chips circulares S T Q Q S S D com Alpine
  x-model, todos ligados por padrão), lista com check de hoje (círculo
  tracejado quando a rotina não vale hoje), mini-badges dos dias, streak com
  flame roxo foco-accent (amarelo é reservado pra atenção), excluir com
  confirm. Estados vazio/erro definidos
- **Agenda** (`agenda/index.blade.php` + `AgendaController@index`): nova seção
  "Rotinas do dia" acima da timeline com check 1-clique (POST routines.check
  com data do dia visto) e badge de streak; link "Gerenciar rotinas" (ou
  "Criar rotinas diárias" quando não há nenhuma — rotinas NÃO estão na navbar,
  o hub é a agenda); barra de progresso do dia agora soma compromissos +
  rotinas; alertas do navegador incluem rotinas com hora (avisa 10 min antes)
  — ids prefixados 'c'/'r' e chave de dedupe do localStorage agora inclui a
  data (rotinas repetem!)
- **QA local (tudo passou)**: `php -l` nos 4 arquivos PHP; migrate OK;
  route:list 4 rotas; view:cache OK; fluxo HTTP real logado: GET /rotinas 200,
  POST store 302 (rotina "Tomar o remédio" 08:00 todos os dias), POST check
  302 → check no banco, streak=1; com check de ontem adicionado via tinker
  streak=2; GET /agenda 200 com "Rotinas do dia", rotina, flame e progresso
  "2 de 4"
- **Deploy em produção (2026-07-13)**: rsync cirúrgico dos 8 arquivos,
  `migrate --force` rodou ([9] Ran), caches reconstruídos. Smoke: `/rotinas`
  e `/agenda` → 302 login (200 com -L), landing 200

Checklist binário de aceitação:
- [x] Tabelas routines/routine_checks em produção
- [x] Criar/excluir rotina com dias da semana
- [x] Check do dia com toggle e unique por dia
- [x] Streak correto (hoje pendente não quebra)
- [x] Seção Rotinas do dia na agenda com progresso somado
- [x] Alertas do navegador incluem rotinas com hora
- [x] Nada existente quebrou (landing 200, rotas cacheadas)

### V14 — Agenda TDAH (fase 1 da remodelagem, 2026-07-13, commit `faf9f96`)
Contexto: o dono decidiu remodelar o FinFoco de controlador financeiro para
**assistente completo para pessoas com TDAH**. Pesquisa (Tiimo, Morgen,
Lifestack, Goblin Tools, Finch) apontou os pilares: tempo visível (combate a
time blindness), lembretes impossíveis de ignorar, micro-passos, recompensa
imediata, setup mínimo. Roadmap: Fase 1 = Agenda (feita), Fase 2 = Rotinas
recorrentes com streak, Fase 3 = e-mail diário "Seu dia hoje" (reusar cron do
`finfoco:avisar-vencimentos`) + micro-passos em tarefas.

- **Migration** `2026_07_13_000001_create_appointments_table.php`: tabela
  `appointments` — id, user_id FK cascade, titulo varchar(80), data date,
  hora time nullable (null = dia todo), lembrete_min default 30, concluido
  boolean, timestamps, index (user_id, data). Guarda `Schema::hasTable`
- **Model** `app/Models/Appointment.php`: fillable, casts, scope `doDia`
  (dia-todo primeiro, depois por hora), `booted` preenche user_id
- **Controller** `app/Http/Controllers/AgendaController.php`: index (visão do
  dia, `?data=`, `Carbon::parse` com fallback pra hoje), store (validação
  pt_BR, máx 3 campos), concluir (toggle), destroy, e `feed($token)` — feed
  iCal público por token secreto de 40 chars guardado em settings (chave
  `ics_token`, gerado na 1ª visita à agenda). Google Agenda assina a URL
  (Outras agendas → + → Com um URL). Eventos com hora usam
  `DTSTART;TZID=America/Sao_Paulo` + DTEND (+1h); sem hora viram `VALUE=DATE`
  (dia todo)
- **Rotas** em `routes/web.php`: GET/POST `/agenda`,
  POST `/agenda/{appointment}/concluir`, DELETE `/agenda/{appointment}`
  (grupo auth+subscribed); GET `/agenda/feed/{token}.ics` público com
  `where` token `[A-Za-z0-9]{40}`
- **View** `resources/views/agenda/index.blade.php`: navegação
  ontem/hoje/amanhã, barra de progresso do dia (verde, "Dia completo!" com
  party-popper), quick-add 3 campos (título, data, hora opcional — "sem hora,
  vale o dia todo"), timeline com linha do AGORA (`li#linha-agora`
  reposicionada por script a cada 30s, só quando vendo hoje), compromissos
  atrasados com ring amarelo foco-alerta + "passou da hora", check circular
  grande com feedback Alpine < 200ms, alertas do navegador via Notification
  API (botão "Ativar alertas no navegador", avisa `lembrete_min` antes,
  dedupe via localStorage), box "Ver no Google Agenda" com URL do feed +
  botão copiar. Estado vazio acolhedor
- **Navbar**: item "Agenda" (calendar-days) logo após Dashboard em
  `layouts/app.blade.php`
- **QA local (tudo passou)**: `php -l` nos 3 arquivos novos; banco local
  recriado do zero (datadir `/tmp/finfoco_mysql_data` sumiu com reboot —
  precisou de `mysql_install_db` + recriar DB `finfoco` e user `finfoco`);
  migrate OK; route:list com as 5 rotas; feed iCal validado com evento com
  hora e dia-todo; token inválido → 404; `/agenda` sem login → redirect
  login; login com user QA (qa@finfoco.test, lifetime_access=1 só no banco
  LOCAL) → 200 com todos os elementos; POST store → 302 e criou; POST
  concluir → 302 e concluido=1
- **Deploy em produção (2026-07-13)**: rsync cirúrgico dos 6 arquivos,
  `php artisan migrate --force` rodou a migration ([8] Ran), caches
  config/route/view reconstruídos. Smoke: `/agenda` → 302 login (200 com -L),
  feed com token inválido → 404, landing 200

Checklist binário de aceitação:
- [x] Migration appointments em produção
- [x] CRUD de compromissos funciona (criar/concluir/excluir)
- [x] Timeline com linha do AGORA só no dia de hoje
- [x] Alertas do navegador com opt-in
- [x] Feed iCal válido e protegido por token
- [x] Navbar com Agenda
- [x] Nada dos módulos financeiros quebrou (landing 200, rotas cacheadas sem erro)

### V13 — WhatsApp de suporte/vendas + dashboard admin de vendas (2026-07-09)
- **Suporte (usuários logados)**: botão "Falar no WhatsApp" em
  `resources/views/settings/index.blade.php`, número (33) 98465-6356 →
  `https://wa.me/5533984656356`, cor `foco-accent` (roxo — ação principal, não
  `foco-entrada` verde, que tem significado fixo de entrada financeira)
- **Vendas (landing pública)**: link "Falar com vendas" em
  `resources/views/marketing/home.blade.php` (header e rodapé), número (31)
  99279-9787 → `https://wa.me/5531992799787`, visível só pro visitante
  deslogado (`@guest`/`@else` de `@auth`), some pra quem já está autenticado
- **Dashboard admin de vendas**: nova rota `GET /admin/vendas`
  (`AdminController@vendas`), protegida por middleware `admin` novo
  (`app/Http/Middleware/EnsureIsAdmin.php`, alias registrado em
  `bootstrap/app.php`) — barra com 403 quem não tem `is_admin = true`. Mostra
  total de assinantes ativos (Cashier `stripe_status = 'active'`), total de
  usuários em trial ativo e tabela das últimas 20 assinaturas (`with('user')`,
  sem N+1)
- Nova coluna `users.is_admin` (boolean, default false) via migration
  `2026_07_09_132208_add_is_admin_to_users_table.php` — **fora** do
  `$fillable` de `User` (protege contra escalada de privilégio via mass
  assignment)
- `database/seeders/AdminUserSeeder.php`: promove `andrexster@gmail.com` a
  admin SE o usuário já existir no banco (não cria usuário — o admin se
  registra pelo fluxo normal e o seeder só promove depois). Idempotente,
  registrado em `DatabaseSeeder.php`
- QA aprovado localmente
- **Deploy em produção concluído (2026-07-09)**: deploy cirúrgico via rsync SSH
  dos 11 arquivos da feature (sem tocar `.env`, sem rodar `deploy_hostinger.sh`
  inteiro); `php artisan migrate --force` criou a coluna `is_admin` em
  produção; `php artisan db:seed --class=AdminUserSeeder --force` rodado e
  confirmado via tinker que `andrexster@gmail.com` está com `is_admin = true`
  no banco de produção; caches config/route/view limpos e reconstruídos.
  Smoke test em produção: `/admin/vendas` → 302 sem sessão (esperado), landing
  pública 200 com link `wa.me/5531992799787` no HTML, `/configuracoes` → 302
  sem sessão
- **Incremento (2026-07-09): tabela de usuários em trial**, a pedido do dono
  do SaaS pra fazer e-mail marketing direcionado a quem está em trial.
  `AdminController@vendas` agora traz `$usuariosEmTrial` com `get()`
  (name/email/created_at/trial_ends_at) em vez de só `count()`; `$totalEmTrial`
  passou a derivar dessa coleção. Nova tabela em `admin/vendas.blade.php`
  abaixo dos cards existentes (e-mail, nome, data de cadastro, dias em trial,
  data de término do trial), com estado vazio "Nenhum trial ativo no momento".
  Correção pós-QA: `diffInDays()` retornava float longo (Carbon 3 tem modo
  "precise" por padrão) — trocado por `(int) $usuario->created_at->diffInDays(now())`
  pra exibir dias inteiros. QA aprovado.
- **Deploy em produção concluído (2026-07-09, commit `15cd9a0`)**: rsync
  cirúrgico dos 2 arquivos alterados (`app/Http/Controllers/AdminController.php`
  e `resources/views/admin/vendas.blade.php`), sem tocar `.env`; sem migration
  nova (não houve mudança de schema nesta entrega). Caches config/route/view
  limpos e reconstruídos. Smoke test em produção: `/admin/vendas` → 302 sem
  sessão (redireciona pro login, esperado), landing pública → 200.

### V12 — Landing page pública com SEO (2026-07-07)
- `resources/views/marketing/home.blade.php`: landing de divulgação servida na raiz `/`
  (inicialmente autenticado era redirecionado ao painel; corrigido no mesmo dia — landing
  sempre visível, com CTA adaptado via `@auth`, ver bullet da imagem OG abaixo)
- Rota do dashboard movida de `/` para `/painel`, mantendo o nome de rota `dashboard`
  (nenhum `route('dashboard')` quebrou; redirect pós-login continua funcionando)
- SEO técnico: HTML semântico (header/nav/main/section/article/footer, h1 único, FAQ com
  `<details>/<summary>`), meta description, canonical, Open Graph, Twitter Card, JSON-LD
  `WebApplication` (oferta R$ 19,98/mês BRL, 7 dias grátis) + `FAQPage`
- **Bug encontrado e corrigido**: `@context` do JSON-LD colide com a diretiva Blade
  `@context` do Laravel 11 — blocos JSON-LD envolvidos em `@verbatim`
- `public/robots.txt` atualizado: libera a landing, bloqueia rotas privadas (/painel,
  /lancamento, /historico etc.) e aponta o sitemap
- `public/sitemap.xml` estático criado com `/`, `/register` e `/login`
- Conteúdo: hero com CTA "Começar teste grátis de 7 dias", seção problema/TDAH, como
  funciona em 3 passos, 6 recursos, preço R$ 19,98/mês, FAQ com 5 perguntas, CTA final e rodapé
- QA local: HTTP 200 na `/`, JSON-LD validado como JSON, `/painel` sem login → 302 /login,
  sitemap e robots respondem 200
- **Deploy em produção (2026-07-07)**: rota `/` convertida de closure para
  `MarketingController@home` (route:cache do servidor não aceita closures); deploy cirúrgico
  via SSH/SCP (só os 5 arquivos alterados); caches remotos refeitos; QA em produção aprovado
  (landing 200 com JSON-LD, `/painel` → 302 /login, login/register/sitemap 200)
- **Verificação Google Search Console (2026-07-07)**: meta tag `google-site-verification`
  adicionada ao head da landing, deployada (view:clear + view:cache) e confirmada em
  produção via curl — falta só o clique em "Verificar" no console + envio do sitemap
- **Verificação Search Console — método arquivo HTML (2026-07-07)**: propriedade tipo
  "Domínio" só aceita DNS TXT (verificação falhou); migrada para propriedade de Prefixo de
  URL. Criado `public/google899748e972a66c9c.html`, deployado via SCP pro public_html e
  confirmado servindo 200 em produção. A meta tag continua na landing (os dois métodos
  valem pra prefixo de URL)
- **Propriedade VERIFICADA no Search Console (2026-07-07)**: Prefixo de URL
  `https://finfoco.nexialabs.com.br/`, método arquivo HTML — resta enviar o sitemap e
  solicitar indexação da home
- **Imagem OG + correção de UX (2026-07-07)**: `public/og-image.png` (1200×630, 117 KB)
  gerada via script Python/PIL com fonte Inter e identidade FinFoco; `og:image` aponta pro
  PNG (com width/height/alt) e `twitter:card` virou `summary_large_image` — confirmada 200
  em produção. Correção reportada pelo usuário: `/` redirecionava logado direto ao painel
  e ele nunca via a landing; agora `MarketingController@home` sempre renderiza a landing e
  o header mostra "Abrir painel" (CTA do hero vira "Abrir meu painel") via `@auth`/`@else`.
  Deployado na Hostinger com view:clear/view:cache

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

### V11 — Auditoria de consistência (2026-07-05)
Sinais checados: log de produção (7 erros, todos antigos e já corrigidos — zero erro de
usuário desde a V7) e diff completo de schema produção × migrate:fresh local (idênticos a
menos de cosmética tinyint(1)/(4) e ordem de enum).

Correções:
1. Categorias: `withCount` de lançamentos escopado ao usuário (globais somavam de todos)
2. Sugestões de descrição: só manuais (`whereNull bill_id`)
3. **Excluir lançamento de pagamento desfaz o pagamento**: `reverterPagamentoDeConta()`
   devolve `valor_pago` e volta a conta pra pendente (destroy + bulkDestroy). Próxima
   ocorrência de recorrente já criada NÃO é removida (exclusão manual se preciso)
4. `DateHelper::formatarDataRelativa`: >30 dias → data absoluta d/m/Y
5. `welcome.blade.php` morto removido
6. `transactions/create`: Setting::get movido pro controller (view não faz query)

QA: pagamento excluído → conta pendente/pago_em NULL/valor_pago 0 ✔; sugestão não lista
descrição vinda de conta ✔; data 2027 aparece absoluta ✔; contagens de categoria próprias ✔

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

Desde a V20 as cores `foco-*` resolvem via tokens CSS `var(--c-*)` definidos no
layout (`:root` = paleta clara acima; `.dark` no `<html>` = paleta escura
original do CLAUDE.md, accent clareado pra #818CF8).

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
- Raiz `/` é a landing pública; o app fica em `/painel` mas o nome de rota continua `dashboard`
  (todos os `route('dashboard')` do código seguem válidos sem alteração)
- JSON-LD em Blade DEVE ser envolvido em `@verbatim`: `@context` do schema.org colide com a
  diretiva Blade `@context` do Laravel 11 e quebra a renderização silenciosamente
- Rotas NUNCA podem usar closure: produção roda `php artisan route:cache`, que não serializa
  closures — toda rota deve apontar pra Controller (ver conversão da `/` pra `MarketingController@home`)
- **AVISO — `deploy_hostinger.sh` sobrescreve o `.env` de produção** com uma versão local
  desatualizada (sem Stripe/SMTP). Até corrigir o script, preferir deploy cirúrgico via
  SSH/SCP dos arquivos alterados (chave `~/.ssh/finfoco_deploy`, porta 65002) + limpar/refazer
  caches remotos (route/view)
- **Deploy do FinFoco é exclusivamente Hostinger via SSH/SCP** — não conectar o repo a
  plataformas de deploy automático (Vercel/Netlify etc.). Um projeto órfão "finfoco" na
  Vercel (preset Next.js, criado em 13/06/2026) disparava deploy inútil a cada push e foi
  removido em 2026-07-07
- **Admin único via booleano, sem sistema de roles**: `users.is_admin` é um único booleano
  pra um único usuário (o dono do SaaS) — decisão deliberada por ser caso de uso simples,
  não vale criar abstração de permissões/roles pra 1 admin
- `users.is_admin` fora do `$fillable` do Model `User` (mesma lógica de `lifetime_access`):
  só pode ser setado por seeder/tinker direto no banco, nunca por mass assignment via form
- Números de WhatsApp: suporte (33) 98465-6356 (usuários logados, em Configurações), vendas
  (31) 99279-9787 (landing pública, só visitante deslogado)
- **Remodelagem TDAH**: FinFoco vira assistente completo para pessoas com TDAH, não só
  controlador financeiro. Pilares (pesquisa em Tiimo, Morgen, Lifestack, Goblin Tools,
  Finch): tempo visível (combate a time blindness), lembretes impossíveis de ignorar,
  micro-passos, recompensa imediata, setup mínimo
- **Feed iCal por token secreto** (não por auth): Google Agenda assina URLs públicas sem
  login — segurança vem do token de 40 chars aleatórios guardado em `settings`
  (chave `ics_token`), com constraint de rota `[A-Za-z0-9]{40}` e 404 pra token inválido
- Agenda: `hora` nullable significa compromisso de dia todo (no iCal vira `VALUE=DATE`;
  com hora vira `DTSTART;TZID=America/Sao_Paulo` + DTEND de +1h)
- **Armadilha Blade**: `@json(...)` com expressão multilinha quebra o compilador do Blade
  ("Unclosed '['") — construir a coleção num bloco `@php` e passar `@json($variavel)`
- **Armadilha shell**: `pkill -f "artisan serve"` mata o próprio shell do Claude Code
  (o padrão casa com a linha de comando dele) — usar `pgrep` antes pra mirar o PID certo
- **Armadilha shell (complemento V15)**: `pkill -f`/`pgrep -f` com padrão que aparece na
  própria linha de comando mata o shell do Claude Code (exit 144) — usar o truque do
  colchete no padrão, ex.: `pgrep -f "serve --por[t]=8899"` (o colchete impede o padrão
  de casar consigo mesmo)
- Rotinas NÃO entram na navbar: o hub delas é a Agenda (seção "Rotinas do dia"); a tela
  `/rotinas` é só gerenciamento, acessada por link contextual na agenda
- Streak usa flame roxo `foco-accent` (não amarelo — amarelo tem significado fixo de atenção)
- `routines.dias` é char(7) '1111111' com posições seg..dom; scope `doDia` resolve via
  `SUBSTRING(dias, dayOfWeekIso, 1) = '1'` direto no SQL
- `streak()`: hoje ainda pendente não quebra a sequência (só dias agendados contam;
  limite de 366 iterações pra evitar loop infinito)
- Dedupe de notificações no localStorage precisa incluir a DATA na chave quando o item
  se repete (rotinas): ids prefixados 'c'/'r' pra compromissos/rotinas não colidirem
- **E-mail matinal sem cron**: Hostinger compartilhada não dá crontab — o disparo diário
  do `finfoco:agenda-do-dia` é pelo tráfego via `rodarRotinaDiaria('agenda_do_dia', ...)`
  no `AppServiceProvider::boot()` (terminating callback + lock atômico em cache file,
  1x/dia), mesmo mecanismo de backup_diario e avisos_vencimento
- `finfoco:agenda-do-dia` só envia pra quem tem compromisso não concluído OU rotina
  agendada no dia; falha de um endereço não bloqueia os demais
- Autorização de micro-passos via relação: `$step->appointment->user_id` (o passo não
  tem user_id próprio — herda do compromisso pai)
- **Docroot real de estáticos na Hostinger**: os arquivos estáticos servidos pelo domínio
  vêm de `~/domains/finfoco.nexialabs.com.br/public_html/` (robots.txt, sitemap.xml,
  index.php, og-image.png etc.) — o rsync para `~/finfoco/public/` NÃO os atualiza.
  Deploy de estáticos = rsync direto para esse public_html. Cache de borda
  (LiteSpeed/CDN) pode servir versão antiga por um tempo — validar com `?v=2`
- **Armadilha Lucide + Alpine**: ícones Lucide dentro de `<template x-if>` não renderizam
  (createIcons roda antes do Alpine montar o template) — usar `x-show` em elemento real
- **Posicionamento de marca (V17)**: "TDAH como superpoder" — a landing usa as FORÇAS do
  TDAH (hiperfoco, criatividade, energia, coragem) mapeadas a ferramentas do app, com
  disclaimer honesto (não romantiza, não substitui tratamento)
- **Armadilha cache file + Carbon (V18)**: objeto Carbon dentro de array cacheado no
  driver file volta como `__PHP_Incomplete_Class` — guardar datas como STRING no cache
  e re-parsear na leitura (GoogleAgendaService)
- Import do Google Agenda via ICS secreto (setting `google_ics_url`), não via OAuth/API:
  parser RFC 5545 próprio com RRULE aproximado (DAILY/WEEKLY/MONTHLY/YEARLY + INTERVAL
  + UNTIL, EXDATE, CANCELLED), cache file 15 min por usuário (`gcal_{userId}`), nunca
  lança exceção — falha de rede/parse = lista vazia (agenda nunca quebra)
- **Setting::set usa auth()** — em contexto sem sessão (webhook do Telegram), gravar
  setting via upsert direto no query builder com o user_id explícito
- Webhook do Telegram protegido por segredo na URL comparado com `hash_equals`
  (`TELEGRAM_WEBHOOK_SECRET`), rota pública com CSRF exception em bootstrap/app.php
- `PushController` retorna JSON/204 — exceção CONSCIENTE à regra "controller nunca
  retorna JSON": é endpoint de máquina (fetch do service worker), não de humano
- `public/sw.js` precisa ser copiado TAMBÉM pro docroot
  `~/domains/finfoco.nexialabs.com.br/public_html/sw.js` (estático, mesma regra do
  robots/sitemap) — e service worker exige escopo servido da raiz
- **Disparo frequente sem cron**: `rodarRotinaFrequente('alertas_minuto','finfoco:alertas',60)`
  no AppServiceProvider (terminating callback, máx 1x/60s, lock em cache) — irmão do
  `rodarRotinaDiaria`, para alertas de compromissos/rotinas por push+Telegram
- Dedupe de alerta enviado: `Cache::add("alerta_c{id}_{data}")` (atômico, 1 aviso por
  item por dia); assinaturas push mortas (404/410) são removidas no envio
- Composer local: binário é `php ~/composer`; local sem gmp/bcmath/zip → instalar
  `minishlink/web-push` com `--ignore-platform-reqs --prefer-source`
- **Armadilha de QA local (V18)**: testar URL ICS apontando pro próprio `artisan serve`
  deadlocka (servidor single-thread se chama e trava) — aquecer o cache via tinker
- **Modo escuro por tokens CSS (V20)**: cores `foco-*` do tailwind.config apontam pra
  `var(--c-*)`; `.dark` no `<html>` troca os valores — views ganham dark sem serem
  tocadas. Trade-off: modificadores de opacidade (`bg-foco-accent/80`) degradam pra
  cor cheia. Script inline no `<head>` ANTES do CSS aplica o tema salvo/preferido
  (sem flash). Cores fixas hardcoded nas views são cobertas por overrides de seletor
  de atributo (`[style*="#..."]`) no layout. Dark é só do app logado — login/registro/
  landing (standalone) seguem claras
- Atalhos de teclado (V20) só disparam fora de INPUT/TEXTAREA/SELECT/contentEditable
  e sem meta/ctrl/alt; micro-animações sempre com `@media (prefers-reduced-motion:
  reduce)` desligando tudo (acessibilidade)

---

## PENDÊNCIAS / BLOQUEIOS
- **Ativação do bot do Telegram (só o Andre pode fazer)**: criar bot no @BotFather,
  colocar `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME` e `TELEGRAM_WEBHOOK_SECRET`
  no .env de produção, rodar `php artisan config:cache` e registrar o webhook:
  `curl "https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://finfoco.nexialabs.com.br/telegram/webhook/<SEGREDO>"`.
  Todo o código já está em produção — sem o bot o card nem aparece em Configurações
- **Próxima etapa combinada**: continuar melhorias de UX/layout focadas em TDAH
  (V19 e V20 já entregues nessa frente)
- **Remodelagem TDAH COMPLETA** — fases 1, 2 e 3 concluídas e deployadas em 2026-07-13;
  timer de foco visual entregue na V17 (Modo Hiperfoco); web push com service worker
  entregue na V18
- Google Search Console: propriedade VERIFICADA (2026-07-07) — falta o usuário enviar o
  `sitemap.xml` no menu Sitemaps e solicitar indexação da home via Inspeção de URL
- Nenhuma pendência de Stripe — setup manual concluído em 2026-07-02 (ver HISTÓRICO).
- Nenhuma pendência de V13 (admin/vendas) — deployada em produção com sucesso em 2026-07-09
  (ver HISTÓRICO).
- Nenhuma pendência de V14 (Agenda) — deployada em produção com sucesso em 2026-07-13
  (ver HISTÓRICO).
- Nenhuma pendência de V15 (Rotinas com streak) — deployada em produção com sucesso em
  2026-07-13 (ver HISTÓRICO).
- Nenhuma pendência de V16 (e-mail matinal + micro-passos) — deployada em produção com
  sucesso em 2026-07-13 (ver HISTÓRICO).
- Nenhuma pendência de V17 (Modo Hiperfoco + landing superpoder) — deployada em produção
  com sucesso em 2026-07-13 (ver HISTÓRICO).
- V18 deployada em produção com sucesso em 2026-07-13 — única pendência é a ativação do
  bot do Telegram pelo Andre (item acima).
- Nenhuma pendência de V19 (UX desktop ≠ mobile) nem de V20 (atalhos + modo escuro +
  micro-animações) — ambas deployadas em produção em 2026-07-14.

---

## QA — Último resultado (2026-07-14, V20 — atalhos + modo escuro + micro-animações)
- view:cache OK; 8 telas autenticadas 200
- HTML verificado: 4× finfocoAlternarTema, modal-atalhos, keyframes surgir/pop,
  prefers-reduced-motion, kbd P, tokens --c-bg
- Produção pós-deploy: rsync do layout + view:cache; /painel 200

## QA — Resultado anterior (2026-07-13, V18 — 4 integrações)
- Local: lint em 11 arquivos; migrate; rotas; parser ICS validado via tinker (evento
  com hora TZID, dia-todo, recorrente semanal na segunda certa; cache na 2ª chamada
  OK; amanhã 0; próxima segunda 1); agenda renderiza 3 eventos GOOGLE; semana 200;
  push/assinar 204 e grava; webhook segredo errado 403, certo 204; finfoco:alertas
  enviou 1 na janela e 0 na repetição (dedupe)
- Produção pós-deploy: /agenda/semana 200 via login, sw.js 200, webhook 403 sem
  segredo configurado (esperado), landing 200, finfoco:alertas rodou (0)

## QA — Resultado anterior (2026-07-13, V17 Modo Hiperfoco + landing superpoder)
- view:cache OK; landing local 200 com todas as âncoras/keywords
- JSON-LD parseado válido com json.loads (WebApplication com featureList + audience
  "Adultos com TDAH", e FAQPage espelhando o FAQ visível de 7 perguntas)
- /foco logado 200 com sugestões do dia, chips 15/25/45 e botões
- Produção: landing com 14 menções a "superpoder", /foco 200 (via login),
  robots.txt e sitemap.xml atualizados no docroot real

## QA — Resultado anterior (2026-07-13, V16 e-mail matinal + micro-passos)
- `php -l` OK em todos os arquivos; migrate local OK; 3 rotas de passos no
  route:list; view:cache OK
- Mailable renderizado via tinker com asserts (saudação, rotina, streak 🔥 2,
  botão, "Dia todo") — todos OK; comando com MAIL_MAILER=log enviou 1 resumo
- Fluxo HTTP logado: criar passo 302, toggle 302, chip "1/1" na UI, concluido=1 no banco
- Produção pós-deploy: migrate --force [10] Ran, comando registrado no artisan list,
  `finfoco:agenda-do-dia` em produção → "Resumos do dia enviados: 0" (esperado),
  landing e /agenda 200

## QA — Resultado anterior (2026-07-13, V15 Rotinas com streak)
- `php -l` OK nos 4 arquivos PHP; migrate local OK; 4 rotas no route:list; view:cache OK
- Fluxo HTTP real logado: GET /rotinas 200, POST store 302 (rotina "Tomar o remédio"
  08:00 todos os dias), POST check 302 → check no banco, streak=1; com check de ontem
  via tinker → streak=2
- GET /agenda 200 com "Rotinas do dia", rotina, flame e progresso "2 de 4"
- Produção pós-deploy: `/rotinas` e `/agenda` → 302 login (200 com -L), landing 200

## QA — Resultado anterior (2026-07-13, V14 Agenda)
- `php -l` OK nos 3 arquivos novos; migrate local OK; 5 rotas no route:list
- Feed iCal validado (evento com hora e dia-todo); token inválido → 404
- `/agenda` sem login → redirect login; com user QA → 200 com todos os elementos
- POST store → 302 e criou; POST concluir → 302 e concluido=1
- Produção pós-deploy: `/agenda` → 302 login (200 com -L), feed token inválido → 404, landing 200

## QA — Resultado anterior (2026-07-02)
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

### 2026-07-14 — V20: atalhos de teclado + modo escuro + micro-animações — commit d09400a, deployada em produção
- Tudo em `layouts/app.blade.php`, nenhuma view individual tocada
- Atalhos desktop: P/A/S/F/R/L/C/H/E/?/Esc, ignorados digitando em campos ou com
  meta/ctrl/alt; dicas `<kbd>` na sidebar e modal de ajuda `#modal-atalhos`
- Modo escuro: tokens CSS `--c-*` no `:root` + classe `.dark` no `<html>`
  (paleta escura original do CLAUDE.md, accent #818CF8); cores tailwind `foco-*`
  viram `var(--c-*)` — views ganham dark de graça; script inline no `<head>`
  antes do CSS (localStorage `finfoco_tema` + prefers-color-scheme, sem flash);
  toggle na sidebar (desktop) e no dropdown do avatar (mobile); overrides pras
  cores fixas herdadas das views; standalone (login/registro/landing) seguem claras
- Micro-animações: cards com keyframe `surgir`, botões :active scale(.94), `pop`
  nos checks circulares; `prefers-reduced-motion: reduce` desliga tudo
- QA: view:cache OK, 8 telas autenticadas 200, HTML verificado; deploy: rsync do
  layout + view:cache em produção, /painel 200; checklist binário 6/6 (ver seção V20)

### 2026-07-14 — V19: UX desktop ≠ mobile — sidebar + tab bar com FAB + painel centro do dia — commit 6e6c315, deployada em produção
- Layout reescrito: desktop com sidebar fixa 264px (grupos MEU DIA / DINHEIRO);
  mobile com topo mínimo + barra inferior de 5 alvos com FAB Lançar central e
  bottom sheet "Mais"; navbar horizontal antiga eliminada
- Painel abre com o dia (saudação + 3 cards: Agora na agenda, Rotinas de hoje,
  Entrar em hiperfoco) antes do dinheiro; agenda em 2 colunas no desktop
- QA: 13 telas autenticadas 200, markup verificado; deploy rsync 4 arquivos +
  caches, /painel 200; checklist binário 6/6 (ver seção V19)

### 2026-07-13 — V18: 4 integrações — visão semanal, Google Agenda dentro do FinFoco, Telegram e Web Push — commit b5fcafc, deployada em produção
- Visão semanal: /agenda/semana com grade de 7 dias, badge HOJE, rotinas feitas/total
  por dia, eventos do Google com "· G", navegação de semanas e links Dia↔Semana
- Import do Google Agenda via ICS secreto (setting `google_ics_url`):
  GoogleAgendaService com parser RFC 5545 (RRULE aproximado, EXDATE, CANCELLED),
  cache 15 min, nunca lança exceção; eventos entram na timeline do dia (badge
  GOOGLE), nos alertas do navegador e na semana. Bug corrigido: Carbon em cache
  file vira `__PHP_Incomplete_Class` — datas como string no cache
- Telegram: TelegramService + TelegramController (conectar via deep link
  t.me/{bot}?start={token}, desconectar, webhook público protegido por segredo com
  hash_equals; /start grava telegram_chat_id via upsert direto). Card em
  Configurações só aparece com TELEGRAM_BOT_USERNAME configurado
- Web Push: minishlink/web-push ^9, chaves VAPID nos .env (backup .env.bak-v18 no
  servidor), tabela push_subscriptions (migration [11]), PushController
  assinar/desassinar (204, endpoint de máquina), public/sw.js (também copiado pro
  docroot public_html), finfocoAssinarPush() no layout
- Comando unificado finfoco:alertas: push + Telegram pra compromissos com hora na
  janela do lembrete e rotinas com hora (janela 10 min), dedupe diário via
  Cache::add, limpeza de assinaturas mortas; disparo por tráfego via
  rodarRotinaFrequente (1x/60s, lock)
- QA local completo aprovado; deploy: rsync 19 caminhos + composer install + migrate
  [11] Ran + caches + sw.js no docroot; smoke em produção OK
- PENDENTE do Andre: criar o bot no @BotFather, 3 variáveis no .env, config:cache e
  setWebhook (comando registrado em PENDÊNCIAS)

### 2026-07-13 — V17: TDAH como superpoder — Modo Hiperfoco + landing reposicionada com SEO — commit 07f74d4, deployada em produção
- Reposicionamento pedido por Andre: usar as FORÇAS do TDAH a favor do usuário
  (pesquisa: ScienceDaily 2025 sobre forças psicológicas do TDAH, ADDA sobre hiperfoco)
- Modo Hiperfoco em /foco: FocoController + view de 3 etapas em Alpine
  (escolha com chips dos compromissos de hoje e duração 15/25/45 min → foco com
  anel SVG gigante e countdown no título da aba → fim com 🎉, notificação e
  "Marcar como feito na agenda"); item "Foco" (zap) na navbar após Agenda
- Landing reescrita: H1 "Seu TDAH não é defeito. É um superpoder sem manual.",
  seção #superpoderes (4 forças → ferramentas), seção dores→respostas, recursos
  atualizados, FAQ com 7 perguntas, CTA "Ativar meu superpoder"
- SEO novo: title/description/keywords TDAH, OG/Twitter, JSON-LD WebApplication
  (featureList + audience) e FAQPage espelhado — validados; sitemap lastmod
  2026-07-13; robots.txt bloqueia /agenda, /rotinas, /foco, /passos, /admin
- Descoberta de infra: docroot real de estáticos é
  `~/domains/finfoco.nexialabs.com.br/public_html/` (rsync pro ~/finfoco/public/
  não atualiza estáticos servidos); cache de borda pode atrasar — validar com ?v=2
- Armadilha registrada: Lucide dentro de `<template x-if>` não renderiza — usar x-show
- QA local e produção aprovados (landing 200 com 14 "superpoder", /foco 200 logado)

### 2026-07-13 — V16: E-mail matinal "Seu dia hoje" + micro-passos (fase 3 TDAH) — commits bfbe9d3 e 6722e9c, deployada em produção
- Fecha a remodelagem TDAH (fases 1–3 COMPLETAS): lembrete externo logo cedo
  (e-mail matinal) + quebra de tarefas em micro-passos (estilo Goblin Tools)
- E-mail "Seu dia hoje": `AgendaDoDia` Mailable + view HTML pura com tabelas de
  compromissos e rotinas (com streak 🔥), botão "Abrir minha agenda", tom
  acolhedor; comando `finfoco:agenda-do-dia` envia um resumo por usuário que
  tenha algo no dia (quem não tem nada não recebe)
- Disparo diário SEM cron (Hostinger não dá crontab): nova linha
  `rodarRotinaDiaria('agenda_do_dia', 'finfoco:agenda-do-dia')` no
  AppServiceProvider, junto de backup_diario e avisos_vencimento
- Micro-passos: tabela `appointment_steps`, model `AppointmentStep`, relação
  `steps()` em Appointment, 3 rotas (criar/toggle/excluir) no AgendaController
  com autorização via `$step->appointment->user_id`; card do compromisso ganhou
  botão "Passos" (chip roxo N/M), painel x-show e form inline de 1 campo
- QA local completo aprovado (php -l, migrate, route:list, Mailable via tinker
  com asserts, comando com MAIL_MAILER=log, fluxo HTTP de passos com chip 1/1)
- Deploy em produção: rsync cirúrgico (9 arquivos + AppServiceProvider),
  migrate --force [10] Ran, caches reconstruídos, comando confirmado no artisan
  list e rodado em produção ("Resumos do dia enviados: 0", esperado); landing e
  /agenda 200; checklist binário 7/7 (ver seção V16)
- Ideias futuras registradas (não comprometidas): timer de foco visual
  (pomodoro), integração WhatsApp para alertas, web push com service worker

### 2026-07-13 — V15: Rotinas recorrentes com streak (fase 2 TDAH) — commit 328b20f, deployada em produção
- Fase 2 da remodelagem TDAH: rotinas/hábitos recorrentes com recompensa imediata
  (streak 🔥), pilar de dopamina da pesquisa
- Novas tabelas `routines` (titulo, hora nullable, dias char(7) seg..dom) e
  `routine_checks` (unique routine_id+data), migration com guardas hasTable
- Models `Routine` (scope `doDia` via SUBSTRING no SQL, `streak()` que não quebra com
  hoje pendente) e `RoutineCheck`; `RoutineController` (index/store/destroy/check
  toggle com `voltar=rotinas`); 4 rotas auth+subscribed
- View `routines/index.blade.php`: form 3 campos com chips de dias, lista com check de
  hoje, mini-badges, streak com flame roxo foco-accent, estados vazio/erro
- Agenda ganhou seção "Rotinas do dia" (check 1-clique + streak), link contextual pra
  /rotinas (sem item na navbar — hub é a agenda), progresso do dia somando compromissos
  + rotinas, alertas do navegador com rotinas com hora (10 min antes, dedupe por data,
  ids prefixados 'c'/'r')
- Lição registrada em DECISÕES: `pkill -f`/`pgrep -f` com padrão que aparece na própria
  linha de comando mata o shell (exit 144) — usar truque do colchete
  (`pgrep -f "serve --por[t]=8899"`)
- QA local completo aprovado (php -l, migrate, route:list, fluxo HTTP real com store,
  check, streak 1→2, agenda com progresso "2 de 4")
- Deploy em produção: rsync cirúrgico dos 8 arquivos, `migrate --force` ([9] Ran),
  caches reconstruídos; smoke aprovado (/rotinas e /agenda 302→login, landing 200);
  checklist binário 7/7 (ver seção V15)

### 2026-07-13 — V14: Agenda TDAH (fase 1 da remodelagem) — commit faf9f96, deployada em produção
- Decisão do dono: remodelar o FinFoco de controlador financeiro para assistente completo
  para pessoas com TDAH; pesquisa (Tiimo, Morgen, Lifestack, Goblin Tools, Finch) definiu
  os pilares e o roadmap de 3 fases (fase 1 = Agenda, feita; fases 2 e 3 em PENDÊNCIAS)
- Nova tabela `appointments` (migration com guarda `Schema::hasTable`), Model
  `Appointment` (scope `doDia`, booted seta user_id), `AgendaController`
  (index/store/concluir/destroy + `feed($token)` iCal), 5 rotas (4 auth+subscribed +
  feed público com token de 40 chars em settings `ics_token`), view
  `agenda/index.blade.php` (navegação ontem/hoje/amanhã, barra de progresso, quick-add
  3 campos, timeline com linha do AGORA a cada 30s, atrasados com ring foco-alerta,
  check com feedback < 200ms, Notification API opt-in com dedupe em localStorage,
  box "Ver no Google Agenda"), item "Agenda" na navbar
- Lições registradas em DECISÕES: `@json` multilinha quebra o compilador Blade
  (usar `@php` + `@json($variavel)`); `pkill -f "artisan serve"` mata o próprio shell
  do Claude Code (usar pgrep antes)
- QA local completo aprovado (inclusive recriação do banco local, que sumiu com reboot)
- Deploy em produção: rsync cirúrgico dos 6 arquivos, `migrate --force` ([8] Ran),
  caches reconstruídos; smoke test aprovado (agenda 302→login, feed inválido 404,
  landing 200); checklist binário de aceitação 7/7 (ver seção V14)

### 2026-07-09 — Deploy em produção: tabela de usuários em trial em admin/vendas — commit 15cd9a0
- Deploy cirúrgico via rsync SSH dos 2 arquivos alterados (`AdminController.php`
  e `admin/vendas.blade.php`), sem tocar `.env` — sem migration nova, pois
  esta entrega não mudou o schema
- Caches config/route/view limpos e reconstruídos em produção
- Smoke test: `/admin/vendas` → 302 sem sessão (esperado, redireciona pro
  login), landing pública → 200

### 2026-07-09 — Admin/vendas: tabela de usuários em trial ativo (incremento V13)
- Pedido do dono do SaaS: listar quem está em trial pra e-mail marketing direcionado
- `AdminController@vendas`: `$usuariosEmTrial` agora vem de `->get()` (name/email/
  created_at/trial_ends_at) em vez de só `->count()`; `$totalEmTrial` derivado da coleção
- `admin/vendas.blade.php`: nova tabela abaixo dos cards (e-mail, nome, cadastro, dias em
  trial, término do trial), estado vazio "Nenhum trial ativo no momento"
- Correção pós-QA: `diffInDays()` devolvia float longo (Carbon 3 modo "precise" por padrão);
  trocado por `(int) $usuario->created_at->diffInDays(now())`
- QA aprovado

### 2026-07-09 — Deploy em produção: WhatsApp de suporte/vendas + dashboard admin de vendas (V13)
- Nenhum arquivo de código mudou desde o commit `080a273` (a correção de cor já
  estava incluída nele) — esta entrada documenta só a operação de deploy
- Deploy cirúrgico via rsync SSH dos 11 arquivos da feature, sem tocar `.env`
  e sem rodar `deploy_hostinger.sh` inteiro
- `php artisan migrate --force` em produção: coluna `is_admin` criada na
  tabela `users`
- `php artisan db:seed --class=AdminUserSeeder --force` em produção:
  confirmado via tinker que `andrexster@gmail.com` está com `is_admin = true`
  no banco de produção
- Caches `config`, `route`, `view` limpos e reconstruídos em produção
- Smoke test em produção: `/admin/vendas` → 302 sem sessão (esperado,
  redireciona pro login), landing pública → 200 com link
  `wa.me/5531992799787` presente no HTML, `/configuracoes` → 302 sem sessão

### 2026-07-09 — WhatsApp de suporte/vendas + dashboard admin de vendas (V13)
- Botão "Falar no WhatsApp" (suporte, (33) 98465-6356) em `settings/index.blade.php`,
  cor `foco-accent`
- Link "Falar com vendas" ((31) 99279-9787) na landing `marketing/home.blade.php`,
  visível só a visitante deslogado
- Nova rota `GET /admin/vendas` (`AdminController@vendas`) protegida por middleware
  `admin` (`EnsureIsAdmin`, 403 se `is_admin` falso) — assinantes ativos, trials ativos,
  últimas 20 assinaturas
- Migration `add_is_admin_to_users_table` (coluna fora do `$fillable`) + seeder
  `AdminUserSeeder` (idempotente, promove `andrexster@gmail.com` se já existir)
- QA aprovado localmente; deploy em produção ainda pendente (ver PENDÊNCIAS)

### 2026-07-07 — Projeto órfão na Vercel removido
- Usuário recebia e-mails de deploy da Vercel; investigação achou um projeto "finfoco" na
  conta professor-andrexster (criado em 13/06/2026, preset Next.js) conectado ao repo
  GitHub — cada push disparava um deploy inútil (FinFoco é Laravel e roda na Hostinger)
- Removido via `vercel project rm finfoco` (CLI autenticada localmente)
- Produção na Hostinger, repo GitHub e demais projetos Vercel do usuário intactos
- Decisão registrada em DECISÕES: deploy exclusivamente Hostinger via SSH/SCP; nunca
  conectar o repo a plataformas de deploy automático

### 2026-07-07 — Imagem OG + landing visível para logados — commit 18f6b25
- `public/og-image.png` criada (1200×630, 117 KB) via script Python/PIL, fonte Inter,
  identidade FinFoco; `og:image` atualizado com width/height/alt; `twitter:card` →
  `summary_large_image`; confirmada servindo 200 em produção — pendência da imagem OG resolvida
- Correção de UX reportada pelo usuário: `/` redirecionava logado ao painel e ele nunca
  conseguia ver a landing. `MarketingController@home` agora sempre renderiza a landing;
  header mostra "Abrir painel" e CTA do hero vira "Abrir meu painel" via `@auth`/`@else`
- Deploy na Hostinger com view:clear/view:cache

### 2026-07-07 — Search Console: propriedade verificada com sucesso
- Propriedade de Prefixo de URL `https://finfoco.nexialabs.com.br/` verificada no Google
  Search Console via método arquivo HTML
- Pendência reduzida a: enviar `sitemap.xml` no menu Sitemaps + solicitar indexação da
  home via Inspeção de URL (opcional: imagem OG 1200×630)

### 2026-07-07 — Search Console: verificação por arquivo HTML (prefixo de URL) — commit 115dabb
- Propriedade criada como "Domínio" só aceita verificação por DNS TXT — falhou; usuário
  migrou para propriedade de Prefixo de URL
- Criado `public/google899748e972a66c9c.html` (conteúdo:
  `google-site-verification: google899748e972a66c9c.html`)
- Deployado via SCP pro public_html da Hostinger; confirmado servindo 200 em produção
- Meta tag `google-site-verification` segue na landing (ambos os métodos valem pra prefixo)
- Pendência restante: usuário clicar em "Verificar" e enviar o sitemap.xml

### 2026-07-07 — Meta tag de verificação do Google Search Console — commit 949923a
- Meta tag `google-site-verification` (token XBiTmaa-B-fDn0VqbYbBKhopfXqkPBJXbFiOkl7ejeU)
  adicionada ao head de `resources/views/marketing/home.blade.php`
- Deployada na Hostinger (view:clear + view:cache OK) e confirmada em produção via curl
- Próximo passo (usuário): clicar em "Verificar" no Search Console e enviar o sitemap

### 2026-07-07 — Deploy da landing page em produção — commit 458e19f
- Rota `/` convertida de closure para `MarketingController@home` (novo
  `app/Http/Controllers/MarketingController.php`): o servidor usa `route:cache`, que não
  aceita rotas com closure
- Deploy cirúrgico via SSH/SCP (chave `~/.ssh/finfoco_deploy`, porta 65002): subiu apenas
  MarketingController.php, routes/web.php, marketing/home.blade.php, robots.txt e sitemap.xml
- `deploy_hostinger.sh` NÃO foi usado: ele sobrescreve o `.env` de produção com versão
  desatualizada (sem Stripe/SMTP) — registrado como aviso em DECISÕES
- Caches remotos: route:clear, view:clear, route:cache, view:cache — todos OK
- QA em produção: `/` 200 com a landing (title + 2 blocos JSON-LD), `/painel` sem login →
  302 /login, `/login` e `/register` 200, `sitemap.xml` 200, `robots.txt` atualizado
- Pendência registrada: Google Search Console + sitemap; opcional imagem OG 1200×630

### 2026-07-07 — Landing page pública com SEO (V12) — commit 8f2ae00
- Nova view `marketing/home.blade.php` servida na raiz `/` para visitantes; autenticado é
  redirecionado ao painel
- Dashboard movido de `/` para `/painel` mantendo o nome de rota `dashboard` (zero quebra)
- SEO completo: HTML semântico, meta description, canonical, OG, Twitter Card, JSON-LD
  WebApplication + FAQPage (envolvidos em `@verbatim` por colisão de `@context` com Blade)
- `robots.txt` bloqueando rotas privadas + `sitemap.xml` estático (/, /register, /login)
- QA local: `/` 200, JSON-LD válido, `/painel` sem login → 302 /login, sitemap/robots 200

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

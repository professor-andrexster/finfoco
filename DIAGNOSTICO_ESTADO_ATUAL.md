# DIAGNÓSTICO DE ESTADO ATUAL — FinFoco
Data: 2026-07-01

---

## ARQUIVOS POR MÓDULO

### Módulo 2 — Lançamento Rápido
- `resources/views/transactions/create.blade.php`
- `resources/views/transactions/edit.blade.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Models/Transaction.php`

### Módulo 3 — Dashboard Visual
- `resources/views/dashboard/index.blade.php`
- `app/Http/Controllers/DashboardController.php`

### Módulo 4 — Categorias
- `resources/views/categories/index.blade.php`
- `resources/views/categories/create.blade.php`
- `resources/views/categories/edit.blade.php`
- `resources/views/categories/_form.blade.php`
- `app/Http/Controllers/CategoryController.php`
- `app/Models/Category.php`

### Módulo 7 — Contas a Pagar/Receber
- `resources/views/bills/index.blade.php`
- `resources/views/bills/create.blade.php`
- `app/Http/Controllers/BillController.php`
- `app/Models/Bill.php`

### Módulo 8 — Lembretes e Avisos no Dashboard
- Widget dentro de `resources/views/dashboard/index.blade.php`
- `app/Http/Controllers/ReminderController.php`
- `app/Models/Reminder.php`

### Módulo 9 — Configurações / Recursos TDAH
- `resources/views/settings/index.blade.php`
- `app/Http/Controllers/SettingController.php`
- `app/Models/Setting.php`

### Helpers e Infraestrutura
- `app/Helpers/DateHelper.php` — `formatarDataRelativa()` + `semaforo()`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`

---

## DIVERGÊNCIAS ENCONTRADAS

### D1 — CRÍTICO | Semáforo: vencimento hoje = amarelo, deveria ser vermelho
**Arquivo:** `app/Helpers/DateHelper.php:28-31`
**Comportamento atual:** `diff=0` retorna `'yellow'` (diff <= 3)
**Especificado em V2:** `🔴 vencida/hoje` — hoje deve ser vermelho
**Impacto:** contas que vencem hoje aparecem com semáforo errado em todo o app (bills + dashboard)
**Correção:** `if ($diff <= 0) return 'red'`

### D2 — MODERADO | Cores fora da paleta no dashboard de avisos
**Arquivo:** `resources/views/dashboard/index.blade.php:21`
**Cores usadas:** `#991B1B` (texto danger), `#92400E` (texto warning)
**Paleta FinFoco:** sem esses tokens — mais próximo seria `text-foco-saida` e `text-foco-alerta`
**Impacto:** inconsistência visual menor; os valores são Tailwind red-800/amber-800

### D3 — MODERADO | Settings: `bg-foco-bg` nos inputs
**Arquivo:** `resources/views/settings/index.blade.php:30,43`
**Problema:** usa `bg-foco-bg` que é o fundo da página (#FFFFFF) nos campos de input — semanticamente incorreto. Deveria ser `bg-white` ou `bg-foco-surface`.
**Impacto visual:** nenhum no tema branco atual; risco se o tema mudar.

### D4 — MODERADO | Histórico: data absoluta sem relativa
**Arquivo:** `resources/views/history/index.blade.php:58`
**Comportamento atual:** exibe só `d/m/Y` (ex: "15/06/2026")
**Especificado em V2 (item 3):** "datas sempre relativas" em todo o app
**Impacto:** quebra a regra TDAH de tornar o tempo visível e relativo

### D5 — MODERADO | Histórico: sem botão de excluir inline
**Arquivo:** `resources/views/history/index.blade.php`
**Comportamento atual:** excluir só existe na tela de edição (`edit.blade.php:95`)
**Impacto:** 2 cliques para excluir, poderia ser 1

### D6 — BAIXO | Visão semanal no dashboard não implementada
**Especificado em V2 (Módulo 9, item 4):** "Alternância dia/semana no dashboard"
**Comportamento atual:** dashboard fixo em visão mensal/diária sem toggle
**Impacto:** funcionalidade TDAH ausente (a semana é mais fácil de processar que o mês)

### D7 — BAIXO | Modal anti-impulso sem countdown
**Arquivo:** `resources/views/transactions/create.blade.php:43-65`
**Nome da feature em V2:** "pensa 10s" — sugere contador regressivo
**Implementação atual:** modal estático sem timer (usuário pode fechar imediatamente)
**Impacto:** a "pausa" não tem duração mínima; a proteção é só psicológica

---

## ESTADO DO QUE EXISTE E FUNCIONA ✅

| Feature | Status | Arquivo |
|---------|--------|---------|
| Modal anti-impulso | ✅ Existe | transactions/create (Alpine x-show) |
| Custo em horas de trabalho | ✅ Existe | transactions/create (computed property) |
| Semáforo contas (parcial) | ⚠️ Bug em "hoje" | DateHelper::semaforo() |
| Safe-to-spend "pode gastar hoje" | ✅ Existe | DashboardController + dashboard/index |
| Semáforo saúde financeira | ✅ Existe | semaforoPodeGastar no dashboard |
| Avisos inteligentes clicáveis | ✅ Existe | DashboardController::gerarAvisos() |
| Widget lembretes | ✅ Existe | dashboard/index (Alpine x-data) |
| Toggle concluído 1-clique | ✅ Existe | reminders.toggle route |
| DateHelper::formatarDataRelativa | ✅ Existe | app/Helpers/DateHelper.php |
| Valor hora + limite_impulso configurável | ✅ Existe | settings/index.blade.php |
| Contas parceladas (todas as parcelas de uma vez) | ✅ Existe | BillController::store() |
| Badge X/Y parcelas + barra progresso | ✅ Existe | bills/index.blade.php |
| marcarPago cria Transaction automática | ✅ Existe | BillController::marcarPago() |
| Recorrente gera próxima ocorrência | ✅ Existe | BillController::marcarPago() |
| Auth Login/Register SaaS | ✅ Existe | Auth/LoginController + RegisterController |
| Multi-tenant user_id em todas as tabelas | ✅ Existe | migration 2024_01_02_000001 |
| Estado vazio encorajador (bills) | ✅ Existe | bills/index.blade.php |

---

## CORES EM USO (auditoria completa)

### Paleta oficial (OK)
| Token | Hex | Usos |
|-------|-----|------|
| foco-bg | #FFFFFF | layout body, cards |
| foco-surface | #F7F7FD | hover, inputs |
| foco-border | #E4E4F0 | bordas |
| foco-entrada | #16A34A | valores positivos |
| foco-saida | #DC2626 | valores negativos, erros |
| foco-alerta | #D97706 | atenção |
| foco-text | #1E1B4B | texto principal |
| foco-muted | #9794B8 | texto secundário |
| foco-accent | #6366F1 | ações, links |

### Cores fora da paleta detectadas
| Hex | Onde | Justificativa |
|-----|------|---------------|
| `#991B1B` | dashboard/index.blade.php:21 | texto danger em aviso — deveria ser foco-saida |
| `#92400E` | dashboard/index.blade.php:21 | texto warning em aviso — deveria ser foco-alerta |
| `#4F46E5` | dashboard/index.blade.php:116 | hover do botão CTA (indigo-700) — aceitável como variante hover |
| `#C4C3D8` | auth/register.blade.php:72 | texto tiny em página standalone — isolado |
| `#6B6B8A` | auth/login.blade.php:63 | label em página standalone — isolado |

### Avaliação
As páginas auth (`login.blade.php`, `register.blade.php`) são standalone com inline styles — cores fora da paleta mas isoladas e sem impacto no app principal.
As cores `#991B1B` e `#92400E` no dashboard afetam uma tela de alta visibilidade.

---

## RISCOS PARA NOVAS MUDANÇAS

1. **DateHelper::semaforo()** — afeta bills, dashboard e qualquer futura tela de vencimentos. Corrigir antes de qualquer feature nova que use semáforo.
2. **Settings view** — usa token semântico errado (`bg-foco-bg` em inputs). Baixo risco atual, mas pode causar regressão visual se a paleta for ajustada.
3. **Sem visão semanal** — se o dashboard crescer, inserir o toggle será mais complexo. Melhor implementar agora.

---

## VEREDICTO: É SEGURO PROSSEGUIR?

**Sim**, com as seguintes correções prioritárias antes de novas features:
1. [CRÍTICO] Corrigir `DateHelper::semaforo()` — hoje = vermelho
2. [MODERADO] Corrigir cores off-palette no dashboard
3. [MODERADO] Histórico: adicionar data relativa + delete inline
4. [BAIXO] Visão semanal no dashboard
5. [BAIXO] Countdown no modal anti-impulso

---

## PRÓXIMAS FEATURES SUGERIDAS (do prompt V2)
- Pausa com countdown de 10s (configurable, não fixo)
- Card de "economia evitada" quando o usuário clica em "Vou esperar"
- Lembrete de contas recorrentes (aviso no dashboard X dias antes)
- Cor da categoria visível no seletor do lançamento

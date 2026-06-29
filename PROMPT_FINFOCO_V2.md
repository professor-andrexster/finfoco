# PROMPT MESTRE — FINFOCO v2 (Aprimoramento Completo)

> Cole este arquivo inteiro para o Claude Code executar.
> Objetivo: evoluir o FinFoco para um controlador financeiro de classe mundial
> para pessoas com TDAH, com nova identidade visual, lembretes no dashboard,
> contas a pagar/receber e recursos baseados em pesquisa sobre TDAH.
>
> Stack do projeto existente: **Next.js 14 (App Router) + Tailwind + Supabase
> (Auth + PostgreSQL + RLS) + TanStack React Query + lucide-react + TypeScript
> strict**, deploy na Vercel em `finfoco.nexialabs.com.br`.
>
> ⚠️ Se o seu projeto local estiver em Laravel/MySQL, troque os termos técnicos
> equivalentes (migration Laravel, Eloquent, Blade, Controller) mantendo as
> mesmas regras de produto e a mesma identidade visual. As features e a UX são
> idênticas em ambos os stacks.

---

## 0. CONTEXTO — POR QUE ESTE APP EXISTE

O FinFoco é para pessoas com TDAH. Pesquisas e produtos de referência mostram
que o TDAH dificulta o controle financeiro por quatro mecanismos centrais:

1. **Impulsividade** — compras por dopamina, sem reflexão. A maioria dos adultos
   com TDAH cita o gasto impulsivo como o maior sabotador do orçamento.
2. **Cegueira temporal (time blindness)** — dificuldade de perceber a passagem
   do tempo e o futuro. "Daqui a uma semana" não é real. Por isso boletos vencem,
   contas são esquecidas e o "ADHD tax" (multas e juros por atraso) aparece.
3. **Função executiva e memória de trabalho fracas** — manter o controle de
   datas, saldos, recibos e categorias é exaustivo. "Eu sei o que fazer, o difícil
   é fazer."
4. **Aversão ao tédio e ao atrito** — qualquer fricção faz o app ser abandonado.
   "O melhor app é o que você realmente usa."

### O que os melhores apps do mundo para TDAH fazem (referências)

- **Cake Budget** — sistema de "semáforo" (🟢 pode, 🟡 cuidado, 🔴 segura) e
  feedback imediato: mostra na hora o impacto de gastar agora. Sem sermão, sem
  culpa.
- **Weekly** — um único número "Safe-to-Spend" (pode gastar): tudo já descontado.
  Foco semanal em vez de mensal, porque a semana é mais fácil de visualizar.
- **YNAB** — "dê uma função a cada real": cada dinheiro tem um destino, o que
  reduz decisão impulsiva. Alertas quando passa do limite.
- **HyperJar** — potes coloridos por categoria, gestão 100% visual.
- **Monarch** — contas e assinaturas em visão de **calendário** + lembretes de
  vencimento, para combater a cegueira temporal.

### Princípios de design que o FinFoco DEVE seguir (não negociáveis)

- Uma ação por tela. No máximo 3 campos por formulário.
- Feedback visual em menos de 200ms após qualquer ação.
- Memória zero: tudo que o usuário precisa está visível na tela atual.
- Cores com significado fixo: verde = entrada, vermelho = saída/erro,
  amarelo = atenção. Nunca inverter.
- Botões dizem o que fazem ("Salvar lançamento", nunca "OK").
- Sem culpa: linguagem neutra e encorajadora. Nunca repreender o usuário por
  gastar.
- Tornar o tempo visível: datas relativas ("vence em 3 dias"), contagens,
  semáforos.

---

## 1. IDENTIDADE VISUAL FINFOCO (aplicar em TODAS as telas)

### Logo
Símbolo: alvo concêntrico (foco) — anel externo cinza, anel roxo no meio,
centro verde. Representa "foco no alvo financeiro". Ao lado, o nome **FinFoco**
com "Fin" em branco e "Foco" em roxo. Tagline: *foco no que importa*.

Gerar como `public/logo.svg` exatamente assim:

```svg
<svg width="240" height="64" viewBox="0 0 240 64" xmlns="http://www.w3.org/2000/svg">
  <g transform="translate(32,32)">
    <circle cx="0" cy="0" r="26" fill="none" stroke="#2A2A38" stroke-width="5"/>
    <circle cx="0" cy="0" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
    <circle cx="0" cy="0" r="7" fill="#22C55E"/>
  </g>
  <text x="72" y="40" font-family="Inter, sans-serif" font-size="30" font-weight="700" fill="#F1F5F9">Fin<tspan fill="#6366F1">Foco</tspan></text>
</svg>
```

Também gerar versão só do símbolo como `public/icon.svg` (o alvo, 64x64) para
favicon e ícone do app.

### Paleta (tokens — definir em CSS vars e no tailwind.config)

```
--foco-bg:       #0F0F13   /* fundo escuro, reduz distração */
--foco-surface:  #1A1A22   /* cards */
--foco-border:   #2A2A38   /* bordas sutis */
--foco-entrada:  #22C55E   /* verde — entrada */
--foco-saida:    #EF4444   /* vermelho — saída/erro */
--foco-alerta:   #F59E0B   /* amarelo — atenção */
--foco-text:     #F1F5F9   /* texto principal */
--foco-muted:    #64748B   /* texto secundário */
--foco-accent:   #6366F1   /* roxo — ação principal */
```

### Tipografia
- Fonte: **Inter** (Google Fonts).
- Corpo mínimo: 16px. Botões principais: 18px bold. Títulos de seção: 24px bold.
- Sempre sentence case. Nada de CAPS LOCK.

### Componentes
- Botão primário: bg roxo `#6366F1`, `rounded-xl`, `py-4 px-6`, ícone Lucide +
  texto, fonte 18px bold.
- Cards: bg `#1A1A22`, borda `0.5px #2A2A38`, `rounded-xl`, padding generoso.
- Pílulas de status: entrada (verde), saída (vermelho), alerta (amarelo).
- Estados obrigatórios em todo componente interativo: default, loading (spinner +
  "Salvando..."), erro (vermelho + ícone AlertCircle), sucesso (verde + ícone
  CheckCircle2, some em 2s), vazio (ilustração simples + texto encorajador +
  botão de ação).

> **O site inteiro deve seguir esta identidade visual exatamente.** Nenhuma cor
> fora da paleta. Sem CSS externo. Sem styled-components.

---

## 2. SCHEMA — NOVAS TABELAS (Supabase)

Manter `transactions`, `categories`, `alerts` que já existem. Adicionar:

```sql
-- CONTAS A PAGAR E A RECEBER
CREATE TABLE IF NOT EXISTS bills (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  tipo text NOT NULL CHECK (tipo IN ('pagar','receber')),
  descricao text NOT NULL CHECK (char_length(descricao) <= 60),
  valor numeric(10,2) NOT NULL,
  categoria_id uuid REFERENCES categories(id) ON DELETE SET NULL,
  vencimento date NOT NULL,
  status text NOT NULL DEFAULT 'pendente' CHECK (status IN ('pendente','pago','recebido','atrasado')),
  recorrente boolean NOT NULL DEFAULT false,
  recorrencia text CHECK (recorrencia IN ('mensal','semanal','anual')),
  pago_em date,
  created_at timestamptz NOT NULL DEFAULT now()
);

-- LEMBRETES / AVISOS DO DASHBOARD
CREATE TABLE IF NOT EXISTS reminders (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  titulo text NOT NULL CHECK (char_length(titulo) <= 60),
  data_lembrete date NOT NULL,
  concluido boolean NOT NULL DEFAULT false,
  created_at timestamptz NOT NULL DEFAULT now()
);
```

RLS obrigatório nas duas tabelas (SELECT/INSERT/UPDATE/DELETE com
`auth.uid() = user_id`). Migration idempotente (`IF NOT EXISTS`).
Gerar os tipos TypeScript correspondentes em `types/index.ts`.

---

## 3. NOVOS MÓDULOS A CONSTRUIR

### MÓDULO 7 — Contas a Pagar e a Receber

Página `/contas` (ou duas abas: "A pagar" e "A receber").

Requisitos de produto:
- Lista cada conta com: descrição, valor, **data de vencimento como data
  relativa** ("vence hoje", "vence em 3 dias", "atrasada há 2 dias") + a data
  absoluta abaixo.
- Ordenação automática: vencidas no topo (vermelho), depois as mais próximas.
- Semáforo de vencimento: 🔴 vencida/hoje, 🟡 vence em ≤3 dias, 🟢 mais de 3 dias.
- Botão de 1 clique "Marcar como pago" / "Marcar como recebido" → muda status e,
  ao confirmar, **cria automaticamente uma transação** correspondente (saída para
  contas a pagar, entrada para contas a receber) na data de hoje.
- Formulário de nova conta: máximo 3 campos visíveis por vez (descrição, valor,
  vencimento); categoria e recorrência ficam em "mais opções" recolhido.
- Contas recorrentes: ao marcar como paga, gerar automaticamente a próxima
  ocorrência (mensal/semanal/anual).
- Estado vazio encorajador: "Nenhuma conta cadastrada. Que tranquilidade! 🎯" +
  botão "Adicionar conta".

### MÓDULO 8 — Dashboard com Lembretes e Avisos

A página inicial `/dashboard` deve, no topo, ter uma faixa de **avisos
inteligentes** gerados automaticamente (não exige cadastro manual):

- "Você tem 2 contas vencendo nos próximos 3 dias (R$ 340,00)."
- "Atenção: gasto em Alimentação já passou 80% do limite do alerta."
- "1 conta está atrasada." (vermelho)
- "Tudo em dia por aqui. 🎯" (verde, quando não há nada pendente)

Cada aviso é clicável e leva direto à tela relevante (contas, alertas).

Abaixo dos avisos, manter os cards de saldo já existentes e adicionar:
- **Card "Pode gastar hoje/esta semana"** (inspirado no Safe-to-Spend): saldo
  do mês menos contas a pagar pendentes do mês, dividido pelos dias restantes.
  Mostrar um número grande e claro. Combate cegueira temporal.
- **Semáforo de saúde financeira do dia**: 🟢/🟡/🔴 conforme o usuário esteja
  dentro, perto ou acima do que pode gastar.
- **Lembretes pessoais**: lista de `reminders` com checkbox de 1 clique para
  concluir. Botão "Adicionar lembrete" (1 campo: título + data).

Tudo com feedback imediato e linguagem sem culpa.

### MÓDULO 9 — Recursos extras baseados em pesquisa de TDAH

Implementar os que forem viáveis sem complexidade de stack (sem workers/queues):

1. **Pausa anti-impulso ("pensa 10s")** — ao lançar uma *saída* acima de um
   valor configurável (ex.: R$ 150), mostrar um micro-modal de 1 pergunta:
   "Isso é necessário agora?" com botões "Sim, lançar" e "Vou esperar". Sem
   julgamento — só um respiro para a dopamina. (Estratégia validada: o atraso
   reduz a compra por impulso.)
2. **Custo em horas de trabalho** — campo opcional no perfil para "valor da sua
   hora". Quando preenchido, mostrar embaixo do valor de uma saída: "≈ 4h de
   trabalho". Torna o preço tangível.
3. **Datas sempre relativas** — em todo o app, exibir tempo de forma relativa
   ("há 2 dias", "vence amanhã") além da data absoluta. Criar um util
   `formatarDataRelativa()`.
4. **Visão semanal opcional** no dashboard — alternância dia/semana, porque a
   semana é mais fácil de processar para o cérebro TDAH.
5. **Zero culpa, sempre celebração** — toasts de sucesso curtos e positivos
   ("Lançado! ✅"). Estado vazio sempre encorajador, nunca "Nada aqui".

---

## 4. ORDEM DE EXECUÇÃO (atômica — um passo por vez)

Execute na ordem. Após cada passo, rode o app e verifique antes de seguir.

1. **DADOS** — criar migration `bills` + `reminders` com RLS e índices por
   `user_id` e `vencimento`. Gerar tipos em `types/index.ts`.
2. **DADOS** — funções de acesso em `lib/supabase/bills.ts` e
   `lib/supabase/reminders.ts` (CRUD + `getContasVencendo`, `getAvisos`,
   `getPodeGastar`). Toda função retorna `{ data, error }`, nunca lança exceção.
3. **INTEGRAÇÃO** — hooks React Query: `useBills`, `useCreateBill`,
   `useMarkBillPaid`, `useReminders`, `useAvisos`, `usePodeGastar`. Mutações
   invalidam `['bills']`, `['transactions']`, `['dashboard']`, `['reminders']`.
4. **UI** — aplicar a identidade visual: logo, favicon, tailwind.config com as
   cores `foco-*`, fonte Inter, util `formatarDataRelativa`.
5. **UI** — página `/contas` (Módulo 7) com semáforo, datas relativas e botão
   pagar/receber de 1 clique.
6. **UI** — `/dashboard` (Módulo 8): faixa de avisos, card "pode gastar",
   semáforo, lembretes.
7. **UI/INTEGRAÇÃO** — Módulo 9: pausa anti-impulso, custo em horas, visão
   semanal.
8. **QA** — rodar o checklist completo da seção 5 e corrigir tudo que falhar.

A cada passo, faça commit e push automático com mensagem descritiva.

---

## 5. CRITÉRIO DE ACEITAÇÃO (checklist binário — testar tudo)

### Identidade visual
- [ ] Logo aparece no header e como favicon
- [ ] Nenhuma cor fora da paleta `foco-*` em nenhuma tela
- [ ] Fonte Inter aplicada, corpo ≥ 16px
- [ ] Botões primários: roxo, ícone + texto, rounded-xl

### Contas a pagar/receber
- [ ] Criar conta a pagar e a receber funciona e salva no banco
- [ ] Vencimento exibido como data relativa + absoluta
- [ ] Vencidas aparecem no topo em vermelho
- [ ] Semáforo 🔴🟡🟢 correto conforme proximidade do vencimento
- [ ] "Marcar como pago" cria uma transação de saída automaticamente
- [ ] "Marcar como recebido" cria uma transação de entrada automaticamente
- [ ] Conta recorrente gera a próxima ocorrência ao ser quitada
- [ ] Estado vazio tem mensagem encorajadora + botão de ação

### Dashboard
- [ ] Faixa de avisos mostra contas vencendo, atrasadas e alertas estourados
- [ ] Cada aviso é clicável e leva à tela certa
- [ ] Card "pode gastar" calcula corretamente (saldo − contas pendentes ÷ dias)
- [ ] Semáforo de saúde do dia muda de cor conforme o gasto
- [ ] Lembrete pode ser criado (1 campo) e concluído com 1 clique
- [ ] Quando não há pendência, mostra "Tudo em dia 🎯" em verde

### Recursos TDAH
- [ ] Saída acima do limite dispara o micro-modal anti-impulso
- [ ] Se "valor da hora" estiver preenchido, mostra custo em horas na saída
- [ ] Datas relativas aparecem em todo o app
- [ ] Alternância dia/semana funciona no dashboard
- [ ] Toasts de sucesso são curtos, positivos e somem em 2s

### TDAH / UX (aplicar em toda tela)
- [ ] Ação principal completável em menos de 3 cliques
- [ ] Feedback visual imediato após qualquer ação
- [ ] Cada tela tem um único objetivo principal
- [ ] Nenhum formulário tem mais de 3 campos visíveis de uma vez
- [ ] Nenhum campo obrigatório exige memória de outra tela
- [ ] Linguagem sem culpa em todas as mensagens

### Técnico
- [ ] `npm run build` passa sem erros de TypeScript (strict)
- [ ] RLS ativo em `bills` e `reminders`, com as 4 políticas
- [ ] Migration reexecutável sem erro (IF NOT EXISTS)
- [ ] Nenhuma chamada direta ao Supabase dentro de componentes (só via hook)
- [ ] Sem console.error no browser
- [ ] Deploy na Vercel sobe sem erro em `finfoco.nexialabs.com.br`

---

## 6. INSTRUÇÃO FINAL AO CLAUDE CODE

Execute os passos da seção 4 em ordem, de forma autônoma
(`--dangerously-skip-permissions`). Ao final de cada módulo, rode o app,
verifique o comportamento real (não suponha), aplique o checklist da seção 5
e corrija o que falhar antes de avançar. Faça commit e push a cada passo.

Ao terminar tudo, gere um arquivo `CONTEXTO.md` na raiz documentando:
o estado final, as tabelas criadas, os hooks e rotas adicionados, e qualquer
decisão técnica relevante — para que a próxima conversa do Agente Arquiteto
tenha o estado atualizado.

Mantenha sempre a regra de ouro: **se houver conflito de design, a experiência
TDAH (menos atrito, menos carga cognitiva, sem culpa) vence.**

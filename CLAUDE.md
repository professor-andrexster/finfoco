# CLAUDE.md — FinFoco

Este arquivo é lido automaticamente pelo Claude Code ao abrir o projeto.
Ele define as regras absolutas, a stack e os princípios que NUNCA mudam.

---

## O QUE É O FINFOCO
Controlador financeiro pessoal projetado para pessoas com TDAH.
Prioridade máxima e inegociável: **baixa carga cognitiva**. Em qualquer
conflito de design, a opção TDAH-friendly vence todas as outras.

URL de produção: https://finfoco.nexialabs.com.br

---

## REGRAS ABSOLUTAS
1. Atomicidade total: cada tarefa mexe em UMA camada só
   (migration OU Model OU Controller OU View).
2. Sempre terminar uma entrega com critério de aceitação em checklist binário.
3. Nunca quebrar o fluxo do usuário. Se algo está ambíguo, diagnostique antes.
4. Commit + push automático ao final de cada tarefa concluída.

---

## STACK (NÃO NEGOCIÁVEL)
- Backend: Laravel 11 (PHP 8.2+)
- Frontend: Blade + Tailwind CSS (via CDN)
- JS dinâmico: Alpine.js (via CDN) — declarativo apenas, sem lógica de negócio
- Ícones: Lucide (via CDN unpkg.com/lucide@latest)
- Banco: MySQL (phpMyAdmin na Hostinger)
- Servidor: Hostinger hospedagem compartilhada (Apache + .htaccess)
- Deploy: FTP / Gerenciador de Arquivos + SSH para composer e artisan
- Sem autenticação: app pessoal, acesso direto pela URL
- Sem user_id no schema (single-user)

### Restrições do ambiente compartilhado
- Sem queues, workers ou websockets.
- Sem Redis. Cache driver = `file`.
- `public/` do Laravel deve ser a raiz pública servida pelo domínio.
- `.env` nunca é commitado.

---

## PRINCÍPIOS DE UX TDAH
- Uma ação por tela.
- Feedback visual imediato (< 200ms): cores, x-show/x-transition do Alpine.
- Nenhum dado obrigatório além do essencial. Máximo 3 campos por formulário.
- Botões grandes, texto curto, sempre ícone Lucide + texto.
- Memória zero: nada exige lembrar de etapas anteriores.
- Cores com significado fixo: verde=entrada, vermelho=saída/erro,
  amarelo=atenção. Nunca inverter.
- Texto de botão é sempre verbo específico: "Salvar lançamento",
  nunca "OK"/"Confirmar"/"Enviar".

---

## MÓDULOS (ordem de construção)
1. Setup Laravel + MySQL + Deploy Hostinger
2. Lançamento Rápido (entrada/saída em < 3 cliques)
3. Dashboard Visual (saldo, gastos do dia e da semana)
4. Categorias com Cores e Ícones
5. Alertas Simples (gasto excessivo por categoria)
6. Histórico com Busca Rápida

---

## SCHEMA MYSQL
```sql
CREATE TABLE categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL,
  cor VARCHAR(7) NOT NULL DEFAULT '#6366F1',
  icone VARCHAR(50) NOT NULL DEFAULT 'tag',
  tipo ENUM('entrada','saida','ambos') NOT NULL DEFAULT 'ambos',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('entrada','saida') NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  descricao VARCHAR(60) NOT NULL,
  categoria_id BIGINT UNSIGNED NULL,
  data DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE alerts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id BIGINT UNSIGNED NOT NULL,
  limite_valor DECIMAL(10,2) NOT NULL,
  periodo ENUM('dia','semana','mes') NOT NULL DEFAULT 'mes',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categories(id) ON DELETE CASCADE
);
```
Notas: `tipo` usa 'saida' SEM acento (ENUM MySQL). Migrations replicam este
schema exato. Seeder popula `categories` com 6 categorias coloridas via
`firstOrCreate` (idempotente).

---

## PALETA FINFOCO (tailwind.config inline no layout)
```js
colors: {
  'foco-bg':      '#0F0F13',  // fundo
  'foco-surface': '#1A1A22',  // cards
  'foco-border':  '#2A2A38',  // bordas
  'foco-entrada': '#22C55E',  // verde
  'foco-saida':   '#EF4444',  // vermelho
  'foco-alerta':  '#F59E0B',  // amarelo
  'foco-text':    '#F1F5F9',  // texto
  'foco-muted':   '#64748B',  // texto secundário
  'foco-accent':  '#6366F1',  // ação principal
}
```
Fonte: Inter (Google Fonts). Corpo mínimo 16px. Botão principal 18px bold.
Border-radius padrão 12px.

---

## ESTRUTURA DE PASTAS
```
app/Http/Controllers/   TransactionController, CategoryController,
                        AlertController, DashboardController
app/Models/             Transaction, Category, Alert
database/migrations/
database/seeders/        CategorySeeder
resources/views/layouts/app.blade.php
resources/views/{dashboard,transactions,categories,alerts}/
routes/web.php
public/index.php         raiz do domínio na Hostinger
DEPLOY.md
ESTADO.md                memória viva do projeto (ver agente memoria)
```

---

## ORDEM DE DEPENDÊNCIA (sempre)
Migration → Model → Seeder → Controller + Route → View → QA

---

## AGENTES E SKILLS
Definições completas em `.claude/agents/` e `.claude/skills/`.
- Agentes: arquiteto, estrutura, dados, ui, integracao, qa, memoria
- Skills: prompts-atomicos, design-tdah, diagnostico

Ao concluir QUALQUER tarefa, acione o agente **memoria** para registrar o
que foi feito em `ESTADO.md` e fazer commit + push.

---

## PADRÕES DE CÓDIGO
- Controllers redirecionam com flash após mutação; nunca retornam JSON.
- Models sempre com `$fillable` explícito (nunca `$guarded = []`).
- Relacionamentos declarados nos dois lados.
- Seeders idempotentes (`firstOrCreate`).
- Validação no Controller (`validate()`) ou Form Request, mensagens em pt_BR.
- Blade: feedback de sucesso em verde some em 3s via Alpine x-init+setTimeout;
  erro em vermelho permanece.

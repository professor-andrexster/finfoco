# ESTADO DO PROJETO — FinFoco
Última atualização: 2026-06-28

## STATUS GERAL
V2 completo localmente: 9 módulos implementados, QA 10/10 rotas HTTP 200, SQL exportado para Hostinger.
**Próximo passo: deploy na Hostinger (FTP + phpMyAdmin + SSH para key:generate).**

## MÓDULOS
- [x] 1. Setup Laravel + MySQL + Deploy Hostinger
- [x] 2. Lançamento Rápido (entrada/saída em < 3 cliques)
- [x] 3. Dashboard Visual (saldo, gastos do dia e da semana)
- [x] 4. Categorias com Cores e Ícones
- [x] 5. Alertas Simples (gasto excessivo por categoria)
- [x] 6. Histórico com Busca Rápida
- [x] 7. Contas a Pagar/Receber (Bill + marcarPago + recorrência)
- [x] 8. Lembretes (Reminder + widget no dashboard)
- [x] 9. Configurações (valor_hora + limite_impulso)

## INFRAESTRUTURA
- Domínio: finfoco.nexialabs.com.br (Hostinger compartilhada)
- Laravel 12 + PHP 8.3.6 instalados em /home/andre_gomes/finfoco-claude-code/
- Banco de dev: MariaDB local port 3307 (socket /tmp/finfoco_mysql.sock)
- Banco de prod: MySQL Hostinger — importar finfocoDB_hostinger.sql via phpMyAdmin
- Deploy: pendente. Arquivos ainda não enviados para a Hostinger.

### Restart do banco após reboot (dev)
```
/usr/sbin/mysqld --datadir=/tmp/finfoco_mysql_data --socket=/tmp/finfoco_mysql.sock --port=3307 --pid-file=/tmp/finfoco_mysql.pid --log-error=/tmp/finfoco_mysql.err --user=andre_gomes &
```

## O QUE FOI CONSTRUÍDO

### V1 — Módulos 1 a 6
- Migrations: categories, transactions, alerts
- Models: Category, Transaction, Alert (fillable, relacionamentos, casts)
- CategorySeeder: 6 categorias via firstOrCreate (idempotente)
- Controllers: Dashboard, Transaction, Category, Alert
- Views: dashboard, lançamento, histórico, categorias, alertas
- Design TDAH: toggle visual tipo, feedback verde 3s, erros vermelho permanente

### V2 — Módulos 7 a 9 e Features

#### Módulo 7 — Contas a Pagar/Receber
- `app/Models/Bill.php` — fillable, casts, belongsTo Category
- `app/Http/Controllers/BillController.php` — CRUD + marcarPago (cria Transaction automática + Bill recorrente)
- Migration: bills (id, descricao, valor, tipo, vencimento, categoria_id, recorrente, pago, pago_em, timestamps)
- Views: `resources/views/bills/` — index com semáforo + abas pendentes/pagas, create com dropdown "mais opções"
- Rotas: /contas, /contas/nova, /contas/{id}/pagar, /contas/{id}/editar, /contas/{id}

#### Módulo 8 — Lembretes
- `app/Models/Reminder.php` — fillable, cast boolean
- `app/Http/Controllers/ReminderController.php` — store, toggle, destroy
- Migration: reminders (id, texto, feito, timestamps)
- Widget integrado no dashboard (lista com toggle feito/não-feito)

#### Módulo 9 — Configurações
- `app/Models/Setting.php` — chave-valor, métodos estáticos get(key, default) e set(key, value)
- `app/Http/Controllers/SettingController.php` — index + update
- Migration: settings (id, chave, valor, timestamps)
- View: `resources/views/settings/` — formulário com valor_hora e limite_impulso

#### Features V2
- **Modal anti-impulso**: em transactions/create.blade.php — quando saída > limite_impulso mostra modal "Isso é necessário agora?" com "Vou esperar" / "Sim, lançar" (Alpine.js)
- **Custo em horas de trabalho**: exibe "≈ X horas de trabalho" abaixo do campo valor (Alpine computed, só quando valor_hora configurado e tipo=saida)
- **Semáforo de vencimento**: vermelho=vencida/hoje, amarelo=vence<=3 dias, verde=mais de 3 dias — via DateHelper::semaforo()
- **Avisos inteligentes no dashboard**: DashboardController::gerarAvisos() retorna lista de alertas (contas vencidas, vencendo em 3 dias, limites de categoria)
- **Safe-to-spend**: cálculo "Pode gastar hoje" = (saldo + entradas esperadas - contas pendentes) / dias restantes no mês

#### Helpers
- `app/Helpers/DateHelper.php` — formatarDataRelativa() e semaforo() estáticos
- Autoloaded via composer.json autoload.files

### SQL de Produção
- Arquivo: `/home/andre_gomes/finfoco-claude-code/finfocoDB_hostinger.sql`
- Contém: 7 tabelas (categories, transactions, alerts, bills, reminders, settings, migrations) + seed de 6 categorias
- Banco alvo: finfocoDB | Usuário: finfocoUser

### QA — V2
- 10/10 rotas HTTP 200: /, /lancamento, /contas, /contas/nova, /historico, /categorias, /alertas, /configuracoes, /categorias/nova, /alertas/novo
- 28/29 critérios: 1 falso negativo (setTimeout existe mas não renderiza sem sessão flash)

## DECISÕES TÉCNICAS
- Laravel 12 + PHP 8.3 + Blade + Tailwind/Alpine/Lucide via CDN
- Sem autenticação, sem user_id (app single-user)
- Cache driver = file, Queue = sync (hospedagem compartilhada)
- Timezone: America/Sao_Paulo
- ENUM tipo usa 'saida' SEM acento (MySQL)
- Setting como tabela chave-valor simples (sem JSON, sem arquivo .env de config)
- marcarPago cria Transaction automaticamente e gera próxima Bill se recorrente
- DateHelper como classe de métodos estáticos (não registrado como Facade)

## PENDÊNCIAS / PRÓXIMOS PASSOS
1. Fazer upload dos arquivos via FTP/Gerenciador de Arquivos para a Hostinger
2. Criar banco `finfocoDB` no phpMyAdmin e importar `finfocoDB_hostinger.sql`
3. Configurar `.env` com os dados de produção (template em `.env.hostinger.template`)
4. Rodar `php artisan key:generate` via SSH na Hostinger
5. Ajustar permissões: `chmod 775 storage/ bootstrap/cache/`
6. Verificar public/index.php com caminhos corretos para hospedagem compartilhada

## HISTÓRICO
### 2026-06-28 — Pacote de configuração criado
- Criados CLAUDE.md, 7 agentes, 3 skills, ESTADO.md e prompt de correção de deploy.
- Diagnóstico do site: apenas default.php no servidor; Laravel não enviado.

### 2026-06-28 — Todos os 6 módulos V1 implementados
- Laravel 12 inicializado + composer install
- Migrations, Models, Seeders, Controllers, Rotas e Views criados
- Design TDAH aplicado em todas as views
- DEPLOY.md criado com guia passo a passo para Hostinger

### 2026-06-28 — V2 completo (módulos 7-9 + features TDAH avançadas)
- Módulo 7: Contas a Pagar/Receber com semáforo, marcarPago automático e recorrência
- Módulo 8: Lembretes com widget no dashboard
- Módulo 9: Configurações (valor_hora + limite_impulso)
- Modal anti-impulso em lançamentos (Alpine.js)
- Custo em horas de trabalho no campo valor
- Safe-to-spend no dashboard
- Avisos inteligentes (contas vencidas + limites categoria)
- DateHelper criado e autoloaded
- SQL completo exportado para phpMyAdmin Hostinger (finfocoDB_hostinger.sql)
- QA: 10/10 rotas OK, 28/29 critérios

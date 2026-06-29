═══════════════════════════════════════════
AGENTE: SUBAGENTE ESTRUTURA (foco em ambiente Hostinger)
MÓDULO: 1 — Setup e Deploy
TAREFA: Corrigir site que mostra página padrão da Hostinger
═══════════════════════════════════════════

## CONTEXTO
O domínio https://finfoco.nexialabs.com.br está exibindo a página padrão da
Hostinger ("Está tudo pronto! Tudo o que tem de fazer agora é carregar os
ficheiros do seu site").

Diagnóstico confirmado: o único arquivo presente em `public_html/` é o
`default.php` (placeholder da Hostinger). NENHUM arquivo do projeto Laravel
foi enviado para o servidor. O site não está quebrado por código — ele
simplesmente não existe no servidor ainda.

Tipo de problema (Skill Diagnóstico): TIPO E — Deploy Hostinger.

## OBJETIVO
Preparar o projeto Laravel para deploy correto na Hostinger compartilhada,
onde o domínio aponta para a pasta `public_html`, e gerar o passo a passo
exato para subir os arquivos e remover o placeholder.

## INVESTIGAÇÃO REQUERIDA
1. Confirmar se existe um projeto Laravel local funcional (rodar
   `php artisan serve` e abrir no navegador). Se NÃO existir, este prompt
   depende do MÓDULO 1 completo — gere os prompts do MÓDULO 1 antes.
2. Verificar onde o domínio finfoco.nexialabs.com.br está apontado no
   hPanel da Hostinger (qual pasta é a raiz: `public_html` ou subpasta).
3. Verificar a versão de PHP configurada no hPanel (precisa ser 8.2+).

## RESTRIÇÕES
- NÃO reescrever o app inteiro — o objetivo é deploy, não desenvolvimento.
- NÃO commitar `.env`, `vendor/`, nem `node_modules`.
- Hospedagem compartilhada: sem queues, sem workers, cache driver = file.
- O `public/` do Laravel precisa virar a raiz pública servida pelo domínio.

## ESTRATÉGIA DE DEPLOY (escolher UMA e documentar)

### Opção A — Estrutura recomendada (app fora da web root)
1. Subir TODO o projeto Laravel para uma pasta IRMÃ de `public_html`
   (ex.: `/home/usuario/finfoco_app`).
2. Mover o conteúdo de `finfoco_app/public/` para dentro de `public_html/`.
3. Editar `public_html/index.php` para apontar os require para a pasta
   irmã:
   require __DIR__.'/../finfoco_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../finfoco_app/bootstrap/app.php';

### Opção B — Tudo dentro de public_html (mais simples, menos seguro)
1. Subir o projeto inteiro para `public_html/`.
2. Mover conteúdo de `public_html/public/` um nível acima.
3. Ajustar caminhos no `index.php`.

## ENTREGÁVEIS ESPERADOS
- DELETAR o `default.php` da Hostinger (é o placeholder que está aparecendo).
- Arquivo `DEPLOY.md` atualizado na raiz do projeto com o passo a passo
  exato da Opção escolhida, incluindo:
  - lista de pastas/arquivos a subir via FTP/Gerenciador de Arquivos
  - edição do `index.php`
  - comandos SSH: `composer install --no-dev --optimize-autoloader`,
    `php artisan key:generate`, `php artisan migrate --force`,
    `php artisan config:cache`
  - ajuste de permissões `storage/` e `bootstrap/cache/` para 775
- `.env` de produção com `APP_ENV=production`, `APP_DEBUG=false`,
  `APP_URL=https://finfoco.nexialabs.com.br`, `CACHE_STORE=file`,
  e credenciais MySQL da Hostinger (DB_HOST, DB_DATABASE, DB_USERNAME,
  DB_PASSWORD obtidas no hPanel).
- `.htaccess` correto em `public_html/` para rotear tudo ao `index.php`.

## CRITÉRIO DE ACEITAÇÃO
- [ ] O `default.php` foi removido do servidor.
- [ ] Abrir https://finfoco.nexialabs.com.br carrega o dashboard do FinFoco,
      não a página da Hostinger.
- [ ] Nenhum erro 500 (se houver, ligar APP_DEBUG=true só para depurar e
      desligar depois).
- [ ] `php artisan migrate --force` rodou via SSH e as 3 tabelas existem no
      phpMyAdmin (transactions, categories, alerts).
- [ ] `storage/` e `bootstrap/cache/` estão com permissão 775.
- [ ] Assets (Tailwind/Alpine/Lucide via CDN) carregam sem erro no console.

## PRÓXIMO PASSO SUGERIDO
SUBAGENTE QA: rodar o Checklist PRÉ-DEPLOY Hostinger completo e gerar
relatório APROVADO/REPROVADO antes de considerar o site no ar.
═══════════════════════════════════════════

## NOTA IMPORTANTE PARA O ANDRÉ
Se você ainda NÃO tem um projeto Laravel local pronto, este prompt não tem o
que subir. Nesse caso, abra uma nova conversa no projeto e digite "começar"
para o Agente Arquiteto gerar os prompts do MÓDULO 1 (Setup Laravel + MySQL
+ Deploy). Só depois de o app rodar localmente é que este prompt de deploy
faz sentido.

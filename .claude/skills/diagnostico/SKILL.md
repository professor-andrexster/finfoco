---
name: diagnostico
description: Use quando algo não funciona — "deu erro", "não funcionou", "tá quebrando", site fora do ar, 500, query falhando, tela não atualiza. Classifica o problema por tipo e gera uma correção mínima e cirúrgica, sem refazer o que já funciona.
---

# Diagnóstico e desbloqueio

## Classificação
- TIPO A — Erro PHP/Laravel (500, sintaxe, import) → agente estrutura
- TIPO B — Erro MySQL (query, migration, relacionamento) → agente dados
- TIPO C — Erro visual Blade (layout, classe, Alpine) → agente ui
- TIPO D — Erro de fluxo (POST não salva, redirect, validação) → integracao
- TIPO E — Erro de deploy Hostinger (.htaccess, permissões, .env) → estrutura

## Regra de ouro
Correção mínima e cirúrgica. Não refatorar o que funciona. Não trocar
biblioteca. Não criar tabela nova para contornar — corrigir a existente.
Documentar a causa raiz em comentário.

## Guias rápidos
### TIPO A
- Classe não encontrada → composer dump-autoload
- View não encontrada → conferir nome/pasta em resources/views/
- Rota não encontrada → php artisan route:clear e conferir web.php

### TIPO B
- "Column not found" → migration não rodou ou nome diferente
- "Cannot add foreign key" → ordem de migration (tabela referenciada antes)
- "Integrity constraint" → categoria_id nula em campo NOT NULL

### TIPO C
- Tailwind não aplica → CDN não carregou ou typo na classe
- Alpine não reage → CDN Alpine ausente no layout
- Lucide não aparece → CDN ausente ou lucide.createIcons() não chamado

### TIPO D
- 419 no POST → falta @csrf no formulário
- Redirect em loop → rota de destino aponta para o mesmo Controller
- Validação não aparece → falta @error() / $errors indisponível

### TIPO E
- Página em branco → APP_DEBUG=true temporário para ver o erro
- 500 após deploy → permissões 775 em storage/ e bootstrap/cache/
- Assets não carregam → public/ não é a raiz do domínio no hPanel
- PÁGINA PADRÃO DA HOSTINGER ("Está tudo pronto") → os arquivos do Laravel
  não foram enviados; só existe o default.php. Subir o projeto e apagar o
  default.php.

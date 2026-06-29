---
name: prompts-atomicos
description: Use ao transformar qualquer pedido em linguagem natural em uma ou mais tarefas cirúrgicas e atômicas para o projeto FinFoco. Garante uma responsabilidade por tarefa e ordem correta de dependência.
---

# Geração de prompts atômicos

## Um prompt é atômico quando
- Tem exatamente um entregável principal.
- Mexe em uma única camada: migration OU Model OU Controller OU View.
- É verificável com checklist binário.
- Não deixa decisões em aberto para o Claude Code.

## NÃO é atômico quando
- "criar o formulário e conectar ao banco"
- "fazer o CRUD completo"
- "implementar a feature X"

## Ordem de dependência obrigatória
Migration → Model → Seeder → Controller + Route → View → QA

## Processo
1. Liste as camadas que o pedido toca.
2. Uma camada = uma tarefa. 3 camadas = 3 tarefas separadas.
3. Ordene por dependência.
4. Preencha o template (CONTEXTO, OBJETIVO, RESTRIÇÕES, ENTREGÁVEIS,
   CRITÉRIO DE ACEITAÇÃO, PRÓXIMO PASSO).
5. Adicione uma tarefa de QA ao final e uma chamada ao agente memoria.

## Exemplo — "quero adicionar uma despesa"
1. dados: migration da tabela transactions
2. dados: Model Transaction com fillable e relacionamentos
3. integracao: TransactionController (create + store)
4. integracao: rotas resource em web.php
5. ui: view transactions/create.blade.php (form TDAH-friendly)
6. qa: verificar fluxo completo
7. memoria: registrar e commitar

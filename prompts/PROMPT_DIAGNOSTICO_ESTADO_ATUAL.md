═══════════════════════════════════════════
AGENTE: SUBAGENTE QA
MÓDULO: Transversal (2, 3, 4, 7, 8, 9)
TAREFA: Diagnóstico de estado atual antes de novas alterações
═══════════════════════════════════════════

## CONTEXTO
O usuário fez alterações manuais diretamente pelo terminal do Claude Code, fora do fluxo de prompts gerados pelo Arquiteto. Antes de qualquer novo prompt ser executado, é necessário mapear o estado real do código para evitar colisão com o que já foi mudado.

## OBJETIVO
Auditar e reportar o estado atual dos arquivos relacionados a: lançamento rápido (Módulo 2), dashboard (Módulo 3), categorias (Módulo 4), contas a pagar/receber (Módulo 7), alert banner + safe-to-spend (Módulo 8) e pausa anti-impulso (Módulo 9) — sem alterar nenhum código.

## RESTRIÇÕES
- Não modificar nenhum arquivo neste prompt — apenas ler e reportar
- Não assumir que a documentação (PROMPT_FINFOCO_V2.md ou ESTADO.md) reflete o código real — comparar os dois
- Não sugerir correções ainda — apenas mapear

## INVESTIGAÇÃO REQUERIDA
1. Listar todos os componentes/hooks/funções relacionados aos módulos acima que existem hoje no código (caminho completo de cada arquivo)
2. Para cada um, indicar se o comportamento atual bate com o que está descrito em ESTADO.md / PROMPT_FINFOCO_V2.md, ou se diverge
3. Identificar especificamente: como o modal de pausa anti-impulso (Módulo 9) está implementado hoje — tempo fixo ou variável? é togglable pelo usuário? onde vive esse componente?
4. Identificar como o "safe to spend" (Módulo 8) é calculado e onde é exibido hoje
5. Verificar se há lógica duplicada, componentes órfãos ou nomes de arquivo que não seguem mais o padrão combinado
6. Listar todas as cores/hex codes usados atualmente nos componentes visuais dos módulos acima, para identificar se alguma cor fora da paleta FinFoco já foi introduzida manualmente

## ENTREGÁVEIS ESPERADOS
- Um arquivo `DIAGNOSTICO_ESTADO_ATUAL.md` na raiz do projeto, com:
  - Lista de arquivos por módulo
  - Divergências encontradas entre documentação e código
  - Riscos identificados para receber novas mudanças nesses módulos
  - Lista de cores em uso hoje nesses módulos

## CRITÉRIO DE ACEITAÇÃO
- [ ] Nenhum arquivo de código foi alterado
- [ ] `DIAGNOSTICO_ESTADO_ATUAL.md` foi criado e cobre os 6 módulos listados
- [ ] Cada divergência encontrada está documentada com caminho de arquivo e descrição objetiva
- [ ] O relatório lista as cores em uso hoje nesses módulos
- [ ] O relatório indica claramente se é seguro prosseguir com novas features ou se algo precisa ser alinhado antes

## PRÓXIMO PASSO SUGERIDO
Após o diagnóstico, trazer o `DIAGNOSTICO_ESTADO_ATUAL.md` de volta para esta conversa antes de gerar os prompts das novas features (pausa proporcional, card de economia evitada, lembrete de recorrentes, cor de categoria no lançamento).
═══════════════════════════════════════════

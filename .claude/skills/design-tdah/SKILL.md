---
name: design-tdah
description: Use sempre que gerar ou revisar qualquer interface do FinFoco. Garante baixa carga cognitiva, feedback imediato, campos mínimos e cores com significado fixo. Esta é a prioridade máxima do projeto.
---

# Design TDAH-friendly

## Por que importa
Mente TDAH tem dificuldade com sobrecarga de informação, etapas longas,
feedback tardio e telas que exigem memória de trabalho. O FinFoco deve ser
o sistema mais direto possível — por respeito ao usuário.

## Princípios
1. Uma coisa por vez — cada tela tem um único objetivo.
2. Feedback imediato — resposta visual em < 200ms após qualquer ação.
3. Memória zero — tudo que a ação precisa está visível na tela atual.
4. Campos mínimos — máximo 3 por formulário; o não essencial é opcional.
5. Cores fixas — verde=entrada/confirmação, vermelho=saída/erro,
   amarelo=atenção. Nunca inverter, nunca decorar com essas cores.
6. Botões que dizem o que fazem — verbo + substantivo.

## Textos de botão corretos
- "Salvar lançamento" (não "Salvar")
- "Adicionar categoria" (não "Adicionar")
- "Excluir lançamento" (não "Excluir")
- "Ver histórico completo" (não "Ver mais")
- "Criar alerta" (não "Confirmar")

## Checklist (aplicar em todo prompt de UI)
- [ ] Objetivo único da tela definido
- [ ] Estados visuais especificados (default, erro, sucesso, vazio)
- [ ] Máximo 3 campos declarado
- [ ] Cores com significado correto
- [ ] Texto de botão com verbo específico
- [ ] Comportamento do estado vazio definido

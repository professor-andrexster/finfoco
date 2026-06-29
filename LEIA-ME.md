# FinFoco — Pacote para o Claude Code

## O que está aqui
- CLAUDE.md ................. regras do projeto (lido automaticamente)
- ESTADO.md ................. memória viva do progresso
- .claude/agents/ ........... 7 subagentes
- .claude/skills/ ........... 3 skills
- prompts/PROMPT_CORRECAO_DEPLOY.md ... corrige o site fora do ar

## Como instalar
1. Descompacte esta pasta na RAIZ do seu projeto Laravel local
   (onde fica artisan, composer.json etc).
2. Abra o Claude Code dentro dessa pasta:  claude
3. O CLAUDE.md é carregado sozinho. Os agentes e skills em .claude/ também.

## Como usar
- Iniciar um módulo:  "começar" ou "planejar o módulo 2"  → aciona o arquiteto
- Algo quebrou:       "deu erro: [cole o erro]"           → aciona diagnostico
- Salvar progresso:   "salvar o que foi feito"            → aciona memoria
- Antes de subir:     "rodar QA pré-deploy"               → aciona qa

## O PROBLEMA DO SEU SITE AGORA
O site mostra a página padrão da Hostinger porque os arquivos do Laravel
NUNCA foram enviados — o único arquivo no servidor é o default.php.
Veja prompts/PROMPT_CORRECAO_DEPLOY.md. Mas atenção: só dá para fazer deploy
se você já tiver um projeto Laravel local pronto. Se não tiver, primeiro
digite "começar" para construir o Módulo 1.

# Como usar o Ralph Loop no GoViral (Cursor)

Este projeto usa o **Ralph Loop** adaptado para o **Cursor**: você (humano) inicia cada iteração; o agente do Cursor executa **uma** fase por vez (planejamento ou uma tarefa de construção), atualiza o plano e, no modo Building, commita.

**Não há script em bash.** O “loop” é você rodar o agente de novo quando quiser a próxima tarefa.

---

## Arquivos nesta pasta

| Arquivo | Uso |
|--------|-----|
| `PROMPT_plan.md` | Instruções do modo **Planning**. O agente só lê docs/FDRs/ADRs e código e atualiza o plano. |
| `PROMPT_build.md` | Instruções do modo **Building**. O agente escolhe uma tarefa, implementa, testa, atualiza o plano e commita. |
| Plano (fora desta pasta) | `docs/FDRs/IMPLEMENTATION_PLAN.md` — lista de tarefas priorizadas. **Planning** gera/atualiza; **Building** consome e marca concluído. |

---

## Fluxo geral

1. **Primeira vez ou plano desatualizado**
   - Abra um chat no Cursor (Agent).
   - Cole o conteúdo de `.cursor/ralph/PROMPT_plan.md` ou peça: *“Rode o Ralph em modo Planning”*.
   - O agente analisa `docs/FDRs/ToDo/`, `docs/ADRs/` e o código e preenche/atualiza `docs/FDRs/IMPLEMENTATION_PLAN.md`. Não implementa nada nem commita.

2. **Implementar tarefas (Building)**
   - Abra um chat no Cursor (Agent).
   - Cole o conteúdo de `.cursor/ralph/PROMPT_build.md` ou peça: *“Rode o Ralph em modo Building”* ou *“Faça uma tarefa do Ralph”*.
   - O agente lê o plano, escolhe **uma** tarefa, implementa, roda testes e Pint, atualiza o plano e commita.
   - Se uma FDR inteira ficar pronta (todos os critérios de aceite), o agente move o arquivo da FDR de `docs/FDRs/ToDo/` para `docs/FDRs/Done/`.
   - Para a **próxima** tarefa, inicie **outro** chat e repita (novo contexto = próxima iteração do “loop”).

---

## Regras importantes

- **Uma tarefa por execução** no modo Building. Não peça “várias tarefas” no mesmo chat.
- **Não assumir que algo não existe.** O prompt manda o agente buscar no código antes de concluir que falta implementar.
- **Comandos:** testes e lint devem ser feitos via Sail (veja `.cursor/rules/starting-environment.mdc`).
- **FDRs:** especificações estão em `docs/FDRs/ToDo/`. Quando uma feature estiver completa, o agente move o arquivo da FDR para `docs/FDRs/Done/`.

---

## Onde estão as “specs” e decisões

- **Features (specs):** `docs/FDRs/ToDo/*.md` (uma FDR por feature).
- **Decisões de arquitetura:** `docs/ADRs/*.md`.
- **Produto e design:** `docs/01 - Product Requirement Document.md`, `docs/02 - High Level Design.md`, `docs/04 - Features.md`, `docs/03 - Branding Manual.md`.

O agente usa esses arquivos como fonte da verdade; não há pasta `specs/` separada.

---

## Rules e skills do Cursor

- **Rules:**  
  - `.cursor/rules/ralph-loop.mdc` — workflow do Ralph e uso do plano.  
  - `.cursor/rules/fdr-todo-done.mdc` — FDRs em ToDo e movimento para Done (sempre aplicada).
- **Skill:** `.cursor/skills/ralph-cursor/SKILL.md` — quando usar Planning vs Building e como rodar no Cursor.

Assim a LLM do Cursor sabe onde estão os prompts, o plano e a regra de mover FDRs para Done.

---

## Resumo rápido

| Objetivo | Ação |
|----------|------|
| Gerar ou atualizar o plano | Chat com `PROMPT_plan.md` ou “Ralph Planning”. |
| Fazer uma tarefa e commitar | Chat com `PROMPT_build.md` ou “Ralph Building” / “uma tarefa do Ralph”. |
| Próxima tarefa | Novo chat, de novo com Building. |
| Ver o que falta | Abrir `docs/FDRs/IMPLEMENTATION_PLAN.md`. |

Não é necessário rodar Planning e Building por você; você só inicia o agente com o prompt certo quando quiser planejar ou implementar mais uma tarefa.

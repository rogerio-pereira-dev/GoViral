# ADR-010: Formato de Entrega do Relatório

## Status

Aprovado

## Contexto

O resultado da análise deve ser entregue ao usuário de forma consumível. Opções incluem PDF, HTML em anexo, HTML no corpo do e-mail, link temporário para download ou dashboard.

## Decisão

Entregar o relatório **exclusivamente por e-mail**, no **corpo da mensagem em HTML**. Não gerar PDF no MVP, não armazenar o relatório para acesso posterior e não oferecer link de download.

O e-mail é enviado via AWS SES após a geração do HTML pelo pipeline (LLM + template).

## Consequências

- **Positivas:** Implementação simples (Laravel Mail + view HTML); sem armazenamento de arquivos; entrega direta na caixa de entrada; alinhado à política de “sem histórico”.
- **Negativas:** Usuário pode perder o e-mail; limitado a clientes que renderizem HTML corretamente; não há “segunda via” sem nova compra.
- **Neutras:** Possível evolução pós-MVP (ex.: PDF, link temporário) via nova decisão.

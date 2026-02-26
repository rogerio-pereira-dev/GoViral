# ADR-013: Idiomas Suportados no Relatório

## Status

Aprovado

## Contexto

O público-alvo é global (criadores iniciantes e pequenos). O relatório gerado pelo LLM deve ser entregue no idioma escolhido pelo usuário para manter clareza e adoção.

## Decisão

Suportar **três idiomas** no MVP:

- **en** — English
- **es** — Spanish (Español)
- **pt** — Portuguese (Português)

O idioma é selecionado na landing page, armazenado no campo `locale` da requisição de análise e repassado ao LLM (ex.: placeholder `{{LANGUAGE}}` no template de prompt) para que todo o conteúdo do relatório seja gerado no idioma correspondente.

## Consequências

- **Positivas:** Melhor experiência em mercados EN/ES/PT; decisão explícita no formulário evita ambiguidade para o LLM.
- **Negativas:** Prompt e testes devem cobrir os três idiomas; possível variação de qualidade entre idiomas conforme modelo usado.
- **Neutras:** Inclusão de novos idiomas no futuro é possível via novo valor de `locale` e atualização do prompt/UI.

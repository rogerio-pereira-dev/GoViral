# ADR-014: Integração LLM — Decisão Adiada

## Status

Deferido

## Contexto

O pipeline de análise depende de um provedor de LLM para gerar o conteúdo do relatório (resumo, score, sugestões, plano de 30 dias, etc.). O HLD indica que o provedor e a forma de integração ainda não estão definidos. Candidatos de provedor: OpenAI, Gemini, Anthropic. Abordagens possíveis: (A) adapter/estratégia dentro do Laravel (interface `LlmClient`, implementações por provedor, seleção por variável de ambiente); (B) orquestração externa (ex.: n8n).

## Decisão

**Adiar a decisão final** sobre (1) qual provedor de LLM utilizar e (2) qual abordagem de integração (in-app adapter vs. orquestração externa) até a conclusão de um **spike técnico** e de um **ADR de implementação** específico para a integração LLM.

Até lá, a arquitetura deve permanecer **agnóstica ao provedor** (ex.: interface/contrato no código) de forma que a escolha possa ser feita após comparação de custo, qualidade e operação.

## Consequências

- **Positivas:** Evita comprometimento prematuro com um provedor ou padrão; spike permite validar custo e qualidade antes de implementar.
- **Negativas:** Desenvolvimento da camada de análise (job, template HTML) pode precisar de mock ou implementação temporária até a decisão.
- **Neutras:** Um ADR futuro documentará o provedor escolhido, o padrão de integração (adapter vs. externo) e as convenções de uso (retry, timeouts, formato de saída).

# FDR-007.1: Pesquisa e decisão do provedor de LLM

**Feature:** 7.1  
**Referência:** FDR-007, docs/04 - Features.md, ADR-014

---

## Como funciona

- **Spike técnico:** avaliar provedores candidatos (OpenAI, Gemini, Anthropic) em: custo por requisição, qualidade da saída (adequação ao template do relatório), latência e limites de uso.
- **Pacotes/SDKs:** avaliar pacotes Laravel ou PHP para cada provedor; padrão de integração: adapter/strategy dentro do Laravel (interface comum, ex.: `LlmClient::generateReport(array $payload, string $locale): array`) vs. orquestração externa (ex.: n8n). Escolher abordagem e provedor.
- **Documentação:** produzir **ADR de implementação** com o provedor escolhido e a abordagem (adapter no Laravel ou externo). Atualizar ADR-014 (status e link para o novo ADR) quando a decisão for tomada.
- **Contrato no código:** definir a interface que o Job (FDR-005) usará para chamar a geração do relatório (payload + locale → estrutura de seções), de forma que a implementação possa ser trocada sem alterar o Job.

---

## Como testar

- Comparar custo/requisição e tempo de resposta para um payload típico (ex.: template mínimo) em cada provedor.
- Validar que a interface definida é suficiente para o Job e para o template (docs/LLM Prompt Template.md).
- ADR de implementação revisado e ADR-014 atualizado.

---

## Critérios de aceitação

- [ ] Spike concluído com comparação de custo, qualidade e latência (OpenAI, Gemini, Anthropic).
- [ ] Decisão registrada: provedor e abordagem (adapter Laravel vs. externo).
- [ ] ADR de implementação criado; ADR-014 atualizado (referência ou status).
- [ ] Interface/contrato no código definida (ex.: `generateReport(payload, locale)`); Job depende apenas da interface.

---

## Notas de deployment

- Nenhuma alteração de deploy até a implementação (FDR-007.2 e FDR-007.3); env e keys serão definidos após a decisão.

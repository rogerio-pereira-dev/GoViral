# FDR-007: Integração com LLM

**Feature:** 7 (7.1 Pesquisa/decisão, 7.2 Integração, 7.3 Obter relatório)  
**Referência:** docs/04 - Features.md, ADR-014, docs/LLM Prompt Template.md

---

## Como funciona

**7.1 Pesquisa e decisão (desbloqueio)**
- Spike: avaliar OpenAI, Gemini, Anthropic (custo por request, qualidade, latência). Avaliar pacotes Laravel/SDKs e padrão (adapter no Laravel vs. orquestração externa). Produzir ADR de implementação; atualizar ADR-014 quando decisão for tomada. Definir interface no código (ex.: `LlmClient::generateReport(array $payload, string $locale): array`) para o Job chamar sem depender do provedor.

**7.2 Integração**
- Implementar adapter que implementa a interface; configurar via env (API key, modelo, endpoint). No Job (FDR-005), chamar o adapter com dados de `analysis_requests` e locale; tratar timeout e erros (retry no nível do job — FDR-006).

**7.3 Obter relatório**
- Montar prompt a partir de docs/LLM Prompt Template.md: substituir USERNAME, BIO, NICHE, VIDEO_1, VIDEO_2, VIDEO_3, NOTES, LANGUAGE (locale). Enviar ao LLM; parsear resposta para estrutura esperada (seções do relatório). Retornar conteúdo ao Job para montagem do HTML (FDR-005) e envio por e-mail (FDR-008). Se o LLM retornar markdown, converter para HTML de forma segura.

---

## Como testar

- **Happy path:** Job chama adapter com payload e locale; LLM retorna texto estruturado; parse extrai seções; Job monta HTML e envia e-mail; conteúdo no e-mail contém as seções.
- **Timeout:** Simular LLM lento; job deve falhar com timeout e fazer retry (FDR-006).
- **Resposta malformada:** LLM retorna texto fora do esperado; parser não quebra; fallback ou last_error gravado; não enviar e-mail com conteúdo quebrado.
- **Edge cases:** (1) Locale pt/es/en: prompt usa LANGUAGE correto; saída no idioma esperado. (2) Campos vazios (notes, algum link): placeholder no prompt tratado (ex.: "N/A"). (3) Caracteres especiais no bio/notes: escapados no prompt; sem injection. (4) Rate limit do provedor: retry com backoff no job.

---

## Critérios de aceitação

- [ ] Interface/contrato definido (ex.: `generateReport(payload, locale)`); Job depende da interface, não do provedor.
- [ ] Adapter implementado para o provedor escolhido; config via env (key, modelo).
- [ ] Prompt montado a partir do template; placeholders preenchidos; locale repassado como LANGUAGE.
- [ ] Resposta do LLM parseada para estrutura de seções; HTML montado e passado ao envio de e-mail.
- [ ] Timeout e erros tratados; retry delegado ao job (FDR-005/006).
- [ ] ADR de implementação produzido quando provedor for escolhido (atualizar ADR-014).

---

## Notas de deployment

- Env: `LLM_PROVIDER`, `LLM_API_KEY`, `LLM_MODEL` (ou equivalente por provedor). Não expor API key no frontend. Em staging, usar modelo mais barato se disponível para reduzir custo.

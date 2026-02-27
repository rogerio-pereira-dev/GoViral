# FDR-007.2: Integração com o provedor de LLM

**Feature:** 7.2  
**Referência:** FDR-007, FDR-007.1, FDR-005, ADR-014

---

## Como funciona

- Implementar o **adapter** que satisfaz a interface definida em FDR-007.1 para o provedor escolhido (OpenAI, Gemini ou Anthropic). Configuração via variáveis de ambiente: API key, modelo, endpoint (quando aplicável).
- O **Job** (FDR-005) chama o adapter com os dados do registro em `analysis_requests` e o `locale`; não depende do provedor concreto. Em caso de timeout ou erro da API (rate limit, 5xx), o adapter lança exceção ou retorna erro; o Job trata com retry (FDR-005/006).
- Não implementar a montagem do prompt nem o parse da resposta aqui — isso é FDR-007.3. Esta feature cobre: cliente HTTP/SDK para o provedor, autenticação, tratamento de erro e integração no pipeline do Job.

---

## Como testar

- **Happy path:** Job chama o adapter; adapter envia request ao provedor (payload mock ou real); resposta recebida e repassada ao caller (ou falha tratada).
- **Timeout:** simular LLM lento; adapter deve falhar com timeout; Job faz retry conforme FDR-006.
- **Erro de API (5xx, rate limit):** adapter propaga erro; Job registra `last_error` e faz retry.
- **Config:** trocar modelo ou key via env; adapter usa nova config sem alterar código do Job.

---

## Critérios de aceitação

- [ ] Adapter implementado para o provedor decidido em FDR-007.1; implementa a interface definida.
- [ ] Configuração via env (API key, modelo, endpoint se necessário).
- [ ] Job (FDR-005) chama o adapter; timeout e erros da API tratados; retry delegado ao Job/fila (FDR-006).
- [ ] API key não exposta no frontend.

---

## Notas de deployment

- Env: `LLM_API_KEY`, `LLM_MODEL` (e variáveis específicas do provedor). Staging pode usar modelo mais barato para reduzir custo.

# FDR-007: Integração com LLM

**Feature:** 7 (visão geral; detalhes em FDR_007_1, FDR_007_2, FDR_007_3)  
**Referência:** docs/04 - Features.md, ADR-014, docs/LLM Prompt Template.md

---

## Como funciona

- O Job (FDR-005) precisa obter do LLM o conteúdo estruturado da análise para montar o relatório HTML e enviar por e-mail (FDR-008). A integração está dividida em três sub-features:
- **7.1** Pesquisa e decisão do provedor (FDR_007_1): spike, escolha de provedor e abordagem, interface no código, ADR.
- **7.2** Integração (FDR_007_2): implementar adapter, config env, chamada pelo Job, tratamento de timeout/erros.
- **7.3** Obter relatório (FDR_007_3): montar prompt a partir do template, chamar LLM, parsear resposta, retornar conteúdo ao Job.

---

## Como testar

- Ver FDR_007_1, FDR_007_2, FDR_007_3 para testes e edge cases.

---

## Critérios de aceitação

- [ ] Interface definida; Job depende dela (FDR_007_1).
- [ ] Adapter implementado e configurável (FDR_007_2).
- [ ] Prompt e parse implementados; conteúdo repassado ao Job e ao e-mail (FDR_007_3).

---

## Notas de deployment

- Env definidos em FDR_007_2 (API key, modelo). Não expor API key no frontend.

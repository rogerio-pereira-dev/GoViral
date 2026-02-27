# FDR-007.3: Obter relatório do LLM

**Feature:** 7.3  
**Referência:** FDR-007, FDR-007.2, docs/LLM Prompt Template.md, FDR-005, FDR-008

---

## Como funciona

- **Montagem do prompt:** usar docs/LLM Prompt Template.md. Substituir placeholders: `USERNAME`, `BIO`, `NICHE`, `VIDEO_1`, `VIDEO_2`, `VIDEO_3`, `NOTES`, `LANGUAGE` (valor do locale: en, es ou pt). Campos vazios (ex.: notes) usar placeholder neutro (ex.: "N/A"). Escapar caracteres especiais para evitar injection no prompt.
- **Chamada ao LLM:** enviar o prompt ao provedor via adapter (FDR-007.2). Receber a resposta (texto ou markdown).
- **Parse da resposta:** extrair a estrutura esperada (seções: Executive Summary, Profile Score, Inferred Niche, Username Suggestions, Optimized Bio, Profile Optimization, Content Ideas, Viralization Tips, 30-Day Action Plan). Se o LLM retornar markdown, converter para HTML de forma segura (sanitização). Resposta malformada: gravar em `last_error`, não enviar e-mail com conteúdo quebrado; job pode falhar e retentar (FDR-005).
- **Retorno ao Job:** devolver conteúdo estruturado (ou HTML já montado) para o Job montar o e-mail e enviar (FDR-005, FDR-008).

---

## Como testar

- **Happy path:** Payload completo + locale; prompt montado corretamente; LLM retorna texto com as seções; parse extrai blocos; Job monta HTML e envia e-mail com conteúdo correto.
- **Locale:** pt, es, en; placeholder LANGUAGE preenchido; saída do LLM no idioma esperado.
- **Campos vazios:** notes ou algum link vazio; placeholder "N/A" (ou similar) no prompt; sem erro de template.
- **Resposta malformada:** LLM retorna texto fora do esperado; parser não quebra a aplicação; fallback ou `last_error`; não enviar e-mail com HTML inválido.
- **Markdown → HTML:** se aplicável, conversão segura (sem XSS).

---

## Critérios de aceitação

- [ ] Prompt montado a partir do template; todos os placeholders preenchidos; locale em LANGUAGE.
- [ ] Resposta do LLM parseada para a estrutura de seções definida no PRD/template.
- [ ] Conteúdo (ou HTML) repassado ao Job para montagem do e-mail (FDR-005, FDR-008).
- [ ] Resposta malformada tratada (last_error; não enviar e-mail quebrado).
- [ ] Markdown convertido para HTML com sanitização quando aplicável.

---

## Notas de deployment

- Nenhuma env adicional além das de FDR-007.2. Template (docs/LLM Prompt Template.md) é referência; alterações no template exigem atualização do código de montagem do prompt.

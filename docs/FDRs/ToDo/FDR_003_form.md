# FDR-003: Formulário

**Feature:** 3  
**Referência:** docs/04 - Features.md, ADR-017, ADR-018

---

## Como funciona

- Formulário coleta: email, TikTok username, bio atual, aspiring niche, link vídeo 1, link vídeo 2, link vídeo 3, notas (opcional), locale (en | es | pt). Locale pode vir da landing ou ser escolhido no form; é obrigatório no submit.
- **Validação:** email válido; URLs válidas nos 3 links; tamanhos máximos por campo (definir em regras de negócio); sanitização no backend para XSS/injection (ADR-017).
- **Turnstile:** widget Cloudflare Turnstile no form; no submit, enviar o token junto com os dados. Backend valida o token com a API Turnstile antes de persistir e criar Checkout (FDR-009).
- **Submit:** POST para o backend com todos os campos + token Turnstile. Backend cria registro em `analysis_requests` (payment_status = pending), cria Stripe Checkout Session, retorna URL de redirect; frontend redireciona para o Stripe Checkout (Feature 4).
- **Microcopy:** labels e botão de submit conforme Branding (ex.: "Start My Growth"). Mensagens de validação/erro no idioma do locale quando aplicável.

---

## Como testar

- **Happy path:** Preencher todos os campos válidos + locale + resolver Turnstile; submit; redirecionar para Stripe Checkout; registro criado com payment_status = pending.
- **Validação:** (1) Email inválido → mensagem de erro, sem submit. (2) URL inválida em um dos 3 links → erro. (3) Campos obrigatórios vazios (exceto notas) → erro. (4) Locale ausente → erro ou default en.
- **Turnstile:** (1) Submit sem token (bot) → backend rejeita (422/400). (2) Token expirado → rejeitar. (3) Token de outro site → rejeitar.
- **Edge cases:** (1) Submit duplo (double-click): apenas uma sessão Checkout criada; evitar duplicar registro. (2) Caracteres especiais em bio/notes: sanitização sem quebrar exibição. (3) URLs com tracking params: aceitar se URL base for válida.

---

## Critérios de aceitação

- [ ] Todos os campos (email, username, bio, niche, 3 URLs, notes opcional, locale) presentes e enviados no submit.
- [ ] Validação de formato (email, URLs) no frontend e backend; tamanhos máximos aplicados.
- [ ] Turnstile integrado; submit sem token válido rejeitado pelo backend.
- [ ] Após submit válido: registro em `analysis_requests` com payment_status = pending; redirect para Stripe Checkout.
- [ ] Microcopy do Branding no botão de submit; mensagens de erro claras.
- [ ] Sanitização de entrada no backend (ADR-017).

---

## Notas de deployment

- Chave pública Turnstile no frontend (env); chave secreta no backend (env). Domínio configurado no Cloudflare Turnstile para o ambiente.

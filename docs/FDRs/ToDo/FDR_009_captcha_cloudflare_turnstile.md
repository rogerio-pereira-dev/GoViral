# FDR-009: Captcha no formulário (Cloudflare Turnstile)

**Feature:** 9  
**Referência:** docs/04 - Features.md, ADR-018

---

## Como funciona

- No formulário (FDR-003), widget Cloudflare Turnstile é exibido (modo managed ou invisible conforme escolha de produto). Usuário interage se necessário; Turnstile gera um token.
- No submit, o frontend envia o token junto com os demais campos (ex.: campo `turnstile_token` ou `cf-turnstile-response`). Backend, antes de criar o registro em `analysis_requests` e a Stripe Checkout Session, valida o token chamando a API Turnstile (siteverify). Se a validação falhar (token ausente, inválido, expirado, domínio diferente), a requisição é rejeitada (ex.: 422) com mensagem adequada; não cria registro nem sessão. Não há rate limiting para usuários reais (ADR-018); o captcha é a barreira anti-bot.

---

## Como testar

- **Happy path:** Usuário preenche form e resolve Turnstile (se visível); submit com token; backend valida token; cria registro e Checkout; redirect para Stripe.
- **Token ausente:** Submit sem token (ex.: script simulando bot); backend retorna 422; nenhum registro criado; mensagem de erro exibida.
- **Token inválido/expirado:** Token fake ou expirado; siteverify retorna falha; backend retorna 422; nenhum registro criado.
- **Edge cases:** 
    - Múltiplos submits rápidos: cada um precisa de novo token (Turnstile gera um por vez). 
    - Domínio: em dev, usar chave de teste Turnstile ou configurar domínio local no dashboard. 
    - Usuário com bloqueador de script: Turnstile pode não carregar; definir fallback (ex.: mensagem "habilite JavaScript" ou degradação controlada por produto). 
    - Timeout da API Turnstile: definir timeout curto no backend; em falha de rede para siteverify, tratar como inválido (422) ou retry uma vez, conforme política.

---

## Critérios de aceitação

- [ ] Widget Turnstile integrado no formulário; token enviado no submit.
- [ ] Backend valida token com API Turnstile antes de persistir e criar Checkout; em falha de validação retorna 4xx e mensagem clara.
- [ ] Sem rate limiting para usuários (apenas captcha como controle anti-bot — ADR-018).
- [ ] Chaves (site key / secret) configuradas por env; domínio correto no Cloudflare Turnstile.

---

## Notas de deployment

- Chave pública (site key) no frontend (env ou build); chave secreta apenas no backend (env). Em produção, registrar domínio no Cloudflare Turnstile. Ambientes diferentes (staging/prod) podem usar chaves diferentes.

# FDR-004: Pagamento

**Feature:** 4 (4.1 Instalar/Config Stripe, 4.2 Realizar pagamento, 4.3 Webhook)  
**Referência:** docs/04 - Features.md, ADR-007, ADR-016, ADR-015

---

## Como funciona

**4.1 Configuração**
- Laravel Cashier (Stripe) instalado. Env: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=usd`.
- Produto/preço no Stripe: pagamento único, USD (ex.: $20). Stripe Checkout como fluxo (hosted). URLs de success e cancel configuradas (ex.: `/thank-you`, `/` ou `/form`).
- Webhook no Stripe apontando para a aplicação: evento `checkout.session.completed`; signing secret em env.

**4.2 Fluxo de pagamento**
- Após submit válido do formulário (FDR-003), backend cria registro em `analysis_requests` (pending), cria Stripe Checkout Session com metadata (ex.: `analysis_request_id` ou email) e preço do produto; retorna a URL da sessão.
- Frontend redireciona o usuário para essa URL (Stripe Checkout). Usuário paga em USD; Stripe redireciona para a URL de success. A confirmação efetiva é via webhook (4.3), não pelo redirect.

**4.3 Webhook**
- Endpoint (ex.: `POST /stripe/webhook`) recebe eventos Stripe. Validar assinatura com `Stripe-Signature` e `STRIPE_WEBHOOK_SECRET` (ADR-016); se inválida → 400/403, não processar.
- Para `checkout.session.completed`: localizar registro em `analysis_requests` via `session_id` ou metadata; atualizar `payment_status = paid`, `processing_status = queued`; despachar job `ProcessAnalysisRequest` com o id do registro; responder 200 rapidamente (sem chamar LLM/email no request).

---

## Como testar

- **4.1:** `php artisan` lista comandos Cashier; env carregado; em teste, usar Stripe CLI para encaminhar webhooks.
- **4.2:** Submit do form → redirect para Checkout; pagar com cartão de teste (4242...) → redirect para success; registro continua pending até o webhook ser recebido.
- **4.3 Happy path:** Stripe CLI dispara `checkout.session.completed` → endpoint retorna 200; registro fica paid e queued; job aparece na fila.
- **4.3 Edge cases:** (1) Assinatura inválida → 4xx, registro não atualizado, job não enfileirado. (2) Evento duplicado (retry Stripe) → idempotente: mesmo evento processado duas vezes não duplica job ou quebra estado. (3) `session_id` não encontrado no banco → log + 200 (Stripe não reenvia por 4xx desnecessário, mas não atualizar registro inexistente). (4) Outros eventos (ex.: `payment_intent.succeeded`) → ignorar ou tratar conforme doc Stripe; pelo menos `checkout.session.completed` deve enfileirar job.

---

## Critérios de aceitação

- [ ] Cashier instalado; env configurado; produto/preço USD no Stripe; Checkout e webhook configurados.
- [ ] Após submit do form: criação de registro (pending) e redirect para Stripe Checkout com sessão correta.
- [ ] Pagamento bem-sucedido no Checkout: usuário vai para success; webhook recebido → payment_status = paid, processing_status = queued, job enfileirado.
- [ ] Webhook com assinatura inválida rejeitado (4xx); nenhuma atualização nem job.
- [ ] Resposta do webhook < ~5s (processamento pesado no job, não no request).

---

## Notas de deployment

- Em produção: configurar webhook no Stripe Dashboard com URL pública e copiar signing secret para env. Usar HTTPS. Stripe CLI apenas para dev/local.

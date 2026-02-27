# FDR-004.1: Instalar e configurar Stripe

**Feature:** 4.1  
**Referência:** FDR-004, docs/04 - Features.md, ADR-007

---

## Como funciona

- **Laravel Cashier (Stripe)** instalado e configurado no projeto.
- **Variáveis de ambiente:** `STRIPE_KEY` (publishable), `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=usd`.
- **Stripe Dashboard:** produto e preço configurados (pagamento único, USD; valor alvo ex.: $20). Para pagamento embutido na página do formulário, usar **Stripe Elements** (ou Payment Element) com Payment Intents ou Checkout Session com `mode: payment` conforme abordagem escolhida; URLs de success e cancel apontam para a aplicação (ex.: success = página de Obrigado, cancel = voltar ao formulário).
- **Webhook:** endpoint da aplicação registrado no Stripe para o evento `checkout.session.completed` (ou equivalente, se usar Payment Intent); signing secret em `STRIPE_WEBHOOK_SECRET`.

---

## Como testar

- `php artisan` lista comandos Cashier; configuração carrega sem erro.
- Chaves e webhook secret em env; em local, usar Stripe CLI para encaminhar webhooks (`stripe listen --forward-to ...`).
- Produto/preço existem no Stripe; valor em USD.

---

## Critérios de aceitação

- [ ] Cashier instalado; env com `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=usd`.
- [ ] Produto e preço (one-time, USD) configurados no Stripe.
- [ ] Webhook configurado no Stripe com evento `checkout.session.completed` (ou o evento usado pelo fluxo de pagamento na página).
- [ ] URLs de success/cancel configuradas (success = página de Obrigado).

---

## Notas de deployment

- Produção: configurar webhook no Stripe Dashboard com URL pública HTTPS; copiar signing secret para env. Stripe CLI apenas para dev/local.

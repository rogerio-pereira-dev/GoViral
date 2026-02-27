# FDR-004.1: Install and configure Stripe

**Feature:** 4.1  
**Reference:** FDR-004, docs/04 - Features.md, ADR-007

---

## How it works

- **Laravel Cashier (Stripe)** installed and configured in the project.
- **Environment variables:** `STRIPE_KEY` (publishable), `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=usd`.
- **Stripe Dashboard:** product and price configured (one-time payment, USD; target amount e.g. $20). For payment embedded on the form page, use **Stripe Elements** (or Payment Element) with Payment Intents or Checkout Session with `mode: payment` per chosen approach; success and cancel URLs point to the app (e.g. success = Thank You page, cancel = back to form).
- **Webhook:** application endpoint registered in Stripe for the `checkout.session.completed` event (or equivalent if using Payment Intent); signing secret in `STRIPE_WEBHOOK_SECRET`.

---

## How to test

- `php artisan` lists Cashier commands; config loads without error.
- Keys and webhook secret in env; locally, use Stripe CLI to forward webhooks (`stripe listen --forward-to ...`).
- Product/price exist in Stripe; amount in USD.

---

## Acceptance criteria

- [ ] Cashier installed; env with `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `CASHIER_CURRENCY=usd`.
- [ ] Product and price (one-time, USD) configured in Stripe.
- [ ] Webhook configured in Stripe with event `checkout.session.completed` (or the event used by the in-page payment flow).
- [ ] Success/cancel URLs configured (success = Thank You page).

---

## Deployment notes

- Production: configure webhook in Stripe Dashboard with public HTTPS URL; copy signing secret to env. Stripe CLI for dev/local only.

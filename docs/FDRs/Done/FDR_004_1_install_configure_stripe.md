# FDR-004.1: Install and configure Stripe

**Feature:** 4.1  
**Reference:** FDR-004, docs/04 - Features.md, ADR-007

---

## How it works

- **Laravel Cashier (Stripe)** installed and configured in the project.
- **Environment variables:** `STRIPE_KEY` (publishable), `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
- **Stripe CLI in Docker:** local webhook forwarding runs through a dedicated `stripe-cli` service in `compose.yaml`, forwarding events to the application webhook URL (e.g. `/webhooks/stripe`).
- **Checkout behavior:** amount and item details are defined dynamically at checkout time; no mandatory pre-created product/price in Stripe Dashboard.
- **Webhook:** application endpoint registered in Stripe for the event used by the in-page flow (e.g. `payment_intent.succeeded`); signing secret in `STRIPE_WEBHOOK_SECRET`.

---

## How to test

- `php artisan` lists Cashier commands; config loads without error.
- Keys and webhook secret in env; locally, `stripe-cli` container forwards webhooks and logs the signing secret.
- Run `php artisan vendor:publish --tag="cashier-migrations"` and `php artisan vendor:publish --tag="cashier-config"` successfully.

---

## Acceptance criteria

- [x] Cashier installed; env with `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
- [x] Stripe CLI service documented in `compose.yaml` and usable for local webhook forwarding.
- [x] Cashier migrations and configuration are published in the project.
- [ ] Webhook configured in Stripe with event used by the in-page payment flow (e.g. `payment_intent.succeeded`).
- [ ] Success/cancel URLs configured (success = Thank You page).

---

## Deployment notes

- Production: configure webhook in Stripe Dashboard with public HTTPS URL; copy signing secret to env. Stripe CLI for dev/local only.

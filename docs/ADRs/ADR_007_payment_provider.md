# ADR-007: Payment Provider

## Status

Approved

## Context

The business model is one-time payment (~US$20), USD only, no subscription in the MVP. Payment must be validated reliably (webhooks) and analysis processing without confirmed payment must be avoided.

## Decision

Use **Stripe** as the payment provider, with **Laravel Cashier** for backend integration and **Stripe Checkout** for the payment flow, ensuring event validation via **webhooks** (e.g. `checkout.session.completed`).

References:
- [Laravel Cashier](https://laravel.com/docs/12.x/billing)
- [Stripe Checkout](https://docs.stripe.com/payments/checkout)
- [Stripe Webhooks](https://docs.stripe.com/webhooks)

## Consequences

- **Positive:** Hosted checkout reduces PCI scope; Cashier abstracts subscriptions and one-off payments; webhooks allow updating status and triggering the analysis job securely.
- **Negative:** Dependency on Stripe and its fees; webhook configuration (URL, signature) required per environment.
- **Neutral:** Webhook signature must always be validated on the backend (security).

# ADR-016: Stripe Webhook Validation

## Status

Approved

## Context

Stripe sends events (e.g. `checkout.session.completed`) to a public URL. Without validation, an attacker could send fake requests and trigger analysis processing without real payment or manipulate status.

## Decision

**Always validate the signature** of Stripe webhooks before processing any event. Use the webhook signing secret (configured in the Stripe Dashboard) and the official library (Laravel Cashier / Stripe SDK) to verify the `Stripe-Signature` header and payload. Requests with invalid or missing signature must be rejected with a 4xx response, without updating the database or enqueueing jobs.

Reference: [Stripe Webhooks](https://docs.stripe.com/webhooks) (signature verification).

## Consequences

- **Positive:** Ensures only legitimate Stripe events trigger payment update and job dispatch; protection against forgery and replay (if applicable by Stripe).
- **Negative:** Secret configuration per environment (dev/staging/prod); rotating the secret requires application update.
- **Neutral:** Rejection logs help detect abuse attempts or misconfiguration.

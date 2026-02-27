# ADR-002: Backend Framework

## Status

Approved

## Context

The backend must provide API, Stripe integration (webhooks), async queues, email delivery, and future LLM provider integration. A mature framework with queue, billing, and email support and a PHP ecosystem aligned with the rest of the stack (Laravel Cloud, Cashier) is required.

## Decision

Use **Laravel** (latest stable version) as the backend framework.

Reference: [Laravel Documentation](https://laravel.com/docs)

## Consequences

- **Positive:** Native support for queues (Redis), Cashier (Stripe), Mail, validation, and extensive documentation; compatibility with Laravel Cloud.
- **Negative:** Single PHP stack; major upgrades require planning.
- **Neutral:** Team must know Laravel to evolve the product.

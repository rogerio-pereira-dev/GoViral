# ADR-005: Queue System

## Status

Approved

## Context

After payment confirmation via the Stripe webhook, processing (LLM call, HTML report generation, email delivery) must be asynchronous so as not to block the webhook response and to allow scaling workers independently.

## Decision

Use **Redis** as the Laravel queue driver for enqueueing and processing analysis jobs.

Reference: [Laravel Queues](https://laravel.com/docs/12.x/queues)

## Consequences

- **Positive:** Redis is fast, natively supported by Laravel, and suitable for queues; allows multiple workers and configurable retries (e.g. every 5 minutes, up to 12 attempts).
- **Negative:** Dependency on Redis availability; if Redis fails for an extended period, jobs are not consumed.
- **Neutral:** Horizontal scalability via more worker instances; queue failure monitoring is recommended.

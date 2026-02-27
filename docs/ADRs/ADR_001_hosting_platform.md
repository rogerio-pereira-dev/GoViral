# ADR-001: Hosting Platform

## Status

Approved

## Context

GoViral needs a hosting environment that supports Laravel, queues (workers), Redis, and PostgreSQL, with horizontal scalability and low operational cost. Alternatives include traditional VPS, direct AWS/GCP, generic PaaS, and Laravel-specific offerings.

## Decision

Use **Laravel Cloud** as the project hosting platform.

Reference: [Laravel Cloud Documentation](https://cloud.laravel.com/docs/intro)

## Consequences

- **Positive:** Native integration with the Laravel ecosystem, queue and worker support, less DevOps effort, alignment with the chosen stack.
- **Negative:** Vendor lock-in to the Laravel ecosystem; costs depend on the platform offering.
- **Neutral:** Horizontal scalability must be validated within Laravel Cloud limits.

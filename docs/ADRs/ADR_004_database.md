# ADR-004: Database

## Status

Approved

## Context

The system must persist analysis requests (form data, Stripe IDs, payment and processing status) with a minimal retention policy: removal after report delivery, after repeated failures, or after 24 hours. There is no need for historical reports or a dashboard in the MVP.

## Decision

Use **PostgreSQL** as the relational database, with minimal storage usage and a single main table for analysis requests.

Reference: stack defined in HLD (section 2).

## Consequences

- **Positive:** PostgreSQL is robust, supported by Laravel and Laravel Cloud; suitable for low volume and programmatic cleanup.
- **Negative:** None critical for the current scope.
- **Neutral:** Resource usage (space and connections) should remain low given the retention model.

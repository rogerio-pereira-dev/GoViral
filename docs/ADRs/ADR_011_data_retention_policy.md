# ADR-011: Data Retention Policy

## Status

Approved

## Context

The product does not offer a dashboard or history in the MVP. Keeping records indefinitely would increase storage, data surface, and privacy obligations without direct benefit to the user.

## Decision

**Delete the record** in `analysis_requests` when any of the following conditions is met:

1. **Report sent successfully:** after the email is sent.
2. **Processing failures exhausted:** after 12 processing attempts (retry every 5 minutes, ~1 hour), the record is marked as failed and removed.
3. **Maximum age:** records older than **24 hours** are removed by a scheduled job (Laravel Scheduler), regardless of status.

Reference: [Laravel Scheduling](https://laravel.com/docs/12.x/scheduling)

## Consequences

- **Positive:** Minimal storage; smaller surface of sensitive data; aligned with the “no history” positioning.
- **Negative:** Cannot resend the report or audit old orders without external logs; incident support depends on logs/metrics.
- **Neutral:** Cleanup jobs must run reliably (cron/scheduler).

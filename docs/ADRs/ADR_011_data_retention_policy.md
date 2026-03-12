# ADR-011: Data Retention Policy

## Status

**Superseded by [ADR-020: Data Retention — Retain for Case Studies](ADR_020_data_retention_retain_for_case_studies.md)**

## Context

The product does not offer a dashboard or history in the MVP. Keeping records indefinitely would increase storage, data surface, and privacy obligations without direct benefit to the user.

## Decision (Original)

**Delete the record** in `analysis_requests` when any of the following conditions is met:

1. **Report sent successfully:** after the email is sent.
2. **Processing failures exhausted:** after 12 processing attempts (retry every 5 minutes, ~1 hour), the record is marked as failed and removed.
3. **Maximum age:** records older than **24 hours** are removed by a scheduled job (Laravel Scheduler), regardless of status.

Reference: [Laravel Scheduling](https://laravel.com/docs/12.x/scheduling)

## Consequences

- **Positive:** Minimal storage; smaller surface of sensitive data; aligned with the "no history" positioning.
- **Negative:** Cannot resend the report or audit old orders without external logs; incident support depends on logs/metrics.
- **Neutral:** Cleanup jobs must run reliably (cron/scheduler).

## Supersession

The retention policy has been revised. Analyses are retained for internal case studies; report content is persisted before sending the email. See **ADR-020: Data Retention — Retain for Case Studies**. FDR-010 (scheduler for data cleanup) is closed and moved to `docs/FDRs/Closed/`.

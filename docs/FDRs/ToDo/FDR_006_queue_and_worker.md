# FDR-006: Configure queue and worker

**Feature:** 6  
**Reference:** docs/04 - Features.md, ADR-005, ADR-015

---

## How it works

- Laravel uses Redis as the queue driver (`QUEUE_CONNECTION=redis`). Redis connection configured in `config/database.php` / `.env`.
- Job `ProcessAnalysisRequest` (FDR-005) is enqueued by the webhook (FDR-004). Single queue (e.g. `default`) or dedicated (e.g. `analysis`); name consistent between dispatch and worker.
- Worker consumes the queue: `php artisan queue:work` (or `queue:work redis --queue=default`). Attempt configuration: max 12; backoff between attempts (e.g. 5 minutes). Job timeout sufficient for LLM + email (e.g. 120–300 s).
- In production: worker process(es) guaranteed (Laravel Cloud, supervisor, systemd or equivalent) so jobs are not only run via cron.

---

## How to test

- **Happy path:** Webhook enqueues job; running worker processes it and job leaves the queue; record updated/deleted per FDR-005.
- **Worker stopped:** Job stays in the queue; when worker is started, job is processed.
- **Retry:** Force job failure (e.g. LLM unavailable); verify job goes back to queue with backoff; attempt_count increases; after 12 attempts job fails and record is removed (FDR-005 behavior).
- **Edge cases:** (1) Redis unavailable: job dispatch should fail or be enqueued in sync so the webhook event is not lost (evaluate fallback). (2) Multiple workers: same job not processed by two workers (Laravel lock). (3) Timeout: job that exceeds timeout is released and retried; ensure attempt_count reflects this.

---

## Acceptance criteria

- [ ] `QUEUE_CONNECTION=redis`; Redis accessible; job is enqueued by webhook and appears in the queue (e.g. Horizon or Redis CLI).
- [ ] `php artisan queue:work` processes the job; after success, job removed from queue; record in sent + deleted.
- [ ] Retry configured: max 12 attempts; backoff applied (e.g. 5 min).
- [ ] Job timeout configured and greater than typical LLM + email time.
- [ ] Documentation or script to run worker in production (Laravel Cloud, supervisor, etc.).

---

## Deployment notes

- Production: ensure the worker is always running (restart on failure). Environment variables (Redis URL, queue name) the same for app and worker. Monitoring: job failures and queue size (e.g. Laravel Horizon or logs).

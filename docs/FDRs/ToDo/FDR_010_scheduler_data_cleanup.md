# FDR-010: Scheduler for data cleanup

**Feature:** 10  
**Reference:** docs/04 - Features.md, ADR-011

---

## How it works

- Laravel Scheduler (cron) configured in the environment. A scheduled command or job runs at a defined interval (e.g. daily or every 6h).
- The cleanup command/job: (1) Removes records from `analysis_requests` with `processing_status = sent` (in case any remain due to delayed deletion in the Job — FDR-005). (2) Removes records with `created_at` older than 24 hours, any status. (3) Removes records with `processing_status = failed` and `attempt_count >= 12` (in case they were not deleted by the Job itself). Do not store reports; do not create a data repository (ADR-011, ADR-012). Optional: log the number of records removed per run.

---

## How to test

- **Sent records:** Insert record with processing_status = sent; run command; record should disappear.
- **Records > 24h:** Insert record with created_at 25 hours ago; run command; record should disappear. Record with created_at 23 hours ago should remain (or document a different policy).
- **Failed records with 12 attempts:** Insert with processing_status = failed, attempt_count = 12; run command; record should disappear.
- **Edge cases:** (1) Old pending or queued records (> 24h): should be removed by the 24h rule. (2) Command run with zero matching records: no error. (3) Concurrency: Job (FDR-005) and scheduler may both delete; avoid race (e.g. delete by id or atomic criteria). (4) Cron actually firing: in production, ensure Laravel cron is active (`schedule:run` every minute).

---

## Acceptance criteria

- [ ] Cleanup command/job implemented; criteria: sent, created_at > 24h, failed with attempt_count >= 12.
- [ ] Scheduler registers the command (e.g. `$schedule->command('analysis:cleanup')->daily()` or equivalent).
- [ ] When running the command manually: records that meet the criteria are removed; others remain.
- [ ] Documentation or comment on how to enable the cron (`* * * * * php artisan schedule:run`) in production.
- [ ] Optional: log with count of rows deleted.

---

## Deployment notes

- Production: ensure the system cron calls `php artisan schedule:run` every minute (or per Laravel docs). Laravel Cloud and many PaaS already configure this. App timezone (`APP_TIMEZONE`) affects "24 hours" (use UTC or consistent timezone for created_at).

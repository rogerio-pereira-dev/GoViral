# FDR-004.3: Payment confirmation webhook

**Feature:** 4.3  
**Reference:** FDR-004, FDR-005, ADR-016, ADR-015

---

## How it works

- Endpoint (e.g. `POST /stripe/webhook`) receives Stripe events. **Always validate the signature** with the `Stripe-Signature` header and `STRIPE_WEBHOOK_SECRET` (ADR-016). If invalid → 400/403, do not process and do not update the database.
- For the **`checkout.session.completed`** event (or the event equivalent to the in-page payment flow): find the record in `analysis_requests` via `session_id` or metadata; update `payment_status = paid`, `processing_status = queued`; **dispatch the job** `ProcessAnalysisRequest` with the record id; respond **200** quickly (heavy processing stays in the job — ADR-015).
- Other events may be ignored or handled per Stripe docs; at least `checkout.session.completed` must enqueue the job.

---

## How to test

- **Happy path:** Stripe CLI sends `checkout.session.completed` with valid payload; endpoint returns 200; record becomes `payment_status = paid`, `processing_status = queued`; job appears in the queue.
- **Invalid signature:** payload without signature or wrong secret → 4xx; record not updated; job not enqueued.
- **Idempotency:** same event processed twice (Stripe retry) does not duplicate job or break state (e.g. check if record is already paid before enqueueing again).
- **session_id not found:** log; respond 200 so Stripe does not retry in a loop; do not update non-existent record.

---

## Acceptance criteria

- [ ] Webhook validates signature; invalid request rejected (4xx).
- [ ] For `checkout.session.completed`: record updated (paid, queued); job `ProcessAnalysisRequest` enqueued; response 200 in < ~5s.
- [ ] Stripe retry (same event) handled idempotently.

---

## Deployment notes

- Production: public HTTPS URL; signing secret from Stripe Dashboard in env. Stripe CLI for local testing.

# ADR-015: Asynchronous Processing After Payment

## Status

Approved

## Context

After payment is confirmed via Stripe, the system must call the LLM, build the HTML report, and send the email. Doing this synchronously in the webhook would make the response slow, increase timeout risk, and couple Stripe reliability to LLM and SES availability.

## Decision

All processing **after payment confirmation** is **asynchronous**:

1. The Stripe webhook (`checkout.session.completed`) validates the signature, updates `payment_status = paid`, and **enqueues a job** (Redis).
2. The webhook responds quickly to Stripe (200 OK).
3. A Laravel **worker** consumes the job: updates `processing_status = processing`, calls the LLM, generates the HTML, sends the email via SES, updates `processing_status = sent`, and removes the record (per retention policy).

No LLM call or email sending is done inside the webhook request.

## Consequences

- **Positive:** Stable webhook within Stripe SLA; horizontal scalability via more workers; LLM/SES failures are handled by queue retry.
- **Negative:** Delivery is not instant; user receives the report within minutes (target SLA: up to 10 min, typical 1–3 min).
- **Neutral:** Queue and job failure monitoring is essential for operations.

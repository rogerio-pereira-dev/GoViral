# ADR-009: Persistence Model (Single Table)

## Status

Approved

## Context

The system must store each analysis request with form data, Stripe references, and payment/processing status. There is no requirement for history, dashboard, or stored reports in the MVP.

## Decision

Use a **single table**, `analysis_requests`, containing all required fields:

- Identification: `id` (UUID)
- Form data: `email`, `tiktok_username`, `bio`, `aspiring_niche`, `video_url_1`, `video_url_2`, `video_url_3`, `notes` (optional), `locale` (en/es/pt)
- Stripe: `stripe_checkout_session_id`, `stripe_payment_intent_id` (optional)
- Status: `payment_status` (pending | paid | failed), `processing_status` (queued | processing | sent | failed)
- Retry control: `attempt_count`, `last_error` (nullable)
- Timestamps: `created_at`, `updated_at`

No storage of generated reports; no history or audit tables in the current scope.

## Consequences

- **Positive:** Simple model, easy to implement and clean; aligned with minimal retention policy; trivial migrations and backups.
- **Negative:** Evolutions requiring multiple entities (e.g. user, orders, deliveries) may require refactoring.
- **Neutral:** Retention and cleanup are covered in a separate ADR (retention policy).

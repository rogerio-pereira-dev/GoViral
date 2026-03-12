# ADR-023: Discount Coupons in Core (Admin CRUD and Checkout)

## Status

Approved

## Context

The product owner wants logged-in users (admins) to create discount coupons that can be applied at checkout on `/start-growth`. Coupons have a code, a percentage value (0–100), and an expiration strategy (never, after X days, or after X uses). Only authenticated users in the core area (`/core/*`) may create, read, update, or delete coupons. Unauthenticated or public requests must not be able to create or manipulate coupons (authorization and abuse prevention). Deletion of coupons must be permanent (hard delete from the database).

## Decision

1. **Model and storage:** A new table (e.g. `discount_coupons`) stores coupons with: `id` (UUID), `code` (string, unique, indexed), `value` (integer 0–100, percentage), and fields to support expiration: e.g. `expires_at` (nullable timestamp for "after X days"), `max_uses` (nullable integer for "after X uses"), `times_used` (integer, default 0). "Never expires" is represented by both `expires_at` and `max_uses` null.

2. **Expiration behaviour:**
   - **Never expires:** `expires_at` and `max_uses` null; coupon remains valid until deleted.
   - **After X days:** Set `expires_at` at creation; when the current time is past `expires_at`, the coupon is invalid. A **scheduler** (Laravel Scheduler) runs a job that hard-deletes rows where `expires_at` is not null and `expires_at < now()`.
   - **After X uses:** Set `max_uses` at creation; on each successful payment that used the coupon, decrement usage (e.g. increment `times_used` and treat as used when `times_used >= max_uses`). When `times_used >= max_uses`, the coupon is invalid and is **hard-deleted** from the database (or marked invalid and deleted in the same flow).

3. **Authorization:** All coupon CRUD is under routes prefixed with `/core/`, protected by the same `auth` (and optionally `verified`) middleware as the rest of the core. No coupon creation or update is possible from public or unauthenticated requests. Validation and authorization must ensure that only authenticated users can create/update/delete coupons (prevent abuse e.g. prompt injection or forged requests from unauthenticated clients).

4. **Checkout integration:** At checkout on `/start-growth`, the user may enter a coupon code. The backend validates the code (exists, not expired, not exhausted), applies the percentage discount to the amount, and records the coupon usage on payment confirmation (webhook). After payment is confirmed, usage is decremented (or `times_used` incremented); when the coupon reaches zero remaining uses, it is hard-deleted.

5. **Deletion:** Coupon deletion from the admin is **hard delete** (remove the row from the database). Expired coupons (by date) and exhausted coupons (by uses) are also hard-deleted as described above.

## Consequences

- **Positive:** Clear model for expiration and usage; single place (core) for CRUD; checkout stays simple (validate code, apply discount, record use).
- **Negative:** Scheduler and webhook logic add moving parts; must keep coupon validation and usage atomic to avoid race conditions.
- **Neutral:** Frontend for CRUD in core can follow existing core UI (e.g. Vuetify after FDR-013); API or Inertia forms both possible.

## References

- docs/04 - Features.md (Feature 14)
- docs/FDRs/ToDo/FDR_014_core_discount_coupons.md

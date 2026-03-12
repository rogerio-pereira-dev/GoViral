# ADR-023: Discount Coupons in Core (Admin CRUD and Checkout)

## Status

Approved

## Context

The product owner wants logged-in users (admins) to create discount coupons that can be applied at checkout on `/start-growth`. Coupons have a code, a percentage value (0–100), and an expiration strategy (never, after X days, or after X uses). Only authenticated users in the core area (`/core/*`) may create, read, update, or delete coupons. Unauthenticated or public requests must not be able to create or manipulate coupons (authorization and abuse prevention). The system must record which coupon was used on each analysis request; therefore coupons **must not be hard-deleted** so that `analysis_requests` can keep a valid reference to the coupon used.

## Decision

1. **Model and storage:** A new table (e.g. `discount_coupons`) stores coupons with: `id` (UUID), `code` (string, unique, indexed), `value` (integer 0–100, percentage), expiration fields (`expires_at`, `max_uses`, `times_used`), and `deleted_at` (nullable timestamp for soft delete). The **analysis_requests** table is altered to include the coupon used (e.g. `discount_coupon_id` nullable foreign key to `discount_coupons.id`). Because deletion is always soft delete, the coupon row is never removed and the reference is never lost; no ON DELETE SET NULL is required.

2. **Usage tracking:** Every time a coupon is used (after payment is confirmed in the webhook), **increment** the coupon’s `times_used` field, regardless of expiration type (never, after X days, or after X uses). This allows consistent analytics and keeps the coupon record for reference from `analysis_requests`.

3. **Expiration behaviour:**
   - **Never expires:** `expires_at` and `max_uses` null; coupon remains valid for new uses until an admin soft-deletes it.
   - **After X days:** Set `expires_at` at creation; when the current time is past `expires_at`, the coupon is **invalid for new uses** at checkout. A scheduler job will soft-delete such expired coupons (and exhausted ones) so the list stays manageable; the row remains in the database (soft delete), so `analysis_requests` never loses the reference.
   - **After X uses:** Set `max_uses` at creation; when `times_used >= max_uses`, the coupon is **invalid for new uses** at checkout. The scheduler also soft-deletes exhausted coupons; the row remains (soft delete), so the reference is preserved.

4. **Soft delete only:** Coupons are never hard-deleted (no physical row removal). Admin "delete" and the scheduler use **soft delete** (e.g. `deleted_at`). The row stays in the database, so `analysis_requests.discount_coupon_id` always keeps a valid reference; no ON DELETE SET NULL is needed. A **scheduler** job runs periodically and soft-deletes invalid coupons: (1) expired (`expires_at` is not null and `expires_at < now()`), (2) exhausted (`max_uses` is not null and `times_used >= max_uses`). Queries for "active" coupons (e.g. at checkout, in admin list) exclude soft-deleted rows.

5. **Authorization:** All coupon CRUD is under routes prefixed with `/core/`, protected by the same `auth` (and optionally `verified`) middleware as the rest of the core. No coupon creation or update is possible from public or unauthenticated requests.

6. **Checkout integration:** At checkout on `/start-growth`, the user may enter a coupon code. The backend validates the code (exists, not expired, not exhausted), applies the percentage discount, and stores the chosen coupon id on the analysis request (e.g. `discount_coupon_id`). On payment confirmation (webhook), increment the coupon’s `times_used`.

## Consequences

- **Positive:** Referential integrity between `analysis_requests` and the coupon used; consistent usage tracking via `times_used` for all coupon types; no orphaned references.
- **Negative:** Coupon table grows over time; admin may need filters (e.g. "active only") or soft delete to keep lists manageable.
- **Neutral:** Frontend for CRUD in core can follow existing core UI; optional soft delete or "inactive" flag for admin UX.

## References

- docs/04 - Features.md (Feature 14)
- docs/FDRs/ToDo/FDR_014_core_discount_coupons.md

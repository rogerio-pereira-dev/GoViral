# ADR-023: Discount Coupons in Core (Admin CRUD and Checkout)

## Status

Approved

## Context

The product owner wants logged-in users (admins) to create discount coupons that can be applied at checkout on `/start-growth`. Coupons have a code, a percentage value (0–100), and an expiration strategy (never, after X days, or after X uses). Only authenticated users in the core area (`/core/*`) may create, read, update, or delete coupons. Unauthenticated or public requests must not be able to create or manipulate coupons (authorization and abuse prevention). The system must record which coupon was used on each analysis request; therefore coupons **must not be hard-deleted** so that `analysis_requests` can keep a valid reference to the coupon used.

## Decision

1. **Model and storage:** A new table (e.g. `discount_coupons`) stores coupons with: `id` (UUID), `code` (string, unique, indexed), `value` (integer 0–100, percentage), and fields to support expiration: e.g. `expires_at` (nullable timestamp for "after X days"), `max_uses` (nullable integer for "after X uses"), `times_used` (integer, default 0). "Never expires" is represented by both `expires_at` and `max_uses` null. The **analysis_requests** table is altered to include the coupon used (e.g. `discount_coupon_id` nullable foreign key to `discount_coupons.id`).

2. **Usage tracking:** Every time a coupon is used (after payment is confirmed in the webhook), **increment** the coupon’s `times_used` field, regardless of expiration type (never, after X days, or after X uses). This allows consistent analytics and keeps the coupon record for reference from `analysis_requests`.

3. **Expiration behaviour:**
   - **Never expires:** `expires_at` and `max_uses` null; coupon remains valid for new uses until an admin disables or soft-deletes it (if such behaviour is implemented).
   - **After X days:** Set `expires_at` at creation; when the current time is past `expires_at`, the coupon is **invalid for new uses** at checkout. The coupon row is **not deleted**; expired coupons remain in the database so that existing `analysis_requests` rows can still reference them.
   - **After X uses:** Set `max_uses` at creation; when `times_used >= max_uses`, the coupon is **invalid for new uses** at checkout. The coupon row is **not deleted**; exhausted coupons remain in the database for the same reason.

4. **No hard delete:** Coupons are **not hard-deleted** (no physical row removal). Admin "delete" may be implemented as soft delete (e.g. `deleted_at`) or as a status flag to hide from lists and invalidate for new uses; the row remains for referential integrity with `analysis_requests`. There is no scheduler job that deletes coupon rows; expiration and exhaustion only make the coupon invalid for new checkout uses.

5. **Authorization:** All coupon CRUD is under routes prefixed with `/core/`, protected by the same `auth` (and optionally `verified`) middleware as the rest of the core. No coupon creation or update is possible from public or unauthenticated requests.

6. **Checkout integration:** At checkout on `/start-growth`, the user may enter a coupon code. The backend validates the code (exists, not expired, not exhausted), applies the percentage discount, and stores the chosen coupon id on the analysis request (e.g. `discount_coupon_id`). On payment confirmation (webhook), increment the coupon’s `times_used`.

## Consequences

- **Positive:** Referential integrity between `analysis_requests` and the coupon used; consistent usage tracking via `times_used` for all coupon types; no orphaned references.
- **Negative:** Coupon table grows over time; admin may need filters (e.g. "active only") or soft delete to keep lists manageable.
- **Neutral:** Frontend for CRUD in core can follow existing core UI; optional soft delete or "inactive" flag for admin UX.

## References

- docs/04 - Features.md (Feature 14)
- docs/FDRs/ToDo/FDR_014_core_discount_coupons.md

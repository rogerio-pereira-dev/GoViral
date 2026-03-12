# FDR-014: Core discount coupons (admin CRUD and checkout)

**Feature:** 14 (discount coupons in core)  
**Reference:** docs/04 - Features.md, ADR-023

---

## How it works

- **Scope:** Logged-in users (admins) manage discount coupons in the core panel (`/core/*`). Coupons are applied at checkout on `/start-growth` as a percentage discount (0–100%). CRUD is protected by authentication; only authenticated users can create, read, update, or delete coupons. Unauthenticated requests must not be able to create or manipulate coupons (authorization and abuse prevention). Coupons are **not hard-deleted** so that `analysis_requests` can keep a valid reference to the coupon used; the **analysis_requests** table is altered to include the coupon used (e.g. `discount_coupon_id`). Every time a coupon is used (after payment confirmation), **increment** `times_used` (for all coupon types, not only "after X uses").

- **Coupon model:** Table (e.g. `discount_coupons`) with: `id` (UUID), `code` (string, unique), `value` (integer 0–100, percentage), and expiration fields: e.g. `expires_at` (nullable timestamp), `max_uses` (nullable integer), `times_used` (integer, default 0). "Never expires" = both `expires_at` and `max_uses` null.

- **analysis_requests:** Add a nullable column (e.g. `discount_coupon_id`) referencing `discount_coupons.id`. When the user applies a valid coupon at checkout, store the coupon id on the analysis request so the webhook can increment usage and the record keeps the link to the coupon used.

- **Expiration types:**
  - **Never expires:** Coupon valid for new uses until an admin disables or soft-deletes it (if implemented).
  - **After X days:** `expires_at` set at creation; when `now() > expires_at`, coupon is **invalid for new uses** at checkout. The coupon is **not deleted**; it remains in the database.
  - **After X uses:** `max_uses` set at creation. When `times_used >= max_uses`, the coupon is **invalid for new uses** at checkout. The coupon is **not deleted**; it remains in the database.

- **Usage:** On every successful payment that used a coupon (webhook), **increment** the coupon’s `times_used`, regardless of whether the coupon expires by uses or not. This keeps consistent usage stats and supports "after X uses" invalidation.

- **Core CRUD:** Routes under `/core/` (e.g. `/core/coupons` or `/core/discount-coupons`) for index, create, store, edit, update, destroy. All protected by `auth` (and `verified` if required for core). Admin "delete" must **not** hard-delete; implement soft delete (e.g. `deleted_at`) or an "inactive" flag so the row remains for referential integrity. No scheduler job that deletes coupon rows.

- **Checkout:** On `/start-growth`, user can enter a coupon code. Backend validates: code exists, not expired (`expires_at` null or `expires_at >= now()`), not exhausted (`max_uses` null or `times_used < max_uses`). Apply discount (percentage of amount) and store the coupon id on the analysis request. On payment confirmation (Stripe webhook), increment the coupon’s `times_used`.

---

## How to test

- **Authorization:** Unauthenticated request to create/update/delete coupon returns 401/302 to login. Only authenticated users can access CRUD.
- **CRUD:** Create coupon (code, value, expiration type); list coupons; edit and update; delete (soft delete or hide, record remains in DB). Validation: duplicate code rejected; value outside 0–100 rejected.
- **analysis_requests:** When a coupon is applied and payment succeeds, the analysis request row has `discount_coupon_id` set to the coupon used.
- **Usage:** After each payment that used a coupon, the coupon’s `times_used` is incremented (for never-expires, after X days, and after X uses).
- **Never expires:** Coupon can be used repeatedly; `times_used` increments each time; coupon row is never deleted.
- **After X days:** After expiry, coupon invalid at checkout; coupon row remains in DB.
- **After X uses:** After `times_used >= max_uses`, coupon invalid at checkout; coupon row remains in DB.
- **Checkout:** Apply valid coupon at `/start-growth`; amount is reduced by percentage; payment succeeds; webhook increments `times_used`; analysis request has `discount_coupon_id` set.
- **Abuse prevention:** Unauthenticated or forged creation/update of coupons is rejected.

---

## Acceptance criteria

- [ ] Migration for discount coupons table: id (UUID), code (unique), value (0–100), expires_at (nullable), max_uses (nullable), times_used (default 0), timestamps.
- [ ] Migration (or alter) for analysis_requests: add nullable `discount_coupon_id` (foreign key to discount_coupons.id).
- [ ] CRUD routes under `/core/*` protected by auth; only authenticated users can create, read, update, delete coupons.
- [ ] Admin UI (core) to list, create, edit, update, delete coupons; expiration type (never / after X days / after X uses) and related fields; validation (unique code, value 0–100). Delete does not hard-delete (soft delete or inactive flag).
- [ ] Checkout at `/start-growth`: user can enter coupon code; backend validates and applies percentage discount; discounted amount used for Stripe payment; store coupon id on analysis request.
- [ ] On payment confirmation (webhook): increment coupon `times_used`; set or keep `discount_coupon_id` on analysis request. Do not delete the coupon.
- [ ] Coupons are never hard-deleted (no scheduler that deletes rows; expired/exhausted coupons remain in DB and are only invalid for new uses).
- [ ] Unauthenticated users cannot create or manipulate coupons; authorization and validation prevent abuse (e.g. prompt injection, forged requests).

---

## Deployment notes

- Run migration for `discount_coupons` and for the new column on `analysis_requests`. No scheduler job for deleting coupons. Optional: soft delete or "active" flag and admin filters for listing.

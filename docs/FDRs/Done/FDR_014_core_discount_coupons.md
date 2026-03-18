# FDR-014: Core discount coupons (admin CRUD and checkout)

**Feature:** 14 (discount coupons in core)  
**Reference:** docs/04 - Features.md, ADR-023

---

## How it works

- **Scope:** Logged-in users (admins) manage discount coupons in the core panel (`/core/*`). Coupons are applied at checkout on `/start-growth` as a percentage discount (0–100%). CRUD is protected by authentication; only authenticated users can create, read, update, or delete coupons. Unauthenticated requests must not be able to create or manipulate coupons (authorization and abuse prevention). Coupons are **not hard-deleted** so that `analysis_requests` can keep a valid reference to the coupon used; the **analysis_requests** table is altered to include the coupon used (e.g. `discount_coupon_id`). Every time a coupon is used (after payment confirmation), **increment** `times_used` (for all coupon types, not only "after X uses").

- **Coupon model:** Table (e.g. `discount_coupons`) with: `id` (UUID), `code` (string, unique), `value` (integer 0–100, percentage), `expires_at` (nullable), `max_uses` (nullable), `times_used` (default 0), `deleted_at` (nullable, for soft delete), timestamps. "Never expires" = both `expires_at` and `max_uses` null. All deletion is soft delete so the row stays and `analysis_requests` never loses the reference (no ON DELETE SET NULL needed).

- **analysis_requests:** Add a nullable column (e.g. `discount_coupon_id`) referencing `discount_coupons.id`. When the user applies a valid coupon at checkout, store the coupon id on the analysis request so the webhook can increment usage and the record keeps the link to the coupon used.

- **Expiration types:**
  - **Never expires:** Coupon valid for new uses until an admin soft-deletes it.
  - **After X days:** `expires_at` set at creation; when `now() > expires_at`, coupon is **invalid for new uses** at checkout. Scheduler will soft-delete these (row remains).
  - **After X uses:** `max_uses` set at creation; when `times_used >= max_uses`, coupon is **invalid for new uses** at checkout. Scheduler will soft-delete these (row remains).

- **Usage:** On every successful payment that used a coupon (webhook), **increment** the coupon’s `times_used`, regardless of expiration type.

- **Core CRUD:** Routes under `/core/` for index, create, store, edit, update, destroy. All protected by `auth` (and `verified` if required). Admin "delete" is **soft delete** (e.g. `deleted_at`); row remains so the reference is preserved.

- **Scheduler:** A scheduled job runs periodically and **soft-deletes** invalid coupons: (1) expired (`expires_at` is not null and `expires_at < now()`), (2) exhausted (`max_uses` is not null and `times_used >= max_uses`). Because it is soft delete, the row stays in the database and `analysis_requests.discount_coupon_id` never loses the reference.

- **Checkout:** On `/start-growth`, user can enter a coupon code. Backend validates: code exists, not soft-deleted, not expired, not exhausted. Apply discount and store the coupon id on the analysis request. On payment confirmation (webhook), increment the coupon’s `times_used`.

---

## How to test

- **Authorization:** Unauthenticated request to create/update/delete coupon returns 401/302 to login. Only authenticated users can access CRUD.
- **CRUD:** Create coupon (code, value, expiration type); list coupons; edit and update; delete (soft delete or hide, record remains in DB). Validation: duplicate code rejected; value outside 0–100 rejected.
- **analysis_requests:** When a coupon is applied and payment succeeds, the analysis request row has `discount_coupon_id` set to the coupon used.
- **Usage:** After each payment that used a coupon, the coupon’s `times_used` is incremented (for never-expires, after X days, and after X uses).
- **Never expires:** Coupon can be used repeatedly; `times_used` increments each time; only admin can soft-delete.
- **After X days:** After expiry, coupon invalid at checkout; scheduler soft-deletes it (row remains).
- **After X uses:** After `times_used >= max_uses`, coupon invalid at checkout; scheduler soft-deletes it (row remains).
- **Scheduler:** Job soft-deletes coupons that are expired or exhausted; assert soft-deleted rows still exist and analysis_requests references remain valid.
- **Checkout:** Apply valid coupon at `/start-growth`; amount is reduced by percentage; payment succeeds; webhook increments `times_used`; analysis request has `discount_coupon_id` set.
- **Abuse prevention:** Unauthenticated or forged creation/update of coupons is rejected.

---

## Acceptance criteria

- [x] Migration for discount coupons table: id (UUID), code (unique), value (0–100), expires_at (nullable), max_uses (nullable), times_used (default 0), deleted_at (nullable), timestamps. Model uses SoftDeletes.
- [x] Migration (or alter) for analysis_requests: add nullable `discount_coupon_id` (foreign key to discount_coupons.id). No ON DELETE SET NULL (soft delete keeps the row).
- [x] CRUD routes under `/core/*` protected by auth; only authenticated users can create, read, update, delete coupons. Admin delete = soft delete.
- [x] Admin UI (core) to list, create, edit, update, delete coupons; expiration type (never / after X days / after X uses) and related fields; validation (unique code, value 0–100). Delete = soft delete.
- [x] Checkout at `/start-growth`: user can enter coupon code; backend validates (exists, not soft-deleted, not expired, not exhausted) and applies percentage discount; store coupon id on analysis request. On payment confirmation (webhook): increment coupon `times_used`.
- [x] Scheduler job: periodically soft-delete invalid coupons (expired: expires_at < now(); exhausted: times_used >= max_uses). Row remains so analysis_requests never loses the reference.
- [x] Coupons are never hard-deleted; only soft delete (admin and scheduler). Reference from analysis_requests is always valid.
- [x] Unauthenticated users cannot create or manipulate coupons; authorization and validation prevent abuse (e.g. prompt injection, forged requests).

---

## Deployment notes

- Run migration for `discount_coupons` (include `deleted_at` for soft delete) and for the new column on `analysis_requests`. Enable Laravel Scheduler (cron) for the job that soft-deletes invalid coupons (expired and exhausted). No ON DELETE SET NULL on the FK; soft delete keeps the row so the reference is never lost.

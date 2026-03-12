# FDR-014: Core discount coupons (admin CRUD and checkout)

**Feature:** 14 (discount coupons in core)  
**Reference:** docs/04 - Features.md, ADR-023

---

## How it works

- **Scope:** Logged-in users (admins) manage discount coupons in the core panel (`/core/*`). Coupons are applied at checkout on `/start-growth` as a percentage discount (0–100%). CRUD is protected by authentication; only authenticated users can create, read, update, or delete coupons. Unauthenticated requests must not be able to create or manipulate coupons (authorization and abuse prevention, including prompt injection or forged requests). Deletion is **hard delete** (remove from database).

- **Coupon model:** Table (e.g. `discount_coupons`) with: `id` (UUID), `code` (string, unique), `value` (integer 0–100, percentage), and expiration fields: e.g. `expires_at` (nullable timestamp), `max_uses` (nullable integer), `times_used` (integer, default 0). "Never expires" = both `expires_at` and `max_uses` null.

- **Expiration types:**
  - **Never expires:** Coupon valid until manually deleted.
  - **After X days:** `expires_at` set at creation; when `now() > expires_at`, coupon is invalid. A **scheduler** runs a job that hard-deletes rows where `expires_at` is not null and `expires_at < now()`.
  - **After X uses:** `max_uses` set at creation. Each time the coupon is used (after payment is confirmed in the webhook), usage is applied (e.g. `times_used` incremented). When `times_used >= max_uses`, the coupon is hard-deleted from the database.

- **Core CRUD:** Routes under `/core/` (e.g. `/core/coupons` or `/core/discount-coupons`) for index, create, store, edit, update, destroy. All protected by `auth` (and `verified` if required for core). UI in the admin panel (Vuetify/branding per FDR-013 when applicable). Create/update forms validate: code unique (on create), value 0–100, expiration type and related fields (days or max_uses). Prevent unauthenticated access and input abuse (sanitization, authorization checks).

- **Checkout:** On `/start-growth`, user can enter a coupon code. Backend validates: code exists, not expired (`expires_at` null or `expires_at >= now()`), not exhausted (`max_uses` null or `times_used < max_uses`). Apply discount (percentage of amount) and pass discounted amount to Stripe. On payment confirmation (Stripe webhook), record coupon usage (increment `times_used`); if `times_used >= max_uses`, hard-delete the coupon.

- **Deletion:** Admin delete action and scheduler/exhaustion logic perform **hard delete** (remove row from database).

---

## How to test

- **Authorization:** Unauthenticated request to create/update/delete coupon returns 401/302 to login. Only authenticated users can access CRUD.
- **CRUD:** Create coupon (code, value, expiration type); list coupons; edit and update; delete (record removed from DB). Validation: duplicate code rejected; value outside 0–100 rejected.
- **Never expires:** Coupon with no expiration can be used repeatedly until deleted.
- **After X days:** Create coupon with expires_at in the past or near future; after expiry it is invalid at checkout. Scheduler job deletes expired rows; assert table has no expired rows after run.
- **After X uses:** Create coupon with max_uses = 1 (or 2); use at checkout and complete payment; after confirmation, usage incremented; after max_uses reached, coupon invalid and hard-deleted.
- **Checkout:** Apply valid coupon at `/start-growth`; amount is reduced by percentage; payment succeeds; webhook runs and usage is recorded (and coupon deleted if exhausted).
- **Abuse prevention:** Attempts to create or apply coupons from unauthenticated or forged contexts are rejected; no coupon created or applied.

---

## Acceptance criteria

- [ ] Migration for discount coupons table: id (UUID), code (unique), value (0–100), expires_at (nullable), max_uses (nullable), times_used (default 0), timestamps.
- [ ] CRUD routes under `/core/*` protected by auth; only authenticated users can create, read, update, delete coupons.
- [ ] Admin UI (core) to list, create, edit, update, delete coupons; expiration type (never / after X days / after X uses) and related fields; validation (unique code, value 0–100).
- [ ] Checkout at `/start-growth`: user can enter coupon code; backend validates and applies percentage discount; discounted amount used for Stripe payment.
- [ ] On payment confirmation (webhook): record coupon usage (increment times_used); if times_used >= max_uses, hard-delete coupon.
- [ ] Scheduler job: hard-delete coupons where expires_at is not null and expires_at < now().
- [ ] All coupon deletion (admin or automatic) is hard delete.
- [ ] Unauthenticated users cannot create or manipulate coupons; authorization and validation prevent abuse (e.g. prompt injection, forged requests).

---

## Deployment notes

- Enable Laravel Scheduler (cron) for the expiration cleanup job. No new env vars required for basic behaviour; optional: config for job schedule frequency.

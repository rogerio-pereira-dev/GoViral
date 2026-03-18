# FDR-015: Discount coupon expiration by date

**Feature:** 14 (core discount coupons – expiration model refactor)  
**Reference:** docs/04 - Features.md, ADR-023, FDR-014

---

## How it works

- **Goal:** Simplify and make more explicit the expiration model of discount coupons by moving from a “after X days” relative expiration to an “after date X” absolute expiration, and by treating the `expires_at` field as a **date-only** value instead of a full timestamp.
- **Data model:**  
  - The `discount_coupons.expires_at` column represents a **calendar date** (no time-of-day semantics). A coupon with `expires_at = 2026-03-31` is valid for new uses **until the end of that day** in the application’s timezone; implementation details (casts/comparison) must respect this invariant.  
  - Coupons that never expire continue to have `expires_at = null` and `max_uses = null`.  
  - Coupons that expire **by usage count** continue to use `max_uses` and `times_used` exactly as in FDR-014 (no behavior change).
- **Expiration types (admin UI and requests):**
  - **Never expires:** same behavior as FDR-014 — coupon remains valid until soft-deleted or exhausted by usage (if `max_uses` is set).  
  - **After date X (absolute date):** Admin selects a concrete calendar date (e.g. `2026-04-30`) in the core UI. No relative “days from now” calculations are performed during create/update. The backend simply stores the provided date as `expires_at`.  
  - **After X uses:** unchanged — admin provides a maximum number of uses; when `times_used >= max_uses`, the coupon becomes invalid for new uses.
- **Validation at checkout:**  
  - A coupon is considered valid for checkout when:
    - It is not soft-deleted.  
    - For date-based expiration: the **current date** is **on or before** `expires_at`.  
    - For usage-based expiration: `max_uses` is null or `times_used < max_uses`.  
  - The checkout flow continues to reject coupons that are soft-deleted, already expired by date, or exhausted by usage.
- **Scheduler and retention:**  
  - The scheduler job that soft-deletes invalid coupons remains in place, but now considers the **date-only semantics** of `expires_at` when deciding if a coupon is expired.  
  - Soft-deleted coupons remain in the database; `analysis_requests.discount_coupon_id` keeps the reference exactly as in FDR-014.
- **Frontend behavior (core panel UI):**
  - The “expiration type” control in the admin coupon form replaces the option “after X days” with “after date X”.  
  - When “after date X” is selected:
    - The form shows a **single date picker / date field** for the exact expiration date.  
    - The form no longer asks “number of days from now”; it always works with an explicit date value.  
    - When editing an existing coupon with date-based expiration, the UI loads the persisted `expires_at` date into the field; no recalculation into “days remaining” is performed.
- **Tests:**  
  - Backend tests cover the new date-based semantics (`expires_at` as a date-only value, inclusive validity on the expiration day) and ensure no regressions for “never” and “after X uses”.  
  - Frontend/browser tests cover creating, editing and using coupons with “after date X” expiration from the core panel and from the checkout flow.

---

## How to test

- **Data model and behavior:**
  - Create a coupon with:
    - `expiration_type = never`: `expires_at` and `max_uses` remain null; coupon is valid until soft-deleted or exhausted by usage (if applicable).  
    - `expiration_type = date` (or equivalent): `expires_at` stored as the exact date chosen in the UI, with no “days from now” calculation.  
    - `expiration_type = uses`: `max_uses` populated, `expires_at` can be null; behavior matches FDR-014.
  - For a date-based coupon (`expires_at = 2026-04-30`):  
    - On a day strictly **before** 2026-04-30, checkout accepts the coupon (assuming other validations pass).  
    - On 2026-04-30, checkout still accepts the coupon.  
    - On a day strictly **after** 2026-04-30, checkout rejects the coupon as expired.
- **Scheduler:**
  - For date-based coupons: after the day following `expires_at`, the scheduler soft-deletes the coupon as expired and the row remains in the database.  
  - For usage-based coupons: when `times_used >= max_uses`, the scheduler soft-deletes them as exhausted, with references from `analysis_requests` preserved.
- **Core admin UI:**
  - The create/edit coupon form:
    - Does not contain the “after X days” input anymore.  
    - Shows an “after date X” option with a date field.  
    - When editing: loads the stored expiration date into the date field for date-based coupons.  
  - Submitting the form with valid data for each expiration type (never, date, uses) succeeds and persists the expected attributes.
- **Checkout flow:**
  - At `/start-growth`, entering a valid date-based coupon reduces the amount correctly and associates the coupon with the `analysis_requests` record.  
  - After payment confirmation, `times_used` is incremented for the coupon, regardless of whether it was date-based or usage-based, preserving existing business rules from FDR-014.  
  - The flow rejects:
    - Soft-deleted coupons.  
    - Date-based coupons past their expiration date.  
    - Usage-based coupons with `times_used >= max_uses`.
- **Regression tests:**
  - Existing flows for:
    - “Never expires” coupons.  
    - “After X uses” coupons.  
  - Continue to behave exactly as before with no breaking changes to admins or end users except for the more explicit date-based configuration in the admin UI.

---

## Acceptance criteria

- [ ] `discount_coupons.expires_at` is treated as a **date-only** field across the application (model casts, queries, validation, scheduler), with clear and consistent semantics: coupons are valid through the end of the expiration date.  
- [ ] The admin coupon form in the core panel replaces the “after X days” expiration option with “after date X”, exposing a single date field with no “number of days from now” calculations.  
- [ ] Creating or editing a coupon with “after date X” persists the exact calendar date provided by the admin as `expires_at` and uses that date for all expiration checks.  
- [ ] Checkout validation correctly accepts date-based coupons on or before the expiration date, and rejects them after that date, while preserving existing logic for “never” and “after X uses”.  
- [ ] The scheduler job uses the updated date-only semantics of `expires_at` to soft-delete expired coupons; exhausted coupons by usage continue to be soft-deleted as defined in FDR-014.  
- [ ] Backend tests are updated/added to cover date-based expiration behavior and ensure no regressions for existing coupon types.  
- [ ] Frontend/browser tests are updated/added to cover the new “after date X” UI in the core coupon screens and the end-to-end checkout behavior with date-based coupons.

---

## Deployment notes

- Run the migration(s) that align the `discount_coupons.expires_at` storage and casting with date-only semantics (if required by the chosen implementation), ensuring backward-compatible handling of any existing timestamp values.  
- After deployment, run the full automated test suite (including Browser tests for the core coupon screens and checkout flow) to verify that the new date-based expiration behavior works end to end and that “never expires” and “after X uses” coupons remain fully functional.


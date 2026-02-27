# FDR-004: Payment

**Feature:** 4 (overview; details in FDR_004_1, FDR_004_2, FDR_004_3)  
**Reference:** docs/04 - Features.md, ADR-007, ADR-016, ADR-015

---

## How it works

- **Payment on the same page as the form:** checkout is not an external page (redirect to Stripe Hosted Checkout). The payment field (Stripe Elements or equivalent) sits **on the form page itself**. The user fills the form (FDR-003) and, on the same screen, enters card details and completes payment. After payment is confirmed, the user is **redirected to the Thank You page** stating they will receive the report by email within 30 minutes.
- Sub-features: **4.1** Install/configure Stripe (FDR_004_1); **4.2** Process payment on the form page and redirect to Thank You (FDR_004_2); **4.3** Payment confirmation webhook (FDR_004_3).

---

## How to test

- Full flow: fill form + pay on same page → redirect to `/thank-you` (or equivalent) with message "report by email within 30 minutes"; webhook confirms payment and enqueues job.
- See criteria and tests in FDR_004_1, FDR_004_2, FDR_004_3.

---

## Acceptance criteria

- [ ] Payment available **on the form page** (no redirect to external checkout page).
- [ ] After successful payment: redirect to Thank You page with message about receiving the report by email within 30 minutes.
- [ ] Webhook validates signature; updates record and enqueues job (see FDR_004_3).

---

## Deployment notes

- See FDR_004_1, FDR_004_2, FDR_004_3 for env, Stripe Dashboard, and webhook.

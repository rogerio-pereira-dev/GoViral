# FDR-004.2: Checkout on form page and Thank You page

**Feature:** 4.2  
**Reference:** FDR-004, FDR-003, ADR-007, ADR-015

---

## How it works

- **Checkout on the same page as the form:** the payment field (Stripe Elements / Payment Element or embedded Checkout Session) is on the form page. The user fills the form data (FDR-003) and, on the same screen, enters card details and confirms payment. There is no redirect to an external Stripe page (Stripe Hosted Checkout); the flow is "form + payment on the same page".
- **Flow order:** (1) User fills form (email, username, bio, etc.; locale is already the page locale). (2) Backend may create a record in `analysis_requests` (payment_status = pending) and return client secret or session id to the frontend, or the frontend first submits form data and then starts payment — per implementation (Stripe Payment Element + Payment Intent or Checkout Session with `mode: payment`). (3) User completes payment on the same page. (4) After success (confirmed on frontend), **redirect** the user to the **Thank You page**.
- **Thank You page:** dedicated route (e.g. `/thank-you`). Clear message: the user will receive the report by email **within 30 minutes**. Content translated (Laravel localization) per locale. No form or payment button; optional: link back to home or landing.
- Actual payment confirmation for the backend is via **webhook** (FDR-004.3); the redirect to the Thank You page happens as soon as the frontend receives success from Stripe (do not wait for the webhook to redirect).

---

## How to test

- **Happy path:** Fill form; enter test card (4242...); complete payment on same page; redirect to `/thank-you`; page shows message "report by email within 30 minutes" (or equivalent translation).
- **Cancel payment:** user cancels or payment fails; remains on form page; can try again.
- **Language:** Thank You page in correct locale (en/es/pt).
- **Edge cases:** (1) Double-click on "Pay": avoid creating two payments or two records. (2) Slow network: loading feedback during payment confirmation.

---

## Acceptance criteria

- [x] Payment field (Stripe) on the **same page** as the form; no redirect to external Stripe page.
- [x] After successful payment: redirect to Thank You page.
- [x] Thank You page displays message that the report will be sent by email within 30 minutes; text translated (Laravel lang).
- [x] Thank You page locale consistent with session/page locale (en/es/pt).

---

## Deployment notes

- Stripe publishable key on frontend (env); in production use live keys. Thank You page can be static (view only) or a named route for easier redirect.

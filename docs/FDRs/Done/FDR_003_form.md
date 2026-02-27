# FDR-003: Form

**Feature:** 3  
**Reference:** docs/04 - Features.md, ADR-017

---

## How it works

- Form collects: **email**, TikTok username, current bio, aspiring niche, video link 1, video link 2, video link 3, notes (optional). **There is no locale field in the form:** locale is the page locale, set at the top (Landing or the form page header — FDR-002). On submit, the backend uses the current application locale (session or request) to persist in `analysis_requests.locale`.
- **Email field:** must make it **explicit** (label, placeholder, or help text) that a **valid email** is required, because the analysis report will be sent by email. This reduces the risk of the user entering a fake email, paying, not receiving the report, and having to pay again.
- **Validation:** valid email (format and existence when possible); valid URLs for the 3 links; max lengths per field (business rules); backend sanitization for XSS/injection (ADR-017).
- **Checkout:** payment is Feature 4 (FDR-004) and will be added later; when implemented, it will be on the same page as the form. This FDR covers the form fields, validation, and redirect to the Thank You page.
- **Redirect to Thank You page:** after a valid form submit, the user is **redirected to the Thank You page** stating they will receive the report by email within 30 minutes. This redirect is implemented **in this feature (FDR-003)**. That way the full form can be tested during development without a card (or test cards); payment (FDR-004) is added next, and the Thank You page will then only be reached after payment is completed on the same page.
- **Microcopy:** labels and button per Branding (e.g. "Start My Growth"); validation messages in the page locale (Laravel translations).

---

## How to test

- **Happy path:** Page with locale already set (top); fill all fields with valid data; email field shows text explaining the report will be sent by email and that the email must be valid; valid submit → **redirect to Thank You page** (message: report by email within 30 min). Record created in backend (payment_status = pending). When FDR-004 is active, flow will be form + payment on same page → Thank You.
- **Validation:** (1) Invalid email → error message, no submit. (2) Invalid URL on any of the 3 links → error. (3) Required fields empty (except notes) → error. (4) Locale: no field on form; use page/session locale.
- **Edge cases:** (1) Double submit (double-click): avoid duplicating record (handled in flow with payment — FDR-004). (2) Special characters in bio/notes: sanitization without breaking display. (3) URLs with query params: accept if base URL is valid. (4) **Email copy:** verify the user clearly sees that a valid email is required to receive the report.

---

## Acceptance criteria

- [x] Fields: email (with explicit text that it must be valid for report delivery), username, bio, niche, 3 URLs, notes optional; **no** locale field (page locale).
- [x] Format validation (email, URLs) on frontend and backend; max lengths applied.
- [x] Backend input sanitization (ADR-017).
- [x] Branding microcopy; error messages in the locale language (Laravel translations).
- [x] After valid submit: redirect to Thank You page with message "report by email within 30 minutes" (implemented in this feature; allows testing the form without payment during development).

---

## Deployment notes

- No extra dependencies; validation translations covered by Laravel (lang) and the locale set on the landing/top.

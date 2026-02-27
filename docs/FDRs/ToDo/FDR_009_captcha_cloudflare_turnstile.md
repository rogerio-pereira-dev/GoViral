# FDR-009: Captcha on form (Cloudflare Turnstile)

**Feature:** 9  
**Reference:** docs/04 - Features.md, ADR-018

---

## How it works

- On the form (FDR-003), the Cloudflare Turnstile widget is shown (managed or invisible mode per product choice). User interacts if needed; Turnstile generates a token.
- On submit, the frontend sends the token with the other fields (e.g. field `turnstile_token` or `cf-turnstile-response`). The backend, before creating the record in `analysis_requests` and the Stripe Checkout session, validates the token by calling the Turnstile API (siteverify). If validation fails (token missing, invalid, expired, wrong domain), the request is rejected (e.g. 422) with an appropriate message; no record or session is created. There is no rate limiting for real users (ADR-018); the captcha is the anti-bot barrier.

---

## How to test

- **Happy path:** User fills form and completes Turnstile (if visible); submit with token; backend validates token; creates record and Checkout; redirect to Stripe (or stays on page when payment is on same page).
- **Token missing:** Submit without token (e.g. script simulating bot); backend returns 422; no record created; error message shown.
- **Token invalid/expired:** Fake or expired token; siteverify returns failure; backend returns 422; no record created.
- **Edge cases:** (1) Multiple quick submits: each needs a new token (Turnstile generates one at a time). (2) Domain: in dev, use Turnstile test key or configure local domain in the dashboard. (3) User with script blocker: Turnstile may not load; define fallback (e.g. message "enable JavaScript" or product-controlled degradation). (4) Turnstile API timeout: set short timeout on backend; on network failure to siteverify, treat as invalid (422) or retry once, per policy.

---

## Acceptance criteria

- [ ] Turnstile widget integrated on the form; token sent on submit.
- [ ] Backend validates token with Turnstile API before persisting and creating Checkout; on validation failure returns 4xx and clear message.
- [ ] No rate limiting for users (captcha only as anti-bot control — ADR-018).
- [ ] Keys (site key / secret) configured via env; correct domain in Cloudflare Turnstile.

---

## Deployment notes

- Publishable key (site key) on frontend (env or build); secret key only on backend (env). In production, register domain in Cloudflare Turnstile. Different environments (staging/prod) may use different keys.

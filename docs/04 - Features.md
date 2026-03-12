# GoViral — Feature List

**Version:** 1.0  
**Date:** 2026-02-26  
**References:** PRD, HLD, Branding Manual, ADRs

Each feature is described in isolation; when there are dependencies, other features are referenced.

---

## 1. Configure Vue + Vuetify per Branding Manual

**Objective:** Align the frontend with the visual and voice identity manual (docs/03 - Branding Manual.md).

**Scope:**
- Vuetify theme in dark mode: base background #121212 (dark charcoal).
- Primary colours: Pink #FE2C55, Teal #25F4EE; neon glow accents on CTAs and highlights.
- Typography: Space Grotesk (headlines), Inter (body); clear hierarchy and high contrast.
- UI: smooth transitions, subtle glow on interactive elements, micro-interactions, clean layout.
- CTAs and microcopy per manual (e.g. "Start My Growth", "Generate My Growth Blueprint", "Analyzing Your Growth Potential...").
- Favicon/logo: Teal→Pink gradient, clean geometry, viral/lightning motion where applicable.

**Dependencies:** None (frontend foundation feature).  
**Related to:** Feature 2 (Landing Page), Feature 3 (Form).

---

## 2. Landing Page

**Objective:** Entry page that communicates value, positioning, and directs users to the form.

**Scope:**
- Content aligned with Branding Manual: tagline "Engineered for Viral Growth", subheadline "Turn insight into viral momentum in minutes", tone sharp/fast/smart.
- Product presentation (TikTok profile analysis, recommendations, 30-day plan).
- Primary CTA to start the flow (leads to the form).
- Language selection (locale) before or at the start of filling: English (en), Spanish (es), Portuguese (pt); value passed to form and report (Feature 3, Feature 8).
- Visual and components per Feature 1 (Vue + Vuetify + Branding).

**Dependencies:** Feature 1 (Vue + Vuetify setup).  
**Related to:** Feature 3 (Form), Feature 4 (Payment).

---

## 3. Form

**Objective:** Collect TikTok profile and user data to generate the analysis and deliver the report.

**Scope:**
- Fields: email, TikTok username, current bio, aspiring niche, links to last 3 videos, notes (optional).
- Language field or selector (locale): en | es | pt; must be set (via landing or on the form itself).
- Input validation (email format, valid URLs, max lengths); sanitization to avoid XSS/injection (ADR-017).
- Cloudflare Turnstile (captcha) on the form to mitigate bots (Feature 9; ADR-018); token sent on submit and validated on the backend.
- Submit sends data to the backend; backend persists in `analysis_requests` (payment_status = pending), creates Stripe Checkout session and redirects to payment (Feature 4).
- Messages and labels in the selected language where applicable; microcopy per Branding (e.g. "Start My Growth").

**Dependencies:** Feature 1, Feature 2 (locale may come from landing).  
**Related to:** Feature 4 (Payment), Feature 9 (Captcha).

---

## 4. Payment

**Objective:** Charge one-time payment (~US$ 20) in USD via Stripe and, after confirmation, trigger analysis processing.

### 4.1 Install and configure Stripe

- Laravel Cashier (Stripe) installed and configured.
- Environment variables: Stripe keys (publishable, secret), webhook signing secret.
- Product/price configured in Stripe (one-time payment, USD); target amount configurable (e.g. $20).
- Stripe Checkout used as payment flow (hosted); success/cancel URLs configured.
- Webhook endpoint registered in Stripe for `checkout.session.completed` (or event used by in-page flow).

### 4.2 Perform payment

- After form submit (Feature 3), backend creates record in `analysis_requests` (pending), creates Stripe Checkout Session linked to the record (metadata with request id or email) and returns redirect URL.
- User is redirected to Stripe Checkout; completes payment in USD.
- After payment, Stripe redirects user to success URL ("thank you" or "report on the way" page); actual confirmation is via webhook (4.3).

### 4.3 Payment confirmation webhook

- Endpoint receives `checkout.session.completed` event.
- Validate webhook signature (ADR-016); reject with 4xx if invalid.
- Identify the record in `analysis_requests` (via session_id or metadata).
- Update `payment_status = paid`, `processing_status = queued`.
- Enqueue job (Feature 5) to process the analysis (initially "empty" job or with real steps per Job and LLM integration implementation).
- Respond 200 to Stripe quickly (processing is asynchronous — ADR-015).

**Dependencies:** Feature 3 (form produces the record to be paid).  
**Related to:** Feature 5 (Job), Feature 6 (Queue and worker), ADR-007, ADR-016.

---

## 5. Analysis processing job

**Objective:** Orchestrate, in background, report generation and email delivery after payment is confirmed.

**Scope:**
- Single job (e.g. `ProcessAnalysisRequest`) triggered by the webhook (Feature 4.3).
- Receives the analysis request id (UUID); loads record from `analysis_requests` (only payment_status = paid).
- Updates `processing_status = processing`, increments `attempt_count`.
- Calls LLM integration (Feature 7) to get analysis content (structured text or blocks per template).
- Builds HTML report (PRD structure: Executive Summary, Profile Score, Inferred Niche, Username Suggestions, Optimized Bio, Profile Optimization, Content Ideas, Viralization Tips, 30-Day Action Plan) using LLM output.
- Sends email with report in HTML (Feature 8).
- On success: update `processing_status = sent` and remove record (retention policy — ADR-011; see ADR-020 for current policy).
- On failure: store `last_error`, schedule retry (e.g. 5 min); after 12 failures, mark as failed and remove record (ADR-011). Retry configured via Laravel queue (Feature 6).

**Dependencies:** Feature 4 (webhook enqueues job), Feature 6 (queue/worker), Feature 7 (LLM), Feature 8 (email).  
**Related to:** Feature 6, Feature 7, Feature 8, ADR-015, ADR-011.

---

## 6. Configure queue and worker

**Objective:** Ensure asynchronous, scalable processing of analysis jobs (ADR-005, ADR-015).

**Scope:**
- Queue driver: Redis; Redis connection configured in Laravel.
- Job `ProcessAnalysisRequest` (or equivalent) implemented and enqueued by the webhook (Feature 4.3).
- Laravel worker(s) consuming the queue (local or Laravel Cloud); timeout and number of attempts configured (max 12, backoff e.g. 5 min).
- Local: `php artisan queue:work`; production: worker process(es) provided by the environment (Laravel Cloud or supervisor/cron).
- Recommended monitoring: job failures, queue size (operational — HLD and ADR-017).

**Dependencies:** Feature 4 (webhook), Feature 5 (job definition).  
**Related to:** Feature 4, Feature 5, ADR-005.

---

## 7. LLM integration

**Objective:** Obtain from the LLM provider the structured analysis content for report assembly (Feature 5). Provider and approach were deferred (ADR-014); this feature includes research, decision and implementation.

### 7.1 LLM provider research and decision

- Technical spike: evaluate candidate providers (OpenAI, Gemini, Anthropic) on cost per request, output quality and latency.
- Evaluate Laravel packages (adapters, SDKs) and integration pattern: adapter/strategy in Laravel (interface `LlmClient`, selection via env) vs. external orchestration (e.g. n8n).
- Produce implementation ADR (chosen provider + approach) and update ADR-014 when the decision is made.
- Define contract/interface in code (e.g. method that receives form payload + locale and returns structured text or blocks) to keep architecture agnostic until the decision.

### 7.2 Integration

- Implement client (adapter) for the chosen provider; configure via environment variables (API key, model, etc.).
- Integrate into the Job pipeline (Feature 5): call LLM with `analysis_requests` data and locale; handle timeout and errors (retry at job level per Feature 6).

### 7.3 Get report

- Build prompt from template (docs/LLM Prompt Template.md): placeholders USERNAME, BIO, NICHE, VIDEO_1/2/3, NOTES, LANGUAGE.
- Send request to LLM; parse response into the expected structure (report sections).
- Return content to the Job for HTML generation and email delivery (Feature 5, Feature 8).

**Dependencies:** ADR-014 (decision deferred; 7.1 unblocks 7.2 and 7.3).  
**Related to:** Feature 5, Feature 8, docs/LLM Prompt Template.md.

---

## 8. Email delivery with report

**Objective:** Deliver the HTML report to the user by email after successful processing (ADR-010).

**Scope:**
- Provider: AWS SES; sender configured (e.g. report@goviral.you); domain verified, DKIM/SPF (HLD).
- Laravel Mail: mailable that receives report HTML and recipient email; message body in HTML (not attachment).
- Subject and plain text per branding; language may reflect request locale.
- Called by the Job (Feature 5) after getting LLM content (Feature 7) and building the HTML.
- Send failure handling: retry by job (Feature 6); after 12 failures record is removed (ADR-011).
- Do not store the report; do not send PDF in MVP (ADR-010).

**Dependencies:** Feature 5 (Job orchestrates), Feature 7 (report content).  
**Related to:** Feature 5, Feature 7, ADR-006, ADR-010.

---

## 9. Captcha on form (Cloudflare Turnstile)

**Objective:** Reduce bot submissions on the form without limiting real users (avoid impact on revenue).

**Scope:**
- Integrate Cloudflare Turnstile on the form (Feature 3): widget on frontend, token sent on submit.
- Backend validates the token with the Turnstile API before creating the record in `analysis_requests` and the Stripe Checkout session; on validation failure, reject the submission with an appropriate message.
- Turnstile acts as anti-bot control; do not apply rate limiting by IP/user for real users (product decision — ADR-018).

**Dependencies:** Feature 3 (form).  
**Related to:** Feature 3, ADR-018.

---

## 10. Scheduler for data cleanup — Closed

**Status:** Closed. Analyses will be used as case studies; sent report content must be retained in the database. The scheduler cleanup approach is no longer desired. See ADR-020 (Data Retention — Retain for Case Studies) and FDR-011 (persist report before email). FDR-010 is in `docs/FDRs/Closed/`.

**Objective (original):** Apply the minimal retention policy (ADR-011): remove old or terminal-state records.

**Scope (original):** Laravel Scheduler, cleanup job (sent, > 24h, failed with 12 attempts).

---

## 11. Persist report in database before sending email

**Objective:** Ensure the report content (HTML) sent by email is stored in the database for internal case studies (ADR-020).

**Scope:**
- New migration: column(s) to store report HTML (e.g. `report_html` on `analysis_requests`, or dedicated table).
- In the Job (Feature 5): after generating report HTML, save it to the database **before** queueing/sending the email; then send the email. Order: generate HTML → persist → send.
- The `analysis_requests` record is not deleted after successful send; it remains with the stored report for internal use.

**Dependencies:** Feature 5 (Job), Feature 7 (report content), Feature 8 (email).  
**Related to:** ADR-020, FDR-011.

---

## 12. Conversion tracking for ads and shared public layout

**Objective:** Enable conversion tracking for paid ads (Google, Meta, TikTok) and ensure a consistent public layout so tracking (e.g. GTM) loads on all funnel pages.

**Scope:**
- **Conversion model:** Lead = visit to `/`; conversion starts at `/start-growth`, completes at `/thank-you`. These three moments are used to link ads (ADR-021).
- **Shared layout:** All public funnel pages (`/`, `/start-growth`, `/thank-you`) use the same header (logo, language selector) and footer (GoViral tagline) as the landing page. A single public layout component wraps these pages so the GTM container loads once and consistently.
- **Tracking:** Pixels and conversion tags are configured in Google Tag Manager (GTM), not hardcoded in the app. The app provides stable URLs and the shared layout; setup guides in `docs/Setup/` describe how to implement Facebook, Google, and TikTok conversions and how to configure GTM.

**Dependencies:** Feature 2 (Landing), Feature 3 (Form).  
**Related to:** ADR-021, FDR-012, docs/Setup/ (Facebook, Google, TikTok, GTM guides).

---

## 13. Auth and dashboard Vuetify branding

**Objective:** Refactor the default Laravel auth pages and the core dashboard to use Vue + Vuetify and the same visual identity as the public pages (GoViral branding). English only; no i18n for these pages.

**Scope:**
- Auth pages: Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge. Replace Tailwind/reka-ui with a Vuetify auth layout (goviralDark theme, gradient background, centered card, GoViral logo) and Vuetify form components.
- Dashboard: Refactor `Dashboard.vue` content to Vuetify (v-container, v-row, v-col, v-card) and same branding.
- Automated tests: Feature tests (no regressions) and Browser tests (smoke for auth/dashboard routes, at least one E2E flow e.g. login → dashboard).

**Dependencies:** Feature 1 (Vue + Vuetify branding).  
**Related to:** ADR-003, ADR-022, FDR-013, docs/03 - Branding Manual.md.

---

## 14. Core discount coupons (admin CRUD and checkout)

**Objective:** Allow logged-in users (admins) to create and manage discount coupons in the core panel (`/core/*`). Coupons are applied at checkout on `/start-growth` as a percentage (0–100%). Expiration: never, after X days (scheduler hard-deletes expired), or after X uses (decrement on payment confirmation, hard-delete when exhausted). CRUD is auth-protected; deletion is hard delete.

**Scope:**
- New table: id (UUID), code (unique), value (0–100), expires_at (nullable), max_uses (nullable), times_used.
- CRUD under `/core/*` (auth); admin UI to list, create, edit, delete coupons; validation (unique code, value 0–100, expiration type).
- Checkout: user enters code at `/start-growth`; backend validates and applies discount; on payment confirmation (webhook), record use and hard-delete if exhausted.
- Scheduler job to hard-delete coupons where expires_at has passed.
- Prevent unauthenticated creation or manipulation of coupons (authorization and abuse prevention).

**Dependencies:** Feature 3 (Form), Feature 4 (Payment); core routes (auth).  
**Related to:** ADR-023, FDR-014.

---

## Feature dependency summary

| Feature | Depends on | Blocks / feeds |
|--------|------------|----------------|
| 1. Vue + Vuetify Branding | — | 2, 3, 13 |
| 2. Landing Page | 1 | 3 (locale), 4 |
| 3. Form | 1, 2, 9 (captcha) | 4 |
| 4. Payment (4.1–4.3) | 3 | 5, 6 |
| 5. Job | 4, 6, 7, 8 | 8 (delivery), 10 (cleanup logic) |
| 6. Queue and worker | 4, 5 | 5 |
| 7. LLM integration (7.1–7.3) | ADR-014 (decision) | 5, 8 |
| 8. Email with report | 5, 7 | — |
| 9. Captcha Turnstile | 3 (form) | 3 |
| 10. Scheduler cleanup | — | — (closed) |
| 11. Persist report before email | 5, 7, 8 | — |
| 12. Conversion tracking + shared layout | 2, 3 | — |
| 13. Auth and dashboard Vuetify branding | 1 | — |
| 14. Core discount coupons | 3, 4 (core auth) | — |

Living document: new features or refinements should be added here and, when applicable, reflected in ADRs.

# Implementation Plan (Ralph)

This file is the shared state for the Ralph Loop. **Planning** mode creates or updates it; **Building** mode consumes it and marks tasks done.

Run Planning (see `.cursor/ralph/README.md`) to populate this list from `docs/FDRs/ToDo/` and the current codebase. Then run Building iterations to implement one task at a time.

---

## Feature branches (active)

Use one branch per feature. Create the branch when starting the first task of that feature, then reuse it for all remaining tasks of the same feature.

- Format: `<feature section name> -> <branch name>`
- Example: `Data layer (for Form and Payment) -> feat/data-layer`
- `Form (FDR-003) -> feat/form`
- `Stripe setup (FDR-004.1) -> feat/stripe-setup`
- `Queue and worker (FDR-006) -> feat/queue-worker`
- `Horizon (FDR-006) -> feature/horizon`
- `LLM (FDR-007) -> feat/llm`
- `Email report (FDR-008) -> feat/email-report`
- `Captcha (FDR-009) -> feat/captcha`
- `Persist report before email (FDR-011) -> feat/persist-report`
- `Conversion tracking + shared layout (FDR-012) -> feat/conversion-tracking-shared-layout`
- `Auth and dashboard Vuetify branding (FDR-013) -> feat/auth-dashboard-vuetify-branding`
- `Core discount coupons (FDR-014) -> feat/core-discount-coupons`

---

## Tasks

Prioritized by dependency and value (docs/04 - Features.md). One line per task. Do not assume something is missing — confirm in code first.

### Foundation (FDR-001: Vue + Vuetify + Branding) — done

- [x] Add Vuetify to the project (npm) and configure it alongside or replacing current UI (Tailwind/reka-ui) for GoViral pages.
- [x] Create Vuetify theme: dark mode, background #121212, primary Pink #FE2C55, Teal #25F4EE, neon/glow on CTAs (docs/03 - Branding Manual.md).
- [x] Load and apply typography: Space Grotesk (headlines), Inter (body) per Branding Manual.
- [x] Apply CTAs and microcopy from Branding Manual (e.g. "Start My Growth", "Generate My Growth Blueprint"); ensure components inherit theme.
- [x] Add favicon/logo with Teal→Pink gradient, clean geometry; ensure readable at small sizes.

### Landing (FDR-002) — done

- [x] Add Laravel localization: create `lang/en`, `lang/es`, `lang/pt` (or JSON) with keys for landing, form, and thank-you copy.
- [x] Add route/middleware or route parameter to set locale (en/es/pt) from user choice; persist in session; use `App::setLocale()` and pass to Inertia.
- [x] Replace Welcome page content with GoViral landing: tagline "Engineered for Viral Growth", subheadline "Turn insight into viral momentum in minutes", supporting copy; use Laravel translations.
- [x] Add language selector at top of landing; on change, set locale and re-render/reload with translated text.
- [x] Add primary CTA "Start My Growth" linking to route `/start-growth`; ensure CTA text is translated.

### Data layer (for Form and Payment)

- [x] Create migration for `analysis_requests` table per HLD (id UUID, email, tiktok_username, bio, aspiring_niche, video_url_1/2/3, notes, locale, stripe_checkout_session_id, stripe_payment_intent_id, payment_status, processing_status, attempt_count, last_error, timestamps).
- [x] Create `AnalysisRequest` model with fillable, casts (e.g. uuid), and any scopes needed.

### Form (FDR-003)

- [x] Add route `GET /start-growth` (and optional locale segment or query); controller returns Inertia form page with locale from session.
- [x] Create form page (Vue): fields email (with explicit help text that report is sent by email — valid email required), username, bio, aspiring_niche, video_url_1, video_url_2, video_url_3, notes (optional); no locale field (use page locale).
- [x] Add backend validation (Form Request): email format, valid URLs, max lengths; sanitize input per ADR-017 (XSS/injection).
- [x] Add POST endpoint for form submit: validate, create `AnalysisRequest` with payment_status=pending; redirect to Thank You page (for now, without payment — FDR-003 allows testing form without card).
- [x] Create Thank You page: route `/thank-you`, message "report by email within 30 minutes"; content translated (lang); optional link back to home.
- [x] Add form validation messages and labels in lang files (en, es, pt) for the form page locale.

### Stripe setup (FDR-004.1)

- [x] Install and configure Laravel Cashier (Stripe): composer require laravel/cashier; run Cashier migrations if any.
- [x] Add env vars: STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET; document in .env.example.
- [x] Document local Stripe CLI container flow in compose/setup docs for webhook forwarding.
- [x] Do not require preconfigured product/price in Stripe Dashboard; checkout defines item and amount dynamically.
- Register webhook in Stripe for `checkout.session.completed` (or event used by in-page flow); set success/cancel URLs (success = Thank You page).

### Checkout on form page and Thank You (FDR-004.2)

- [x] Integrate Stripe Payment Element (or Checkout Session with mode payment) on the same page as the form; no redirect to external Stripe Hosted Checkout.
- [x] Flow: form submit creates AnalysisRequest (pending), backend returns client secret or session ID; user pays on same page; on success redirect to `/thank-you`.
- [x] Ensure Thank You page shows message "report by email within 30 minutes" in correct locale (en/es/pt); Thank You route uses session locale.
- [x] Add browser/E2E coverage for payment scenarios: valid payment, declined card, and insufficient funds.

### Webhook (FDR-004.3)

- [x] Add POST route for Stripe webhook (`/webhooks/stripe`); validate signature with STRIPE_WEBHOOK_SECRET (ADR-016); reject with 4xx if invalid.
- [x] Handle `payment_intent.succeeded`: find AnalysisRequest by stripe_payment_intent_id; set payment_status=paid, processing_status=queued; dispatch ProcessAnalysisRequest job with record id; return 200 quickly.
- [x] Handle Stripe retries idempotently (e.g. skip if record already paid); if payment_intent_id not found, log and return 200.

### Queue and worker (FDR-006) — done

- [x] Set QUEUE_CONNECTION=redis; ensure Redis connection in config/database.php and .env; document for Sail/production.
- [x] Create job `ProcessAnalysisRequest` (stub or full): receives AnalysisRequest id; configure max 12 attempts, backoff (e.g. 5 min), job timeout (e.g. 120–300 s) for LLM + email.
- [x] Document running worker: `php artisan queue:work` (or queue name); production: Laravel Cloud, supervisor, or equivalent.
- [x] Install and configure Laravel Horizon (Redis queue dashboard + workers); enable Horizon in `docker/8.5/supervisord.conf`.
- [x] Split Horizon into two supervisors (analysis + emails) so both queues run; config in `config/horizon.php`.

### LLM (FDR-007, 007.1, 007.2, 007.3) — done

- [x] FDR-007.1: Run spike: compare OpenAI, Gemini, Anthropic (cost, quality, latency); choose provider and approach (Laravel adapter vs external); create implementation ADR; ~~update ADR-014~~ use ADR-019; define interface in code (e.g. `generateReport(array $payload, string $locale): array`).
- [x] FDR-007.2: Implement adapter for chosen provider; env (GEMINI_API_KEY per ADR-019); Job calls interface; timeout and API errors propagate for Job retry.
- [x] FDR-007.3: Build prompt from docs/LLM Prompt Template.md (placeholders USERNAME, BIO, NICHE, VIDEO_1/2/3, NOTES, LANGUAGE); call LLM via adapter; parse response into report sections; return structured content (or HTML) to Job; handle malformed response (last_error, no email); sanitize markdown→HTML if needed.

### Email report (FDR-008)

- [x] Configure mail for AWS SES: MAIL_MAILER=ses, sender (e.g. report@goviral.you); document DKIM/SPF for production.
- [x] Create Mailable (e.g. GrowthReportMail): accepts report HTML and recipient email; body HTML; subject and plain text per branding/locale.
- [x] Job (FDR-005) will queue the email after building HTML (use queue name "emails"); Job (report) failure triggers job retry; after 12 failures record removed (FDR-005).
- [x] Improve report email: branding styles (dark #121212, pink/teal), intro copy (confident, new chapter), full i18n (en/es/pt) for subject and body.

### Job orchestration (FDR-005)

- [x] Implement ProcessAnalysisRequest job fully: load record (payment_status=paid only); set processing_status=processing, attempt_count++; call LLM integration (FDR-007); build report HTML (sections per PRD); send email (FDR-008); on success: processing_status=sent, delete record; on failure: last_error, release with backoff; after 12 attempts: mark failed, delete record (ADR-011). Use queue name "analysis".
- [x] Ensure job is only dispatched by webhook (FDR-004.3); only process paid records; handle already-deleted or non-paid gracefully.

### Captcha (FDR-009)

- Read **Setup tutorial:** [docs/Setup/TURNSTILE_SETUP.md](../Setup/TURNSTILE_SETUP.md)
- [x] Add Cloudflare Turnstile widget to form page; send token (e.g. turnstile_token) on submit.
- [x] Backend: before creating AnalysisRequest and Stripe session, validate token with Turnstile siteverify API; on failure return 422 with clear message; keys via env (site key frontend, secret backend).

### Persist report before email (FDR-011)

- [x] Add migration: column `report_html` (longText, nullable) and optionally `sent_at` (timestamp, nullable) on `analysis_requests`.
- [x] Add `report_html` and `sent_at` to AnalysisRequest `$fillable`; cast `sent_at` to `datetime`.
- [x] In ProcessAnalysisRequest: after generating HTML, save `report_html` (and set `sent_at` when sending) to the record **before** calling Mail::queue; then queue email; then set `processing_status = sent`. On retry, if `report_html` is already set, use it and only resend email (no duplicate LLM call).
- [x] Ensure record is not deleted after successful send (already the case; remove or keep commented delete per ADR-020).
- [x] Add tests: persistence before send; persisted HTML matches email content; record retained with `processing_status = sent`; retry after persist only resends email (idempotent).

### Conversion tracking and shared public layout (FDR-012)

- Create a reusable **public layout** Vue component (e.g. `PublicLayout.vue` or `LandingLayout.vue`) that contains: (1) the same app bar as the landing (logo "GoViral", language selector en/es/pt); (2) the same footer as the landing (GoViral branding, tagline); (3) a default slot for main content.
- Refactor **Landing.vue** to use this layout; move hero and sections into the layout’s default slot.
- Refactor **Form/StartGrowth.vue** to use this layout; form and payment UI in the layout’s default slot.
- Refactor **Form/ThankYou.vue** to use this layout; thank-you message and CTA in the layout’s default slot.
- Add GTM snippet to the public layout (or root Blade/Inertia template): head script + noscript in body; use `GTM_ID` from env (optional); if not set, do not inject GTM.
- Ensure no visual or behavioural regressions; run existing browser and smoke tests (landing, form, thank-you, locale).
- Setup guides already exist in `docs/Setup/`: facebook-conversion-setup.md, google-conversion-setup.md, tiktok-conversion-setup.md, gtm-pixels-conversion-setup.md (no code change required for this bullet).

### Auth and dashboard Vuetify branding (FDR-013) — done

- [x] Create a Vuetify auth layout: `v-app` theme goviralDark, same background as thank-you/start-growth (radial gradients, #121212), centered card with GoViral logo and default slot; used by all auth pages. No Tailwind/reka-ui in this layout.
- [x] Refactor auth pages to use the new layout and Vuetify components: Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge. Use `v-text-field`, `v-btn`, `v-checkbox`, `v-alert`; keep Inertia Form and route helpers. Copy English only.
- [x] Refactor Dashboard.vue content to Vuetify: `v-container`, `v-row`, `v-col`, `v-card`; match GoViral branding (dark theme, card borders). Sidebar/wrapper as needed.
- [x] Ensure existing auth and dashboard Feature tests pass.
- [x] Add or update Browser tests: smoke checks for auth routes (e.g. `/login`, `/forgot-password`) and for dashboard (guest redirect to login, authenticated sees dashboard); at least one E2E flow (e.g. login → dashboard). Use stable selectors (e.g. `data-test` or Pest `@selector`) for login button and key fields.

### Core discount coupons (FDR-014) — done

- [x] **Before implementing any FDR-014 task:** Read `.cursor/skills/laravel-vue-crud/SKILL.md` and follow its backend + frontend workflow (and combine with `frontend-vue-vuetify` for UI rules).
- [x] Add migration for `discount_coupons`: id (UUID), code (string, indexed; unique among active via validation), value (integer 0–100), e
  - [x] **Browser tests for delete**: dialog opens on Delete click; Cancel closes without deleting; Confirm calls destroy and list updates.
  - [x] Add **sidebar/menu link** to discount coupons Index (e.g. "Discount coupons" in core nav).
  - [x] **Browser test for menu**: authenticated user can reach coupons Index via sidebar/menu link.
- [x] Checkout at `/start-growth`: add optional coupon code input; backend validates (exists, not soft-deleted, not expired, not exhausted) and applies discount; store coupon id on analysis_requests (via PI metadata). **After invalid coupon, re-fetch PI without coupon so card element stays available.** Webhook: increment coupon `times_used` when payment succeeded and coupon was used.
- [x] Scheduler: add job that soft-deletes invalid coupons (expired: `expires_at` is not null and `expires_at < now()`; exhausted: `max_uses` is not null and `times_used >= max_uses`). Schedule in Laravel Scheduler. Because it is soft delete, the row remains and analysis_requests never loses the reference.
- [x] Tests: Feature tests for CRUD (auth, validation, soft delete); checkout and webhook; scheduler soft-deletes invalid coupons and references remain valid; Browser E2E for admin flows and invalid-coupon payment regression; i18n for coupon copy on form (en/es/pt).

### Discount coupon expiration by date (FDR-015)

- [x] Adjust `discount_coupons` schema and model so `expires_at` is treated consistently as a **date-only** value (casts, queries, scheduler) and coupons remain valid through the expiration date.
- [x] Update core admin coupon Form Requests and controller helpers so creation and update of coupons use explicit date-based expiration (`after date X`) instead of relative “after X days” calculations.
- [x] Refactor core coupon Vue pages to replace the “after X days” UI with an “after date X” option that uses a date field, loading and persisting the exact `expires_at` date with no intermediate day-count logic.
- [ ] Update checkout and any supporting logic that validates coupons to respect the date-only semantics of `expires_at` (valid on or before the expiration date; invalid after), while preserving behavior for “never expires” and “after X uses”.
- [ ] Extend/update backend tests to cover the new date-based expiration behavior and to ensure no regressions for existing coupon types (never and usage-based).
- [ ] Extend/update Browser tests for the core coupon screens and checkout flow to cover creating, editing and using date-based coupons end to end.

### Scheduler cleanup (FDR-010) — closed

- **N/A.** FDR-010 is closed (see docs/FDRs/Closed/). ADR-020: retain reports for case studies; no scheduled cleanup. Do not implement analysis:cleanup or scheduler for deletion.

---

## Notes

- **FDRs fully done:** When all acceptance criteria of an FDR are met, move the FDR file from `docs/FDRs/ToDo/` to `docs/FDRs/Done/` (same filename) in a Building run.
- **Current codebase:** FDR-014 done. FDRs in ToDo: FDR-012 (conversion tracking + shared public layout). FDR-011, FDR-013 done.
- **Order:** Implement in the order above; within each section order by dependency.
- **FDR-014:** See `docs/FDRs/Done/FDR_014_core_discount_coupons.md`; plan steps above kept for reference.
- **FDR-010:** Closed; retention for case studies (ADR-020). No scheduler cleanup.
- **Discovery (Browser tests):** If browser tests fail locally, ensure Vite dev server is running or run `npm run build` before tests.

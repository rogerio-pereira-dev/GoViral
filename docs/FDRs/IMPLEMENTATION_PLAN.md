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

### LLM (FDR-007, 007.1, 007.2, 007.3) — done

- [x] FDR-007.1: Run spike: compare OpenAI, Gemini, Anthropic (cost, quality, latency); choose provider and approach (Laravel adapter vs external); create implementation ADR; ~~update ADR-014~~ use ADR-019; define interface in code (e.g. `generateReport(array $payload, string $locale): array`).
- [x] FDR-007.2: Implement adapter for chosen provider; env (GEMINI_API_KEY per ADR-019); Job calls interface; timeout and API errors propagate for Job retry.
- [x] FDR-007.3: Build prompt from docs/LLM Prompt Template.md (placeholders USERNAME, BIO, NICHE, VIDEO_1/2/3, NOTES, LANGUAGE); call LLM via adapter; parse response into report sections; return structured content (or HTML) to Job; handle malformed response (last_error, no email); sanitize markdown→HTML if needed.

### Email report (FDR-008)

- [x] Configure mail for AWS SES: MAIL_MAILER=ses, sender (e.g. report@goviral.you); document DKIM/SPF for production.
- [x] Create Mailable (e.g. GrowthReportMail): accepts report HTML and recipient email; body HTML; subject and plain text per branding/locale.
- [x] Job (FDR-005) will queue the email after building HTML (use queue name "emails"); Job (report) failure triggers job retry; after 12 failures record removed (FDR-005).

### Job orchestration (FDR-005)

- [x] Implement ProcessAnalysisRequest job fully: load record (payment_status=paid only); set processing_status=processing, attempt_count++; call LLM integration (FDR-007); build report HTML (sections per PRD); send email (FDR-008); on success: processing_status=sent, delete record; on failure: last_error, release with backoff; after 12 attempts: mark failed, delete record (ADR-011). Use queue name "analysis".
- Ensure job is only dispatched by webhook (FDR-004.3); only process paid records; handle already-deleted or non-paid gracefully.

### Captcha (FDR-009)

- Add Cloudflare Turnstile widget to form page; send token (e.g. turnstile_token) on submit.
- Backend: before creating AnalysisRequest and Stripe session, validate token with Turnstile siteverify API; on failure return 422 with clear message; keys via env (site key frontend, secret backend).

### Scheduler cleanup (FDR-010)

- Create artisan command (e.g. `analysis:cleanup`): delete analysis_requests where processing_status=sent, or created_at > 24h, or (processing_status=failed and attempt_count >= 12).
- Register in Laravel Scheduler (e.g. daily or every 6h); document cron `* * * * * php artisan schedule:run` for production.
- Optional: log count of deleted rows.

---

## Notes

- **FDRs fully done:** When all acceptance criteria of an FDR are met, move the FDR file from `docs/FDRs/ToDo/` to `docs/FDRs/Done/` (same filename) in a Building run.
- **Current codebase:** Welcome is the Laravel starter page (not GoViral landing). Frontend uses Tailwind + reka-ui (no Vuetify). No analysis_requests, Stripe, job, LLM, or mail report. No lang files for en/es/pt. Queue default is database — change to Redis per ADR-005.
- **Order:** Implement in the order above; within each section order by dependency (e.g. migration before model; Stripe config before webhook; LLM interface before adapter; adapter before Job full implementation).
- **Discovery (FDR-004.1):** The last Stripe setup task "Register webhook in Stripe" is an ops/dashboard step (not code); the webhook endpoint code is done in FDR-004.3. Consider marking it done or deferring to deployment docs.
- **Discovery (Browser tests):** 14 browser tests fail on `main` (pre-existing, unrelated to queue work). They need Vite dev server or a Chrome environment fix. Tracked here for visibility.

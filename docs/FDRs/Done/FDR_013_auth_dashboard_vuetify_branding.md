# FDR-013: Auth and dashboard refactor to Vue + Vuetify (GoViral branding)

**Feature:** 13 (auth and dashboard Vuetify branding)  
**Reference:** docs/04 - Features.md, ADR-022, docs/03 - Branding Manual.md

---

## How it works

- **Scope:** Refactor the default Laravel auth pages and the core dashboard to use **Vue + Vuetify** and the same visual identity as the public pages (`/`, `/start-growth`, `/thank-you`). No Tailwind or other UI libraries on these pages.
- **Auth pages** (`resources/js/pages/auth/`): Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge. They currently use `AuthLayout` (Tailwind-based). Replace with a **Vuetify auth layout**: `v-app` with theme `goviralDark`, same background treatment as thank-you/start-growth (e.g. radial gradients with primary/secondary), centered card with logo "GoViral" and slot for form content. All form controls use Vuetify components (e.g. `v-text-field`, `v-btn`, `v-checkbox`, `v-alert`). Inertia `Form` and existing route helpers (e.g. `store.form()`, `email.form()`) are kept; only the presentation layer changes.
- **Dashboard** (`resources/js/pages/Dashboard.vue`): Currently uses `AppLayout` (sidebar) and Tailwind/shadcn-style layout with `PlaceholderPattern`. Refactor the dashboard **content** to Vuetify (`v-container`, `v-row`, `v-col`, `v-card`) and the same branding (dark theme, borders, card style). Sidebar/layout wrapper may remain or be adapted so the overall experience matches the brand.
- **Copy:** All auth and dashboard pages are **English only**; no translation or locale switching for these pages.
- **Tests:** Add or update automated tests so that auth and dashboard flows are covered: Feature tests (existing Fortify/Inertia assertions) and **Browser tests** (e.g. login flow, dashboard load, smoke checks for auth routes in `tests/Browser/WebRoutesTest.php` or equivalent).

---

## How to test

- **Visual:** Visit `/login`, `/register`, `/forgot-password`, reset-password, verify-email, confirm-password, two-factor challenge, and `/core/dashboard`; confirm they use Vuetify, goviralDark theme, and match the branding (background #121212, primary/secondary colors, typography, CTAs with subtle glow where applicable). No Tailwind-only styling on these pages.
- **Functional:** Existing auth and dashboard behaviour unchanged: login, logout, password reset, email verification, two-factor challenge, password confirmation, and dashboard access for authenticated users. All existing Feature tests pass.
- **Browser:** Smoke checks for auth routes (e.g. `/login`, `/forgot-password`, `/core/dashboard` when guest vs authenticated) and at least one E2E flow (e.g. guest visits dashboard → redirect to login → log in → see dashboard). Selectors (e.g. `data-test` or Pest Browser `@selector`) remain stable for auth buttons and key form fields.

---

## Acceptance criteria

- [x] Auth layout is implemented with Vuetify (`v-app`, goviralDark theme, gradient background, centered card, GoViral logo). No Tailwind/reka-ui in auth layout.
- [x] All auth pages (Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge) use Vuetify components and the new auth layout; copy in English only.
- [x] Dashboard page uses Vuetify for content (e.g. `v-container`, `v-row`, `v-col`, `v-card`) and matches GoViral branding.
- [x] Existing auth and dashboard Feature tests pass (no regressions).
- [x] Browser tests cover auth and dashboard: smoke checks for relevant routes and at least one E2E flow (e.g. login → dashboard). Auth routes included in smoke suite where appropriate.

---

## Deployment notes

- Frontend only; no backend or env changes required. Build and deploy as usual (`npm run build`). Ensure Browser test suite runs in CI if applicable.

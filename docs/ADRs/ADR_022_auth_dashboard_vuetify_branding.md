# ADR-022: Auth and Core Dashboard Use Vuetify and GoViral Branding

## Status

Approved

## Context

The public funnel pages (landing, form, thank-you) use Vue + Vuetify and the GoViral theme (docs/03 - Branding Manual.md): dark background #121212, primary pink #FE2C55, secondary teal #25F4EE, Space Grotesk/Inter typography. The default Laravel auth pages (login, register, forgot/reset password, verify email, confirm password, two-factor challenge) and the core dashboard currently use a different stack (Tailwind / reka-ui components), resulting in a visual and technical inconsistency for authenticated and admin-facing flows.

## Decision

1. **Auth pages** (`resources/js/pages/auth/*`) and **Dashboard** (`resources/js/pages/Dashboard.vue`) are refactored to use **Vue + Vuetify** only, with the same **goviralDark** theme and visual identity as the public pages (`/`, `/start-growth`, `/thank-you`).
2. Auth and dashboard pages follow docs/03 - Branding Manual.md (colors, typography, high contrast, subtle glow on CTAs). No Tailwind or other UI libraries for these pages.
3. Auth and dashboard copy remains **English only**; no internationalisation (i18n) is required for these pages.
4. Automated tests (Feature and Browser) must cover the refactored auth and dashboard flows.

## Consequences

- **Positive:** Single visual and technical identity across public and authenticated areas; easier maintenance; consistent branding.
- **Negative:** One-time refactor effort; auth/dashboard must use Vuetify components (e.g. v-text-field, v-btn, v-card) instead of existing Tailwind-based components for those pages.
- **Neutral:** Existing Fortify routes and backend behaviour remain unchanged; only frontend implementation changes.

## References

- docs/03 - Branding Manual.md
- docs/04 - Features.md (Feature 13)
- ADR-003 (Frontend Stack)
- docs/FDRs/ToDo/FDR_013_auth_dashboard_vuetify_branding.md

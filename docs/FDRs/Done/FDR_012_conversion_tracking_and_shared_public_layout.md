# FDR-012: Conversion tracking for ads and shared public layout

**Feature:** 12 (conversion tracking, shared layout)  
**Reference:** docs/04 - Features.md, ADR-021

---

## How it works

- **Conversion model:** A **lead** is a visit to `/`. A **conversion** starts when the user visits `/start-growth` and **completes** when they reach `/thank-you` (after payment). These three moments are used to link ads on Google, Meta (Facebook/Instagram), and TikTok (ADR-021).

- **Shared public layout:** All public-facing pages that participate in the funnel use the **same header and footer** as the landing page (`/`). Specifically: Landing (`/`), Form (`/start-growth`), and Thank You (`/thank-you`) must render inside a common layout that includes:
  - **Header:** Same app bar as the landing (logo "GoViral", language selector en/es/pt).
  - **Footer:** Same footer as the landing (GoViral branding, tagline).
  - **Content area:** The main content (hero sections on landing, form on start-growth, thank-you card on thank-you) is the only part that changes per page.

- **Implementation approach:** Extract the header (v-app-bar) and footer (v-footer) from the current Landing page into a reusable **public layout** component (e.g. `PublicLayout.vue` or `LandingLayout.vue`). The Landing page uses this layout and fills the main slot with its sections. The Form page (`StartGrowth.vue`) and Thank You page (`ThankYou.vue`) use the same layout and fill the main slot with their content. The GTM container script is included once in this layout (e.g. in the root Blade/Inertia layout or in the public layout component) so every page receives the same tracking base.

- **Tracking:** Conversion and pixel logic are **not** implemented in application code. The app only ensures (1) stable URLs `/`, `/start-growth`, `/thank-you`, and (2) a single shared layout so the GTM snippet loads on all three. Setup of Facebook, Google, and TikTok conversion events and pixels is done in GTM and in each platform’s interface; step-by-step guides are in `docs/Setup/` (see Deployment notes).

---

## How to test

- **Shared layout:** Visit `/`, `/start-growth`, and `/thank-you`; verify that the header (logo + language selector) and footer (GoViral + tagline) are identical on all three. Changing language on one page persists and is reflected on the others when navigating.
- **Content correctness:** Landing shows hero and sections; start-growth shows the form and payment; thank-you shows the success message and CTA. No duplicate headers/footers or broken styles.
- **GTM readiness:** The same layout (and thus the same GTM container, when configured) is present on all three URLs so that tags can be triggered by URL or dataLayer in GTM. Manual test: add GTM container ID to env/layout; confirm one script load per page and correct URL in browser.

---

## Acceptance criteria

- [ ] A reusable **public layout** component exists that contains the landing header (app bar with logo and language selector) and landing footer (GoViral + tagline).
- [ ] **Landing** (`/`) uses this layout; its main content (hero, sections) is in the layout’s default slot.
- [ ] **Form** (`/start-growth`) uses this layout; form and payment UI are in the layout’s default slot.
- [ ] **Thank You** (`/thank-you`) uses this layout; thank-you message and CTA are in the layout’s default slot.
- [ ] Visual and behaviour match current branding; no regressions on mobile/desktop.
- [ ] Documentation: four Setup guides exist in `docs/Setup/` — Facebook conversion setup, Google conversion setup, TikTok conversion setup, GTM pixels/conversion setup — describing how to implement and configure conversions in GTM and each platform.

---

## Deployment notes

- **GTM:** Add the GTM container script to the public layout (or root template) via env (e.g. `GTM_ID`). If not set, the script is not injected. See `docs/Setup/gtm-pixels-conversion-setup.md`.
- **Setup guides:** Implementers and marketers use:
  - `docs/Setup/facebook-conversion-setup.md` — Meta Pixel / Conversions API and events for Lead, StartCheckout, Purchase.
  - `docs/Setup/google-conversion-setup.md` — Google Ads / GA4 conversion events for the same funnel.
  - `docs/Setup/tiktok-conversion-setup.md` — TikTok Pixel and events for the same funnel.
  - `docs/Setup/gtm-pixels-conversion-setup.md` — How to configure GTM (triggers, tags, variables) for these pixels and conversions.

# ADR-021: Conversion Tracking for Ads (Google, Meta, TikTok) via GTM

## Status

Approved

## Context

GoViral needs to attribute conversions to paid campaigns on Google, Meta (Facebook/Instagram), and TikTok. A lead is defined as a visit to the landing page (`/`). A conversion starts when the user enters the funnel at `/start-growth` and completes when they reach `/thank-you` after payment. The product owner wants to link ads to these events so each platform can optimize and report on conversions.

Implementing pixels and conversion APIs directly in the frontend for each platform would duplicate logic and make maintenance and consent management harder. A single tag management layer is preferable.

## Decision

1. **Use Google Tag Manager (GTM)** as the single container for all conversion and pixel tags. The application does not embed platform-specific scripts directly; it exposes a minimal, stable data layer (or equivalent) and/or page/event identifiers that GTM uses to fire the correct tags.

2. **Define three conversion-related moments:**
   - **Lead (page view):** User lands on `/`. Fired as a "Lead" or "PageView" event for each platform as required by their conversion setup.
   - **Conversion start:** User lands on `/start-growth` (start of funnel). Fired as "StartCheckout" or equivalent "begin checkout" / "add payment info" event per platform.
   - **Conversion complete:** User lands on `/thank-you` after successful payment. Fired as "Purchase" or "CompleteRegistration" / "Conversion" per platform.

3. **Application responsibilities:**
   - Provide a **shared public layout** (header and footer identical to the landing page) for `/`, `/start-growth`, and `/thank-you` so that the GTM container script is loaded once and consistently across these pages. Layout structure is documented in FDR-012.
   - Ensure **stable, predictable URLs** for the three moments (`/`, `/start-growth`, `/thank-you`) so GTM rules (e.g. "Fire tag when URL equals X") can be configured without code changes.
   - Optionally push a minimal **dataLayer** (e.g. `page`, `event`) on these pages so GTM can trigger tags based on event name and page path; implementation detail is left to the FDR and setup guides.

4. **Platform-specific setup** (pixels, conversion APIs, event names, parameters) is **not** hardcoded in the application. It is documented in `docs/Setup/` so that marketing or ops can configure GTM and each platform’s interface (Meta Events Manager, Google Ads, TikTok Events Manager) using the same funnel definition.

## Consequences

- **Positive:** One place (GTM) to add, remove, or change pixels and conversion tags; easier A/B tests and consent; consistent funnel definition across Google, Meta, and TikTok; setup guides allow non-developers to complete configuration.
- **Negative:** Dependency on GTM and correct configuration; if GTM is blocked or misconfigured, conversions may not be tracked; shared layout requires refactoring existing Form and Thank You pages to use the same header/footer as the landing.
- **Neutral:** Server-side tagging or server-side conversion APIs can be added later without changing this decision (GTM can still be the trigger source).

## References

- docs/04 - Features.md (Feature 12)
- docs/FDRs/ToDo/FDR_012_conversion_tracking_and_shared_public_layout.md
- docs/Setup/facebook-conversion-setup.md
- docs/Setup/google-conversion-setup.md
- docs/Setup/tiktok-conversion-setup.md
- docs/Setup/gtm-pixels-conversion-setup.md

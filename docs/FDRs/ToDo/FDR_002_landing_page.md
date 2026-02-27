# FDR-002: Landing Page

**Feature:** 2  
**Reference:** docs/03 - Branding Manual.md, docs/04 - Features.md

---

## How it works

- An entry page (`/`) displays the product positioning and a CTA to the form.
- **Content:** tagline "Engineered for Viral Growth"; subheadline "Turn insight into viral momentum in minutes"; supporting copy (TikTok profile analysis, recommendations, 30-day plan); tone sharp, fast, smart. Landing copy must use **Laravel translations** (localization) to support en, es, pt.
- **Locale at top of page:** the language selector is at the top of the page (e.g. header). When the user chooses en, es, or pt, the application locale is set (e.g. `App::setLocale($locale)` in the backend and persisted in session or query; frontend uses the same value). The page re-renders or reloads with text in the chosen language. The locale set here is the session/page locale and will be used in the form and report — there is no locale field inside the form.
- **Locale configuration:** use [Laravel Localization](https://laravel.com/docs/12.x/localization): files in `lang/en`, `lang/es`, `lang/pt` (or `lang/*.json`); helper `__('key')` or `@lang` in views; locale configurable via `config/app.php` and `App::setLocale()` per request. Routes or middleware can set the locale from the user's top-of-page choice.
- **Primary CTA:** leads to the form. URL aligned with the Branding Manual: route `/start-growth` (reflects the CTA "Start My Growth"). CTA text per manual (e.g. "Start My Growth").
- **Visual:** follows FDR-001 (Vue + Vuetify + Branding). No collection form on the landing; presentation + top language selector + CTA only.

---

## How to test

- **Happy path:** Visit `/`; see tagline and subheadline; language selector at top; when changing language, page text updates (Laravel translations); click CTA and go to `/start-growth` (form) with locale already set.
- **Edge cases:** (1) Default locale when user does not choose (e.g. `APP_LOCALE` or fallback en). (2) Locale persistence when navigating to form and thank-you page. (3) All landing strings translated in en, es, pt. (4) CTA and routes working; no layout break.
- **Responsiveness:** layout usable on mobile and desktop.

---

## Acceptance criteria

- [ ] Landing displays tagline and subheadline from the manual; copy via Laravel localization (lang files or JSON).
- [ ] Language selector (en, es, pt) at the **top** of the page; on selection, application locale is set and text updated.
- [ ] Primary CTA visible; leads to the form via route `/start-growth`; CTA text per manual (translated).
- [ ] Visual aligned with Branding (FDR-001).
- [ ] No console errors or layout break in common viewports.

---

## Deployment notes

- Publish language files if needed (`php artisan lang:publish`). Ensure `lang/en`, `lang/es`, `lang/pt` (or equivalents) exist with the keys used on the landing.

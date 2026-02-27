# ADR-003: Frontend Stack

## Status

Approved

## Context

The MVP requires a landing page and a data collection form (email, TikTok username, bio, niche, video links, notes), with language selection and Stripe Checkout integration. Consistency with the branding manual (dark mode, Vuetify mentioned in HLD) and good UX without a heavy SPA are required.

## Decision

Use **Laravel + Inertia.js + Vue.js + Vuetify** on the frontend.

References:
- [Laravel Frontend (Inertia)](https://laravel.com/docs/12.x/frontend)
- [Vue.js](https://vuejs.org/guide/introduction.html)
- [Vuetify](https://vuetifyjs.com)

## Consequences

- **Positive:** Single Laravel application, server-side rendering with Vue interactivity, Vuetify components for consistent UI aligned with the design system; less complexity than a pure SPA.
- **Negative:** Dependency on Inertia and Vue on the frontend; Vuetify themes may require customization for branding (colors, typography).
- **Neutral:** Build via Vite/Laravel tooling; team must master Vue and Vuetify.

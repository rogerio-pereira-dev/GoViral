# FDR-001: Configure Vue + Vuetify per Branding Manual

**Feature:** 1  
**Reference:** docs/03 - Branding Manual.md, docs/04 - Features.md

---

## How it works

- Vue (Inertia) application uses Vuetify with a custom theme.
- **Theme:** dark mode; base background `#121212`; primary colors Pink `#FE2C55`, Teal `#25F4EE`; accents with neon/glow effect on CTAs and highlights.
- **Typography:** Space Grotesk for headlines, Inter for body; clear hierarchy and high contrast.
- **UI:** smooth hover transitions, subtle glow on interactive elements, micro-interactions; clean layout, no visual clutter.
- **Microcopy/CTAs** use copy from `docs/03 - Branding Manual.md` (e.g. "Start My Growth", "Generate My Growth Blueprint", "Analyzing Your Growth Potential...").
- **Favicon/logo:** Teal→Pink gradient, clean geometry; viral/lightning concept when applicable; scalable for favicon and icon.

---

## How to test

- **Happy path:** Load landing and form; verify background is #121212, CTAs use pink/teal and have glow; headlines in Space Grotesk, body in Inter; CTA text matches the manual.
- **Edge cases:** (1) Custom Vuetify components (inputs, buttons): ensure they inherit colors and do not break contrast. (2) Favicon at multiple sizes (tab, bookmark): correct display.
- **Accessibility:** text/background contrast within limits (WCAG AA where applicable).

---

## Acceptance criteria

- [ ] Vuetify dark theme with #121212, #FE2C55, #25F4EE applied globally where defined in the manual.
- [ ] Space Grotesk and Inter loaded and used per manual (headlines vs body).
- [ ] CTAs and microcopy from the manual present where feature 1 applies (e.g. primary button "Start My Growth").
- [ ] Subtle glow/neon on CTAs and interactive elements; smooth transitions.
- [ ] Favicon/logo with Teal→Pink gradient, readable at small sizes.
- [ ] No styles that explicitly violate the manual (e.g. light background at root).

---

## Deployment notes

- Fonts (Space Grotesk, Inter): ensure they are loaded (e.g. Google Fonts) in all environments.
- Theme variables (colors) can live in a single Vuetify theme file for easier maintenance.

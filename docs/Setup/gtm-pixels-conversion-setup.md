# GTM: Pixels and Conversion Configuration

This guide explains how to configure **Google Tag Manager (GTM)** so that conversion and pixel tags (Facebook, Google, TikTok) fire on the correct GoViral pages. The app provides stable URLs and a shared layout so the GTM container loads on all funnel pages.

**Conversion model (ADR-021, FDR-012):**

| Moment              | URL             | Purpose                    |
|---------------------|-----------------|----------------------------|
| **Lead**            | `/`             | User landed on site        |
| **Conversion start**| `/start-growth` | User started checkout      |
| **Conversion complete** | `/thank-you` | User completed payment     |

---

## 1. Create a GTM container

1. Go to [Google Tag Manager](https://tagmanager.google.com).
2. Create an account (if needed) and a **container** for your GoViral website (type **Web**).
3. Note the **Container ID** (format `GTM-XXXXXXX`). You will add this to the app (e.g. via env `GTM_ID`).

---

## 2. Install the GTM snippet in the app

The GoViral app must include the GTM snippet on every public funnel page (`/`, `/start-growth`, `/thank-you`). This is done in the **shared public layout** (see FDR-012).

**Head (as early as possible in `<head>`):**

```html
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
<!-- End Google Tag Manager -->
```

**Body (immediately after opening `<body>`):**

```html
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
```

Replace `GTM-XXXXXXX` with your Container ID. Prefer loading the container ID from environment (e.g. `GTM_ID` in `.env`) so the same code works in staging and production with different IDs.

---

## 3. GTM variables (recommended)

Create these in GTM for reuse in triggers:

| Variable name     | Type              | Configuration                    |
|-------------------|-------------------|----------------------------------|
| **Page Path**     | Built-in or **Page Path** | — (default)                |
| **Page URL**      | Built-in          | —                                |

You will use **Page Path** in triggers to fire tags only on specific URLs.

---

## 4. GTM triggers

Create **Page View** triggers that fire only when the URL matches:

| Trigger name              | Type       | Condition                    |
|---------------------------|------------|------------------------------|
| **PV – Landing (Lead)**   | Page View  | Page Path **equals** `/`     |
| **PV – Start growth**     | Page View  | Page Path **equals** `/start-growth` |
| **PV – Thank you**        | Page View  | Page Path **equals** `/thank-you`   |

Use **Window Load** or **DOM Ready** as needed so the page URL is available. **Page View** in GTM usually runs on the initial page load and is sufficient for single-page navigation if the URL updates (e.g. Inertia); if the app is SPA and URL changes without full reload, add **History Change** triggers or push to `dataLayer` on route change and use **Custom Event** triggers.

---

## 5. Tags – overview

Create tags for each platform and attach the triggers above:

| Tag purpose              | Fires on trigger   | Platform |
|--------------------------|--------------------|----------|
| Meta Pixel – Init + Lead | PV – Landing       | Facebook |
| Meta Pixel – InitiateCheckout | PV – Start growth | Facebook |
| Meta Pixel – Purchase    | PV – Thank you     | Facebook |
| GA4 – Config             | All Pages          | Google   |
| GA4 – begin_checkout     | PV – Start growth  | Google   |
| GA4 – purchase           | PV – Thank you     | Google   |
| TikTok – Init            | All Pages          | TikTok   |
| TikTok – InitiateCheckout | PV – Start growth | TikTok   |
| TikTok – CompletePayment | PV – Thank you     | TikTok   |

Details for each platform are in:

- [facebook-conversion-setup.md](facebook-conversion-setup.md)
- [google-conversion-setup.md](google-conversion-setup.md)
- [tiktok-conversion-setup.md](tiktok-conversion-setup.md)

---

## 6. Optional: Data Layer for events

If you prefer to drive tags from the app instead of URL-only:

1. In the app, on each page load (or route change), push to `dataLayer`, for example:
   - On `/`: `dataLayer.push({ event: 'conversion_lead' });`
   - On `/start-growth`: `dataLayer.push({ event: 'conversion_start' });`
   - On `/thank-you`: `dataLayer.push({ event: 'conversion_complete', value: 20, currency: 'USD' });`

2. In GTM, create **Custom Event** triggers for `conversion_lead`, `conversion_start`, `conversion_complete` and use them instead of (or in addition to) **Page Path** triggers.

3. Create **Data Layer Variables** for `value` and `currency` if you pass them, and use them in conversion tags (e.g. purchase value).

---

## 7. Test and publish

1. In GTM, use **Preview** and connect to your site. Visit `/`, `/start-growth`, and `/thank-you` and confirm the correct tags fire in the GTM debug panel.
2. Fix any trigger or variable issues, then **Submit** and **Publish** the container.
3. Verify in each platform’s tools (Meta Events Manager, GA4 DebugView, TikTok Events Manager) that events are received.

---

## References

- [GTM quick start](https://support.google.com/tagmanager/answer/6103696)
- [Triggers](https://support.google.com/tagmanager/answer/6103696)
- ADR-021, FDR-012, docs/04 - Features.md (Feature 12)

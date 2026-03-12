# Google Ads / GA4 Conversion Setup

This guide explains how to implement Google conversion tracking for GoViral using **Google Tag (gtag.js)** or **GA4** events, with triggers managed in **Google Tag Manager (GTM)**.

**Prerequisites:** GTM container is installed and loads on `/`, `/start-growth`, and `/thank-you`. See [gtm-pixels-conversion-setup.md](gtm-pixels-conversion-setup.md).

---

## 1. Get your Google IDs

- **Google Ads:** In [Google Ads](https://ads.google.com) → **Tools & settings** → **Conversions**, note your **Conversion ID** and **Conversion Label** (for legacy gtag conversion), or use **GA4** as the conversion source (recommended).
- **GA4:** In [Google Analytics](https://analytics.google.com) → **Admin** → **Data streams** → select your web stream → note **Measurement ID** (format `G-XXXXXXXXXX`). You will use this in GTM as the GA4 Configuration tag.

---

## 2. Define the three conversion moments

| Moment                 | URL             | Recommended event / conversion      |
|------------------------|-----------------|--------------------------------------|
| **Lead**               | `/`             | `page_view` (GA4) or Lead conversion |
| **Conversion start**   | `/start-growth` | `begin_checkout` (GA4)               |
| **Conversion complete** | `/thank-you`  | `purchase` (GA4) or conversion action |

For **Google Ads**, you can either:
- Use **GA4** as the linked conversion source and import GA4 events (e.g. `purchase`, `begin_checkout`) as conversions in Google Ads, or
- Use a **Google Ads Conversion** tag (gtag) with Conversion ID + Label, fired on `/thank-you` for the “Purchase” conversion.

---

## 3. GA4 Configuration tag in GTM

1. In GTM: **Tags** → **New** → Tag type **Google Analytics: GA4 Configuration**.
2. **Measurement ID:** your GA4 Measurement ID (`G-XXXXXXXXXX`).
3. **Trigger:** **All Pages** (or **Page View** – Window Load) so GA4 loads on every page.
4. Save and name e.g. **GA4 – Configuration**.

---

## 4. GA4 Event tags in GTM

Create one GA4 Event tag per moment (or use one tag with a variable event name).

**Option A – One tag per event:**

- **GA4 – Event – Lead (page_view)**  
  - Event name: `page_view` (or use the default page_view from the config tag; if you want a dedicated “Lead” event, create **GA4 – Event – Lead** with event name `generate_lead`).  
  - Trigger: **Page View** where **Page Path** equals `/`.

- **GA4 – Event – Begin Checkout**  
  - Event name: `begin_checkout`.  
  - Trigger: **Page View** where **Page Path** equals `/start-growth`.

- **GA4 – Event – Purchase**  
  - Event name: `purchase`.  
  - Parameters (optional): `value`, `currency` (e.g. from Data Layer).  
  - Trigger: **Page View** where **Page Path** equals `/thank-you`.

**Option B – Data Layer–driven:**

- Push from the app: `dataLayer.push({ event: 'ga4_event', eventName: 'begin_checkout', ... });`
- In GTM: one **GA4 Event** tag; **Event name** = Data Layer Variable; trigger = **Custom Event** `ga4_event`.

---

## 5. Google Ads Conversion tag (optional, if not using GA4 import)

If you use a **Google Ads Conversion** tag instead of GA4:

1. **Tags** → **New** → **Google Ads Conversion Tracking**.
2. **Conversion ID** and **Conversion Label** from Google Ads.
3. **Conversion value** and **Currency** (optional; can be 0 and USD for count-only).
4. **Trigger:** **Page View** where **Page Path** equals `/thank-you`.
5. Save.

---

## 6. Mark conversions in Google Ads

1. **Google Ads** → **Goals** → **Conversions**.
2. If using **GA4:** **New conversion action** → **Import** → **Google Analytics 4 properties** → select the GA4 property → choose events (e.g. `purchase`, `begin_checkout`) and import.
3. If using **Google Ads tag:** create conversion action with **Use Google Tag Manager** and the same Conversion ID/Label; then the GTM tag will fire that conversion.

---

## 7. Test

1. **GA4:** **Admin** → **DebugView** (or use the browser extension **Google Analytics Debugger**). Visit `/`, `/start-growth`, `/thank-you` and confirm events.
2. **Google Ads:** **Tools** → **Conversions** → check status and use **Tag Assistant** or a test conversion to verify the thank-you page fires the conversion.

---

## References

- [GA4 events](https://support.google.com/analytics/answer/9267738)
- [Google Ads conversion setup](https://support.google.com/google-ads/answer/1722022)
- [GTM setup for this site](gtm-pixels-conversion-setup.md)

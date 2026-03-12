# Facebook (Meta) Conversion Setup

This guide explains how to implement and configure Facebook/Meta conversion tracking for GoViral using the **Meta Pixel** and optional **Conversions API**, with triggers managed in **Google Tag Manager (GTM)**.

**Prerequisites:** GTM container is installed on the site and loads on `/`, `/start-growth`, and `/thank-you`. See [gtm-pixels-conversion-setup.md](gtm-pixels-conversion-setup.md).

---

## 1. Get your Meta Pixel ID

1. Go to [Meta Events Manager](https://business.facebook.com/events_manager2).
2. Select your **Data source** (or create one: **Meta Pixel**).
3. Copy the **Pixel ID** (numeric). You will use it in GTM as the tag configuration.

---

## 2. Define the three events in Meta

GoViral uses three moments that map to Meta events:

| Moment            | URL            | Recommended Meta event   | Use case                    |
|-------------------|----------------|--------------------------|-----------------------------|
| **Lead**          | `/`            | `Lead` or `PageView`     | Top-of-funnel, ad attribution |
| **Conversion start** | `/start-growth` | `InitiateCheckout`   | User started checkout       |
| **Conversion complete** | `/thank-you` | `Purchase`           | Payment completed           |

In Events Manager you can create **Custom conversions** or use these standard events. For **Conversions API** you will send the same event names from the server if you implement it later.

---

## 3. Create the Meta Pixel tag in GTM

1. In GTM: **Tags** → **New** → Tag type **Meta Pixel** (or **Custom HTML** if the Meta Pixel template is not available).
2. **Tag configuration:**
   - **Pixel ID:** your Meta Pixel ID.
   - **Event name:** leave empty here; use **Triggers** to fire different events (see step 4).
3. If using **Custom HTML**, use the standard `fbq()` call, for example:
   - Lead: `fbq('track', 'Lead');` or `fbq('track', 'PageView');`
   - Start: `fbq('track', 'InitiateCheckout');`
   - Purchase: `fbq('track', 'Purchase', { value: 0, currency: 'USD' });`  
   (You can pass real value/currency from the data layer if available.)

---

## 4. Create one tag per event (or one tag with variable event name)

**Option A – One tag per event (recommended for clarity):**

- **Tag 1 – Meta Pixel – Lead**
  - Trigger: **Page View** where **Page Path** equals `/` (or **Page Path** contains `/` and referrer is not internal, if you want only first landing).
- **Tag 2 – Meta Pixel – InitiateCheckout**
  - Trigger: **Page View** where **Page Path** equals `/start-growth`.
- **Tag 3 – Meta Pixel – Purchase**
  - Trigger: **Page View** where **Page Path** equals `/thank-you`.

**Option B – Single tag with event from Data Layer:**

- In the app, push to `dataLayer`: `{ event: 'meta_event', eventName: 'Lead' }` (or `InitiateCheckout`, `Purchase`) on the corresponding page.
- In GTM: one Meta Pixel tag; **Event name** = **Data Layer Variable** `eventName`. Trigger: **Custom Event** `meta_event`.

---

## 5. Load the Meta Pixel base code

The base pixel code (e.g. `fbq('init', 'PIXEL_ID'); fbq('track', 'PageView');`) must run once on the site. In GTM:

1. Create a tag **Meta Pixel – Init** (or use the template “Meta Pixel” with “Page View” as the default event).
2. Set **Trigger** to **All Pages** (or **Page View** – Window Load).
3. Set **Tag firing order** so this tag fires before the event-specific tags (e.g. **Tag sequencing** or fire on same trigger with lower priority).

Ensure the Meta Pixel script is loaded before any `fbq('track', ...)` calls. If you use the Meta Pixel tag type in GTM, the init is usually included.

---

## 6. Test

1. Install [Meta Pixel Helper](https://chrome.google.com/webstore/detail/meta-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc) (Chrome).
2. Visit `https://your-domain.com/` → expect **Lead** or **PageView**.
3. Visit `https://your-domain.com/start-growth` → expect **InitiateCheckout**.
4. Complete payment and land on `https://your-domain.com/thank-you` → expect **Purchase**.
5. In Events Manager, check **Test Events** (or live events after 24–48 h).

---

## 7. Link to Ads and Conversions API (optional)

- In **Meta Business Suite** / **Ads Manager**, create or select a campaign and choose **Conversions** as the objective; select the **Pixel** as the conversion source and choose the event (e.g. **Purchase**) for optimization.
- For more accurate attribution and to reduce impact of browser blockers, consider implementing **Conversions API** (server-side): send the same events from your backend to Meta. Documentation: [Meta Conversions API](https://developers.facebook.com/docs/marketing-api/conversions-api).

---

## References

- [Meta Pixel documentation](https://developers.facebook.com/docs/meta-pixel)
- [Standard events](https://developers.facebook.com/docs/meta-pixel/reference)
- [GTM setup for this site](gtm-pixels-conversion-setup.md)

# TikTok Conversion Setup

This guide explains how to implement TikTok conversion tracking for GoViral using the **TikTok Pixel**, with triggers managed in **Google Tag Manager (GTM)**.

**Prerequisites:** GTM container is installed and loads on `/`, `/start-growth`, and `/thank-you`. See [gtm-pixels-conversion-setup.md](gtm-pixels-conversion-setup.md).

---

## 1. Get your TikTok Pixel ID

1. Go to [TikTok Events Manager](https://ads.tiktok.com/marketing_portal/events).
2. Create or select a **Web** pixel.
3. Copy the **Pixel ID** (long alphanumeric string). You will use it in GTM.

---

## 2. Define the three events in TikTok

GoViral uses three moments that map to TikTok events:

| Moment                 | URL             | Recommended TikTok event   |
|------------------------|-----------------|-----------------------------|
| **Lead**               | `/`             | `ViewContent` or `Contact` |
| **Conversion start**   | `/start-growth` | `InitiateCheckout`          |
| **Conversion complete**| `/thank-you`    | `CompletePayment`           |

In TikTok Events Manager you can create **Custom events** or use these standard events. Standard events are preferred for optimization.

---

## 3. Load the TikTok Pixel base code in GTM

The TikTok pixel must be initialized once with your Pixel ID before sending events.

1. In GTM: **Tags** → **New** → Tag type **Custom HTML**.
2. **HTML** (replace `YOUR_PIXEL_ID` with your Pixel ID):

```html
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
  ttq.load('YOUR_PIXEL_ID');
  ttq.page();
}(window, document, 'ttq');
</script>
```

3. **Trigger:** **All Pages** (or **Page View** – Window Load).
4. Save and name e.g. **TikTok Pixel – Init**.

---

## 4. Create event tags in GTM

Create one **Custom HTML** tag per event (or use **TikTok Pixel** tag type if available in GTM).

**Lead (ViewContent) – Page path = `/`**

- Trigger: **Page View** where **Page Path** equals `/`.
- If the init tag already sends `ttq.page()` on all pages, the Lead event may be covered. Otherwise add:

```html
<script>
  ttq.track('ViewContent');
</script>
```

**InitiateCheckout – Page path = `/start-growth`**

- Trigger: **Page View** where **Page Path** equals `/start-growth`.

```html
<script>
  ttq.track('InitiateCheckout');
</script>
```

**CompletePayment – Page path = `/thank-you`**

- Trigger: **Page View** where **Page Path** equals `/thank-you`.

```html
<script>
  ttq.track('CompletePayment', { value: 0, currency: 'USD' });
</script>
```

You can pass `value` and `currency` from the Data Layer if available.

---

## 5. Ensure load order

The **TikTok Pixel – Init** tag must fire before any `ttq.track(...)` tags. In GTM:

- Use the same trigger (e.g. **Page View**) for Init and event tags, and set **Tag sequencing** so Init fires first, or
- Fire Init on **All Pages** and event tags on **Page View** with **Page Path** conditions; ensure the pixel script is loaded before event tags run (e.g. use **Window Load** for Init and **DOM Ready** or **Window Load** for events, with Init first).

---

## 6. Test

1. Install [TikTok Pixel Helper](https://chrome.google.com/webstore/detail/tiktok-pixel-helper/) (Chrome) if available, or use browser dev tools (Network tab: filter by `analytics.tiktok.com`).
2. Visit `https://your-domain.com/` → expect pixel load and Lead/ViewContent.
3. Visit `https://your-domain.com/start-growth` → expect `InitiateCheckout`.
4. Complete payment and land on `https://your-domain.com/thank-you` → expect `CompletePayment`.
5. In TikTok Events Manager, use **Test events** or wait for events to appear in the dashboard.

---

## 7. Use in TikTok Ads

In **TikTok Ads Manager**, create or edit a campaign and set the **Optimization event** to the conversion you want (e.g. **CompletePayment**). Select the same pixel as the conversion source.

---

## References

- [TikTok Pixel documentation](https://business.tiktok.com/help/article?aid=10028)
- [TikTok standard events](https://business.tiktok.com/help/article?aid=10021)
- [GTM setup for this site](gtm-pixels-conversion-setup.md)

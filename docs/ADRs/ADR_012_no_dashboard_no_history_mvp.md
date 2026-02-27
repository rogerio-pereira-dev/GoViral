# ADR-012: No Dashboard and No History in the MVP

## Status

Approved

## Context

GoViral is positioned as an impulse-purchase tool, with delivery by email and no need for a “logged-in area”. Including a dashboard or history would increase scope, persistence, and authentication complexity without being essential for the MVP.

## Decision

In the **MVP**, do not implement:

- **Dashboard** (admin or user)
- **Analysis or order history** for the user
- **Report retention** for later access (per ADR-010 and ADR-011)

The flow is: landing → form → payment → delivery by email; the user does not access the system after payment to view past reports.

## Consequences

- **Positive:** Smaller scope, less code, less persisted data, and smaller security surface; focus on conversion and delivery.
- **Negative:** User has no “second copy” of the report in the product; support and metrics depend on logs and external tools.
- **Neutral:** Post-MVP extensions (e.g. reanalysis, upsell) may require minimal retention or a light dashboard; will be addressed in future ADRs.

# ADR-008: TikTok Profile Data Collection Strategy

## Status

Approved

## Context

To generate the analysis, the system needs TikTok profile data (username, bio, niche, video links, etc.). Alternatives include the official TikTok API, scraping, or third-party services, which bring legal, maintenance, and cost risks.

## Decision

In the MVP, use **manual input only** by the user in the form. Do not integrate the TikTok API, do not perform scraping, and do not use third-party data collection services.

Data collected: email, TikTok username, current bio, desired niche, links to the last 3 videos, notes (optional), preferred language.

## Consequences

- **Positive:** Reduced legal risk and dependence on unstable APIs; lower infrastructure complexity and cost; MVP viable without API approvals.
- **Negative:** Data may be outdated or incorrect; analysis quality depends on user honesty and accuracy.
- **Neutral:** Post-MVP evolution with API or additional sources is possible via a new assessment (e.g. new ADR).

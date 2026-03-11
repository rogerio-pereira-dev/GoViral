# ADR-020: Data Retention — Retain for Case Studies

## Status

Approved

## Context

Analyses sent by email have value for internal use as case studies. The previous policy (ADR-011) assumed deletion of records after send and a scheduled cleanup job (FDR-010). That approach has been closed: FDR-010 is in `docs/FDRs/Closed/`. Retaining the content of sent reports in the database is required so analyses can be used as case studies.

## Decision

- **Persist the report content (HTML)** in the database before sending the email to the user. The same HTML that is sent by email will be stored (e.g. column `report_html` on `analysis_requests` or a dedicated table), linked to the analysis record.
- **Do not delete** the `analysis_requests` record after successful send; the record remains with the stored report for internal use and case studies.
- **Order of operations:** (1) Generate report HTML; (2) Save HTML to the database; (3) Send the email. Thus, even if sending fails, the content is already persisted for retry or audit.
- **No scheduled cleanup** for sent or aged records: FDR-010 (scheduler for data cleanup) is closed; retention for case studies takes precedence.

References: FDR-011 (persist report before email), ADR-011 (superseded by this ADR).

## Consequences

- **Positive:** Sent report content is available for case studies; ability to audit and improve the product using real analyses.
- **Negative:** Higher storage usage; sensitive data (report content and user data) remains in the database; access control and privacy (GDPR/LGPD) must be considered for internal use.
- **Neutral:** The user still has no in-product history (ADR-012); persistence is for internal use only.

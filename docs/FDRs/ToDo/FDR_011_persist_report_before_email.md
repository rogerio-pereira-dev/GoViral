# FDR-011: Persist report in database before sending email

**Feature:** 11 (persistence for case studies)  
**Reference:** docs/04 - Features.md, ADR-020

---

## How it works

- Before sending the email with the report to the user, the content (report HTML) is saved to the database. The order in the Job (ProcessAnalysisRequest) is: (1) generate report HTML (via GrowthReportService); (2) persist the HTML to the database (new column or table); (3) queue/send the email.
- A **migration** is required to add storage for the report content (e.g. column `report_html` on `analysis_requests`, type `longText` or equivalent, nullable; optionally `sent_at` for send timestamp). Alternative: dedicated table for sent reports with a reference to `analysis_requests`.
- The `analysis_requests` record is no longer deleted after successful send (per ADR-020); the report remains available for case studies.

---

## How to test

- **Persistence before send:** Run the Job through to send; assert that the record in `analysis_requests` (or reports table) contains the report HTML before the email is queued/sent.
- **Correct content:** The persisted HTML must match what is sent in the email (same content and locale).
- **Failure after persistence:** If persistence runs before send and send fails, the Job retry must not duplicate the HTML; update status and resend the email only.
- **Record retained:** After successful send, the record is not deleted; `report_html` (or equivalent) is set and `processing_status = sent`.

---

## Acceptance criteria

- [ ] Migration added: column(s) to store report HTML (and optionally send timestamp) on `analysis_requests` or related table.
- [ ] In the Job: after generating the HTML, save it to the database before calling Mail::queue (or equivalent).
- [ ] Record is not deleted after successful send; it remains with the stored report.
- [ ] Tests (Feature or Unit) cover persistence before send and correct stored content.

---

## Deployment notes

- Run the new migration in all environments. Existing records (sent before this feature) will not have `report_html` set (nullable). Consider access policy for case-study data (restricted access, anonymization if needed).

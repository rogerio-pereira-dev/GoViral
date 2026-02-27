# FDR-008: Email delivery with report

**Feature:** 8  
**Reference:** docs/04 - Features.md, ADR-006, ADR-010

---

## How it works

- Provider: AWS SES. Sender configured (e.g. report@goviral.you); domain verified, DKIM/SPF in production.
- Laravel: mailable (e.g. `GrowthReportMail`) that receives the report HTML and the recipient email. Message body in HTML (not attachment). Subject and plain alternative text per branding; may vary by locale (en/es/pt).
- The Job (FDR-005) builds the HTML with the LLM content (FDR-007) and calls the mailable; sends to the email from the record in `analysis_requests`. On send failure (SES rejects, timeout), the job fails and goes to retry (FDR-005/006); after 12 failures the record is removed. Report is not stored; no PDF in the MVP (ADR-010).

---

## How to test

- **Happy path:** Job runs; mailable sent; email received in inbox with HTML rendered; subject and sender correct.
- **Content:** All report sections present in the HTML; links and special characters escaped; no XSS (LLM content sanitized when building HTML).
- **Edge cases:** (1) Invalid email (bounce): SES returns error; job fails and retries; after 12 attempts record deleted. (2) Very large HTML: check SES limits (and typical report size). (3) Locale: subject/plain in en, es or pt per request locale. (4) Email client without HTML: readable alternative text.
- **Non-functional:** Do not save HTML to disk or database; do not attach PDF.

---

## Acceptance criteria

- [ ] Mailable configured; send via AWS SES; sender and domain configured.
- [ ] Email body in HTML with all report sections; subject and alternative text defined.
- [ ] Job calls send after building the HTML; send failure causes job retry; after 12 failures record removed.
- [ ] Content sanitized (no XSS); report not stored; no PDF in the MVP.
- [ ] In production: domain verified in SES; DKIM/SPF configured.

---

## Deployment notes

- Env: `MAIL_MAILER=ses`, AWS credentials (or IAM role on Laravel Cloud). In dev: `log` or Mailtrap to avoid sending to real emails. Production: request SES sandbox exit if needed; monitor bounces/complaints.

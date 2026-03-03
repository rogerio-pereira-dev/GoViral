# FDR-007.3: Get report from LLM

**Feature:** 7.3  
**Reference:** FDR-007, FDR-007.2, docs/LLM Prompt Template.md, FDR-005, FDR-008

---

## How it works

- **Prompt building:** use docs/LLM Prompt Template.md. Replace placeholders: `USERNAME`, `BIO`, `NICHE`, `VIDEO_1`, `VIDEO_2`, `VIDEO_3`, `NOTES`, `LANGUAGE` (locale value: en, es or pt). Empty fields (e.g. notes) use a neutral placeholder (e.g. "N/A"). Escape special characters to avoid prompt injection.
- **LLM call:** send the prompt to the provider via the adapter (FDR-007.2). Receive the response (text or markdown).
- **Response parsing:** extract the expected structure (sections: Executive Summary, Profile Score, Inferred Niche, Username Suggestions, Optimized Bio, Profile Optimization, Content Ideas, Viralization Tips, 30-Day Action Plan). If the LLM returns markdown, convert to HTML safely (sanitization). Malformed response: record in `last_error`, do not send email with broken content; job may fail and retry (FDR-005).
- **Return to Job:** return structured content (or already-built HTML) to the Job to build the email and send (FDR-005, FDR-008).

---

## How to test

- **Happy path:** Full payload + locale; prompt built correctly; LLM returns text with the sections; parse extracts blocks; Job builds HTML and sends email with correct content.
- **Locale:** pt, es, en; LANGUAGE placeholder filled; LLM output in the expected language.
- **Empty fields:** notes or some link empty; "N/A" (or similar) placeholder in prompt; no template error.
- **Malformed response:** LLM returns unexpected text; parser does not break the app; fallback or `last_error`; do not send email with invalid HTML.
- **Markdown → HTML:** if applicable, safe conversion (no XSS).

---

## Acceptance criteria

- [ ] Prompt built from the template; all placeholders filled; locale in LANGUAGE.
- [ ] LLM response parsed into the section structure defined in the PRD/template.
- [ ] Content (or HTML) passed to the Job for email building (FDR-005, FDR-008).
- [ ] Malformed response handled (last_error; do not send broken email).
- [ ] Markdown converted to HTML with sanitization when applicable.

---

## Deployment notes

- No extra env beyond FDR-007.2. Template (docs/LLM Prompt Template.md) is the reference; template changes require updating the prompt-building code.

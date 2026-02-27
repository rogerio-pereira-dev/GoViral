# FDR-007: LLM integration

**Feature:** 7 (overview; details in FDR_007_1, FDR_007_2, FDR_007_3)  
**Reference:** docs/04 - Features.md, ADR-014, docs/LLM Prompt Template.md

---

## How it works

- The Job (FDR-005) needs to get from the LLM the structured analysis content to build the HTML report and send by email (FDR-008). The integration is split into three sub-features:
- **7.1** Provider research and decision (FDR_007_1): spike, provider and approach choice, code interface, ADR.
- **7.2** Integration (FDR_007_2): implement adapter, env config, Job call, timeout/error handling.
- **7.3** Get report (FDR_007_3): build prompt from template, call LLM, parse response, return content to the Job.

---

## How to test

- See FDR_007_1, FDR_007_2, FDR_007_3 for tests and edge cases.

---

## Acceptance criteria

- [ ] Interface defined; Job depends on it (FDR_007_1).
- [ ] Adapter implemented and configurable (FDR_007_2).
- [ ] Prompt and parse implemented; content passed to Job and email (FDR_007_3).

---

## Deployment notes

- Env defined in FDR_007_2 (API key, model). Do not expose API key on the frontend.

# FDR-007.1: LLM provider research and decision

**Feature:** 7.1  
**Reference:** FDR-007, docs/04 - Features.md, ADR-014

---

## How it works

- **Technical spike:** evaluate candidate providers (OpenAI, Gemini, Anthropic) on: cost per request, output quality (fit for the report template), latency, and usage limits.
- **Packages/SDKs:** evaluate Laravel or PHP packages for each provider; integration pattern: adapter/strategy inside Laravel (common interface, e.g. `LlmClient::generateReport(array $payload, string $locale): array`) vs. external orchestration (e.g. n8n). Choose approach and provider.
- **Documentation:** produce an **implementation ADR** with the chosen provider and approach (adapter in Laravel or external). Update ADR-014 (status and link to the new ADR) when the decision is made.
- **Code contract:** define the interface the Job (FDR-005) will use to call report generation (payload + locale → section structure), so the implementation can be swapped without changing the Job.

---

## How to test

- Compare cost/request and response time for a typical payload (e.g. minimal template) for each provider.
- Validate that the defined interface is sufficient for the Job and the template (docs/LLM Prompt Template.md).
- Implementation ADR reviewed and ADR-014 updated.

---

## Acceptance criteria

- [ ] Spike completed with cost, quality, and latency comparison (OpenAI, Gemini, Anthropic).
- [ ] Decision recorded: provider and approach (Laravel adapter vs. external).
- [ ] Implementation ADR created; ADR-014 updated (reference or status).
- [ ] Interface/contract in code defined (e.g. `generateReport(payload, locale)`); Job depends only on the interface.

---

## Deployment notes

- No deploy changes until implementation (FDR-007.2 and FDR-007.3); env and keys will be defined after the decision.

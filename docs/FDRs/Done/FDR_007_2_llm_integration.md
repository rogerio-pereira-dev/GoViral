# FDR-007.2: Integration with the LLM provider

**Feature:** 7.2  
**Reference:** FDR-007, FDR-007.1, FDR-005, ADR-014

---

## How it works

- Implement the **adapter** that satisfies the interface defined in FDR-007.1 for the chosen provider (OpenAI, Gemini, or Anthropic). Configuration via environment variables: API key, model, endpoint (when applicable).
- The **Job** (FDR-005) calls the adapter with data from the record in `analysis_requests` and the `locale`; it does not depend on the concrete provider. On timeout or API error (rate limit, 5xx), the adapter throws or returns an error; the Job handles it with retry (FDR-005/006).
- Do not implement prompt building or response parsing here — that is FDR-007.3. This feature covers: HTTP client/SDK for the provider, authentication, error handling, and integration in the Job pipeline.

---

## How to test

- **Happy path:** Job calls the adapter; adapter sends request to the provider (mock or real payload); response received and passed to caller (or failure handled).
- **Timeout:** simulate slow LLM; adapter should fail with timeout; Job retries per FDR-006.
- **API error (5xx, rate limit):** adapter propagates error; Job records `last_error` and retries.
- **Config:** change model or key via env; adapter uses new config without changing Job code.

---

## Acceptance criteria

- [ ] Adapter implemented for the provider decided in FDR-007.1; implements the defined interface.
- [ ] Configuration via env (API key, model, endpoint if needed).
- [ ] Job (FDR-005) calls the adapter; timeout and API errors handled; retry delegated to Job/queue (FDR-006).
- [ ] API key not exposed on the frontend.

---

## Deployment notes

- Env: `LLM_API_KEY`, `LLM_MODEL` (and provider-specific variables). Staging can use a cheaper model to reduce cost.

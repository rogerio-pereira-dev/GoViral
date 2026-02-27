# ADR-014: LLM Integration — Decision Deferred

## Status

Deferred

## Context

The analysis pipeline depends on an LLM provider to generate the report content (summary, score, suggestions, 30-day plan, etc.). The HLD states that the provider and integration approach are not yet defined. Candidate providers: OpenAI, Gemini, Anthropic. Possible approaches: (A) adapter/strategy inside Laravel (interface `LlmClient`, implementations per provider, selection via environment variable); (B) external orchestration (e.g. n8n).

## Decision

**Defer the final decision** on (1) which LLM provider to use and (2) which integration approach (in-app adapter vs. external orchestration) until completion of a **technical spike** and an **implementation ADR** specific to LLM integration.

Until then, the architecture must remain **provider-agnostic** (e.g. interface/contract in code) so that the choice can be made after comparing cost, quality, and operations.

## Consequences

- **Positive:** Avoids premature commitment to a provider or pattern; spike allows validating cost and quality before implementing.
- **Negative:** Development of the analysis layer (job, HTML template) may need a mock or temporary implementation until the decision.
- **Neutral:** A future ADR will document the chosen provider, the integration pattern (adapter vs. external), and usage conventions (retry, timeouts, output format).

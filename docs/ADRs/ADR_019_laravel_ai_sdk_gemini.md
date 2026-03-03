# ADR-019: Laravel AI SDK com Gemini

## Status

Approved

## Context

The analysis pipeline (see ADR-014) requires an LLM to generate report content (summary, score, suggestions, 30-day plan, etc.). We need a concrete integration approach and provider. Laravel 12 offers the official **Laravel AI SDK** ([documentation](https://laravel.com/docs/12.x/ai-sdk)), which provides a unified API for multiple providers (OpenAI, Anthropic, Gemini, etc.), agents, structured output, streaming, queueing, and testing support.

## Decision

1. **Use the Laravel AI SDK** (`laravel/ai`) as the integration layer for LLM calls.
2. **Use Gemini** as the initial provider, configured via `GEMINI_API_KEY` and `config/ai.php`.
3. Keep the architecture **provider-agnostic** in application code: use the SDK’s abstractions (agents, prompts, optional structured output) so that the provider can be switched or combined (e.g. failover) via configuration without changing business logic.
4. Prefer **agents** for report generation when it fits (instructions, optional tools, structured output); use the SDK’s **anonymous agents** or simple `prompt()` where a full agent class is unnecessary.
5. Use a job that calls the agent.
6. Use the SDK’s **testing** utilities (`Agent::fake()`, `Image::fake()`, etc.) for unit and feature tests without calling real APIs.

## Configuration

- Install: `composer require laravel/ai`
- Publish config and migrations: `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"`
- Environment: set `GEMINI_API_KEY` in `.env`
- Default text/model and provider can be set in `config/ai.php` (e.g. default provider `gemini`, model as per docs).

## Consequences

- **Positive:** Single, documented API; native Laravel integration; support for agents, structured output, streaming, queueing, and testing; easy to add other providers (OpenAI, Anthropic) or failover later.
- **Negative:** Dependency on Laravel AI SDK and Gemini availability; team must follow SDK patterns and config.
- **Neutral:** Migrations for `agent_conversations` and `agent_conversation_messages` are optional unless we use the “RemembersConversations” feature; we can use agents without conversation persistence for report generation.

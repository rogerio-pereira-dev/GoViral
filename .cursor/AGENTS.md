---
description: Project initialization guide for Cursor/Codex
---

# Project Initialization

## Overview

GoViral is a micro SaaS product designed to provide fast, AI-powered
TikTok profile analysis for beginner and small creators.

The product analyzes user-provided profile information and generates
actionable recommendations focused on growth and monetization. It is
positioned as an affordable, impulse-buy entry product rather than a
marketing consultancy.

Primary value proposition: 
- Fast AI-driven analysis 
- Clear, practical recommendations 
- 30-day action plan 
- No learning curve required

## Stack

- PHP 8.5
- Laravel 12
- Inertia (Vue3) With Vuetify
- Pest PHP tests with 90% minimum coverage
- Laravel Pint for linting
- Docker via Laravel Sail

## Standards

- All code must be in English.
- Follow PSR standards (one statement per line).
- For fluent chains, put one method call per line and keep indentation consistent.
- For assigned fluent chains, use a deeper continuation indent for `->` lines.
- For standalone fluent chains, use a single continuation indent level for `->` lines.
- In tests, chained expectations are allowed when each method call is on its own line.
- Follow Clean code and you have the motto: 
    > Any idiot can write code a computer understand, but only good developer write code that idiots can understand.
- Prefer readable code over cleverness
- Keep controllers thin and move business logic to services.
- Prefer Form Requests for validation.
- Use interfaces for services when appropriate.
- Every Eloquent model must have an equivalent factory in `database/factories`.
- All frontend pages must use Vuetify only (components and styling primitives); do not use Tailwind, Bootstrap, or other UI component libraries.
- All pages must have dedicated browser tests and be included in smoke route checks (`tests/Browser/WebRoutesTest.php`).
- Every new public page (ignore Core Routes (Admin) Group) must also have translation coverage tests (en/es/pt), preferably via Feature tests asserting Inertia props.
- Critical user journeys must include at least one end-to-end browser test covering validation, successful submit, and expected persistence/redirect outcomes.
- For browser automation reliability, interactive UI elements used in E2E tests should expose stable selectors (for example, `dusk` attributes) and/or explicit form field names.
- Although i can write instructions to you (ai) in portguese all code should be in english

## Environment and Commands

All commands must run inside Sail. Use the rule in `.cursor/rules/starting-environment.mdc` as the source of truth for setup and test commands.

Key commands:

```
./vendor/bin/sail up -d
./vendor/bin/sail artisan test --parallel --coverage --min=90
./vendor/bin/sail artisan test --type-coverage --min=90 --parallel
./vendor/bin/sail exec laravel.test vendor/bin/pint --parallel
```

## Queue Worker

The project uses **Redis** as the queue driver (`QUEUE_CONNECTION=redis`). Redis runs as a Sail service (`redis` in `compose.yaml`).

### Running the worker (development / Sail)

```
./vendor/bin/sail artisan queue:work redis --queue=default
```

### Running the worker (production)

Use a process manager (supervisor, systemd, or Laravel Cloud) to keep the worker alive:

```
php artisan queue:work redis --queue=default --tries=12 --backoff=300 --timeout=300 --sleep=3
```

Key flags:
- `--tries=12` — max 12 attempts per job (ADR-011)
- `--backoff=300` — 5-minute delay between retries
- `--timeout=300` — kill a job after 300 s (LLM + email ceiling)
- `--sleep=3` — poll interval when queue is empty

The `retry_after` in `config/queue.php` (Redis connection) is set to **600 s** so Redis does not re-queue a job that is still running within the 300 s timeout window.

## Pull requests

When a feature is complete and the branch is pushed, **create the PR using the GitHub MCP server** (MCP tools), not the `gh` CLI. If MCP is unavailable, push the branch and tell the user to open the PR manually (branch name + repo URL).

## Notes

- Use docs in `docs/` for project and setup details

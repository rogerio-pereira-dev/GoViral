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
- Follow Clean code and you have the motto: 
    > Any idiot can write code a computer understand, but only good developer write code that idiots can understand.
- Prefer readable code over cleverness
- Keep controllers thin and move business logic to services.
- Prefer Form Requests for validation.
- Use interfaces for services when appropriate.
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

## Notes

- Use docs in `docs/` for project and setup details

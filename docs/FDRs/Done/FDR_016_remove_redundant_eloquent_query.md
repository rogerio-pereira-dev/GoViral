# FDR-016: Remove redundant `::query()` from Eloquent entry points

**Feature:** 15 (code style — Eloquent entry)  
**Reference:** docs/04 - Features.md §15; `.cursor/AGENTS.md` (PSR / fluent chains); `.cursor/skills/backend-laravel/SKILL.md`

---

## How it works

- **Goal:** Remove unnecessary `Model::query()` where the builder can start directly on the model (e.g. `Model::where(...)`). This reduces noise and matches common Laravel style in this repo.
- **Scope:** All first-party PHP under `app/`, `routes/`, `tests/`, `database/` (seeders, factories as applicable). Do not change `vendor/`.
- **Pattern:** Replace `SomeModel::query()->where(...)` with `SomeModel::where(...)` (and equivalent for `firstOrCreate`, scopes, etc.), keeping **one method call per line** on fluent chains and existing line breaks where they already comply with project rules.
- **Exceptions:** If a project convention or Laravel quirk requires `query()` (e.g. rare macro edge cases), document in the PR why that call site is exempt; default is to remove.
- **Discovery:** First-party PHP had `::query()->` in `DiscountCoupon::findValidByCode`, seeders, and coupon-related tests; all were replaced with direct model entry.

---

## How to test

- Run full test suite and Pint after refactor (`./vendor/bin/sail artisan test ...`, Pint per project rules).
- No intentional behavior change: same SQL semantics for equivalent builder entry points.

---

## Acceptance criteria

- [x] No redundant `SomeModel::query()->` (or `self::query()->` / `static::query()->`) where direct `SomeModel::` / `self::` entry is equivalent and readable.
- [x] `grep`/`rg` over first-party PHP for `::query()->` returns no unnecessary usages (or only documented exceptions).
- [x] All tests pass; Pint passes.

---

## Deployment notes

- None (refactor only).

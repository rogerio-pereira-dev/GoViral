# FDR-017: Fluent chains — one instruction per line; no nested calls in arguments

**Feature:** 16 (code style — fluent chains and arguments)  
**Reference:** docs/04 - Features.md §16; `.cursor/AGENTS.md`; `.cursor/skills/backend-laravel/SKILL.md` (DiscountCouponController baseline)

---

## How it works

- **Goal:** Refactor PHP (and PHPUnit/Pest test code) so that:
  1. **Fluent / chained calls:** Prefer **one method call per line** (per AGENTS.md). Avoid long one-line chains where the project would split them across lines.
  2. **Arguments:** Do **not** pass expressions that invoke constructors or methods **inside** another call’s argument list when splitting improves clarity and complies with “one statement per line.” Extract to a variable, configure the object on subsequent lines if needed, then pass the variable.

**Example (mail / queue):**

```php
// Bad: `new` and `onQueue` inside Mail::...->queue(...)
$mailable = new GrowthReportMail($reportHtml, $locale);
$mailable->onQueue('emails');

Mail::to($analysisRequest->email)
    ->queue($mailable);
```

- **Scope:** First-party PHP in `app/`, `routes/`, `tests/`, `database/` (same as FDR-016). Prioritize high-churn areas: jobs, controllers, mail, HTTP tests using long fluent test helpers (`$this->...->post(...)`).
- **PHP ternaries:** Reminder — project rule is **no ternary operators** in PHP; use `if` / `else` or early returns (do not introduce ternaries while refactoring).

---

## How to test

- Full test suite + Pint; spot-check critical paths (webhook, checkout, job, mail) via existing Feature/Browser tests.

---

## Acceptance criteria

- [ ] New or refactored code avoids **nested** `new Foo(...)->bar()` (and similar) **as arguments** where extraction to locals is straightforward.
- [ ] Chained calls follow **one method per line** aligned with project conventions (see FDR-018 for indentation nuance).
- [ ] No new ternary operators in PHP.
- [ ] All tests pass; Pint passes.

---

## Deployment notes

- None.

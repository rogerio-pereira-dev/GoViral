# FDR-018: Logical alignment for multiline fluent calls and argument lists

**Feature:** 17 (code style — indentation and readability)  
**Reference:** docs/04 - Features.md §17; `.cursor/AGENTS.md` (fluent chain indentation); `.cursor/skills/backend-laravel/SKILL.md` (DiscountCouponController reference style)

---

## How it works

- **Goal:** Apply **logical alignment** to multiline fluent chains and their arguments so continuation lines read clearly: the **first** call in a chain may start on the same line as the assignment/receiver; **subsequent** chained calls and **multi-line argument lists** (arrays, `route(...)`, payloads) are indented consistently with surrounding context.
- **Scope:** First-party PHP (especially tests using `$this->withSession([...])->post(route(...), [...])`, HTTP/client facades, Eloquent chains, Mail facade). Match the readability goal illustrated below; use `app/Http/Controllers/Core/DiscountCouponController.php` as a **baseline** for controller-style breaks where applicable.

**Example (test request):**

```php
$response = $this->withSession([
        'locale' => 'pt',
    ])
    ->post(
        route('form.store'),
        [
            ...validFormPayload(),
            'tiktok_username' => '',
            'bio' => '',
            'video_url_1' => '',
            'video_url_2' => '',
            'video_url_3' => '',
        ]
    );
```

- **Coordination with FDR-017:** Apply FDR-017 first where the same lines need extracted variables; FDR-018 then normalizes indentation without re-introducing nested calls in parameters.

---

## How to test

- Pint (may reformat; ensure project Pint rules align with intended style).
- Full test suite; visually review diffs for readability.

---

## Acceptance criteria

- [ ] Multiline fluent chains in prioritized files (controllers, jobs, tests) use **consistent, logical** indentation (no arbitrary one-space drift); array and argument lists break across lines readably.
- [ ] Style is consistent with backend skill reference (`DiscountCouponController`) where the same constructs appear.
- [ ] All tests pass; Pint passes.

---

## Deployment notes

- None.

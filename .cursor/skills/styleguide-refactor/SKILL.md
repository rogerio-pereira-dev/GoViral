---
name: styleguide-refactor
description: Apply styleguide refactoring with strict full-coverage validation for a target scope (specific folder, backend parts, frontend resources, or whole project). Use when the user asks to refactor code style and requires zero partial application.
---

# Styleguide Refactor

## Goal

Refactor code style for a chosen scope and guarantee that **all requested rules** are applied to **all files in scope**.

Partial refactoring is unacceptable.

## Accepted Input Scope

The skill must accept exactly one scope mode per run:

1. **Specific folder** (user-provided path)
2. **Backend only** (one or more of):
   - `app`
   - `bootstrap`
   - `database`
   - `routes`
   - `tests`
3. **Frontend only**:
   - `resources`
4. **Whole project** (workspace root)

If the user asks for multiple modes at once, normalize to one explicit scope before editing.

## Mandatory Workflow

1. Read these rule files first, before any other analysis or edit:
   - `.cursor/rules/method-chains-alignment.mdc`
   - `.cursor/rules/method-chains-no-nested-calls.mdc`
2. Read all additional rule files explicitly requested by the user.
3. Build the file list for the selected scope (recursive).
4. Report file count before refactoring.
5. Audit all files in scope against all requested rules.
6. Refactor all non-compliant files directly (no generated scripts).
7. Re-audit all files in scope.
8. Run formatter/linter if applicable (for PHP use Sail + Pint).
9. Re-open every changed file and perform a manual structural audit focused on indentation and closings.
10. If any file still fails, continue fixing until zero remaining violations.
11. Run relevant tests/lint checks when applicable.
12. Provide a completion report with:
   - scope used
   - total files scanned
   - total files changed
   - confirmation: zero remaining violations

## PSR/Indentation Guardrails (Mandatory)

- Enforce **one statement per line** and avoid visual-column alignment padding between variable names and `=`.
- In multiline arrays, array items must be indented exactly one level inside `[` and `]` must align with `[` line.
- In multiline calls, arguments must be indented exactly one level inside `(` and `)` must align with call-start line.
- For fluent chains, keep one call per line and deterministic indentation (no mixed indentation widths in the same chain).
- If formatter output conflicts with requested styleguide rules, do not declare success; manually correct files and re-audit.

## Required Final Re-Audit Checks

Before declaring completion, run targeted checks on changed files:

1. No assignment alignment padding (regex example: `\$[A-Za-z0-9_]+\s{2,}=` must return zero).
2. No over-indented array items inside `[...]` for multiline calls.
3. No misaligned closing `]` / `)` / `]);`. 
    Closing parenthesis and brackets must be aligned 1 level inside, not at callers level, check: [](### 5) Visual hierarchy indentation in multiline payloads)
4. No chain line using a different indentation level than the rest of the same chain.
5. No remaining violations in any file inside the selected scope.

## Known Violations and How to Fix

Use these concrete cases as mandatory references during refactor and final audit.

### Visual hierarchy indentation in multiline payloads 
**This one has precedence over all other rules**

Why this style is preferred:
- Indentation is not only style; it helps developers read scope and hierarchy.
- In multiline calls with array payloads, visual nesting should make ownership clear:
  - Call start and call end aligned
  - Argument lines as the first hierarchy level inside the call
  - Array items as the second hierarchy level inside the array

Bad:
```php
$response = $this->post($loginRoute, [
    'email' => $user->email,
    'password' => 'password',
]);
```

Good:
```php
$response = $this->post(
                        $loginRoute,
                        [
                            'email' => $user->email,
                            'password' => 'password',
                        ]
                    );
```

### Assignment padding (breaks PSR readability and "one statement per line" discipline)

Why it is wrong:
- Visual alignment padding (`$user            =`) is unstable, noisy in diffs, and not deterministic.
- Keep a single space around `=`.

Bad:
```php
$user            = User::factory()->create();
```

Good:
```php
$user = User::factory()->create();
```

**Note (only allowed exception):**
- The only allowed exception to assignment padding is when applying **Visual hierarchy indentation in multiline payloads**.
- This exception must follow the owner-approved antipattern section in `.cursor/rules/method-chains-alignment.mdc` (allowed examples block around lines 164-248, including aligned assignments and aligned keys in dense readability blocks).
- Outside that specific exception, keep single-space assignment formatting (`$var = ...`) with no visual alignment padding.

### Multiline call array body indented one extra level

Why it is wrong:
- Array items inside multiline calls must be exactly one level deeper than the `[` line.
- Closing `]);` must align with the call-start indentation.
- Post parameter not aligned

Bad:
```php
$response = $this->post($loginStoreRoute, [
        'email' => $user->email,
        'password' => 'password',
    ]);
```

Good:
```php
$response = $this->post(
                    $loginStoreRoute, 
                    [
                        'email' => $user->email,
                        'password' => 'password',
                    ]
                );
```

### Feature/config arrays with wrong internal indentation

Why it is wrong:
- Associative arrays used in configuration calls follow the same rule: one indentation level inside `[` and aligned closing `]);`.

Bad:
```php
Features::twoFactorAuthentication([
    'confirm' => true,
    'confirmPassword' => true,
]);
```

Good:
```php
Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
```

### Misaligned chained continuation after multiline argument block

Why it is wrong:
- The chain continuation (`->save()`) must keep consistent chain indentation.
- Do not jump to deeper indentation after closing `])`.
- Array with wrong identation

Bad:
```php
$user->forceFill([
    'two_factor_secret' => $twoFactorSecret,
])
            ->save();
```

Good:
```php
$user->forceFill([
        'two_factor_secret' => $twoFactorSecret,
    ])
    ->save();
```

### Final rule for all examples above

- If any one of these patterns appears in any changed file, the task is **not complete**.
- Fix, rerun formatter/linter, then manually re-open files and verify structure before reporting success.

## Hard Rules

- Never stop at partial coverage.
- Never skip files in selected scope.
- Never claim success without final full re-audit.
- Never auto-commit.
- After successful validation, always ask:
  - `Refatoracao concluida e validada. Deseja que eu faca o commit agora?`

## Execution Notes

- Prefer parallel audits/refactors when beneficial, but preserve full coverage guarantees.
- Keep edits deterministic and consistent with the provided rules.
- If rules conflict or are ambiguous, pause and ask for disambiguation before continuing.
- Never trust formatter output alone; completion requires explicit manual structural verification of changed files.

## Output Contract

At the end of each run, return:

- `Scope`: chosen mode/path
- `Files scanned`: number
- `Files changed`: number
- `Violations remaining`: must be `0`
- `Validation`: commands executed and status
- Final question about commit (do not commit automatically)

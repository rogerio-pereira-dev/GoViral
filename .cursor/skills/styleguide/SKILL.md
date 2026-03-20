---
name: styleguide
description: Default styleguide for day-to-day development and refactoring. For refactors, enforce strict full-coverage validation for the selected scope.
---

# Styleguide

## Goal

Use this skill as the default styleguide during development.

Refactor code style for a chosen scope and guarantee that **all requested rules** are applied to **all files in scope**.

Partial refactoring is unacceptable.

## Authority and Precedence (Mandatory)

- Project styleguide rules are the source of truth.
- If `pint`, PSR, language defaults, or editor auto-formatters conflict with this styleguide, the styleguide must win.
- Allowed antipatterns defined by owner rules are intentional and must be preserved.
- Never declare success based only on formatter output.

## Accepted Input Scope (Refactor Mode)

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
For regular feature development (non-refactor), the scope is the files being created or modified in the task.

## Mandatory Workflow

### Development Mode (default)

1. Read these rule files first, before any analysis or edit:
   - `./rules/method-chains-alignment.mdc`
   - `./rules/method-chains-no-nested-calls.mdc`
2. Apply rules to every file touched during implementation.
3. Before finishing, manually re-open changed files and audit indentation, chain depth, multiline call closings, and array structure.
4. Run relevant tests/lint checks when applicable.
5. Report validation status and remaining violations (must be zero in changed files).

### Refactor Mode (strict full-coverage)

1. Read these rule files first, before any other analysis or edit:
   - `./rules/method-chains-alignment.mdc`
   - `./rules/method-chains-no-nested-calls.mdc`
2. Read all additional rule files explicitly requested by the user.
3. Build the file list for the selected scope (recursive).
4. Report file count before refactoring.
5. Audit all files in scope against all requested rules.
6. Refactor all non-compliant files directly (no generated scripts).
7. Re-audit all files in scope.
8. Run formatter/linter if applicable (for PHP use Sail + Pint), only as an auxiliary check.
9. Re-open every changed file and perform a manual structural audit focused on indentation and closings.
10. If formatter/linter rewrites against this styleguide, restore styleguide-compliant formatting manually.
11. If any file still fails, continue fixing until zero remaining violations.
12. Run relevant tests/lint checks when applicable.
13. Provide a completion report with:
- scope used
- total files scanned
- total files changed
- confirmation: zero remaining violations
- confirmation that styleguide precedence was enforced over formatter output

## Base Indentation Guardrails (Mandatory)

- Enforce **one statement per line** and avoid visual-column alignment padding between variable names and `=`, except in owner-approved visual-hierarchy antipattern blocks.
- In multiline arrays, array items must be indented exactly one level inside `[` and `]` must align with `[` line.
- In multiline calls, arguments must be indented exactly one level inside `(` and `)` must align with call-start line.
- For fluent chains, keep one call per line and deterministic indentation (no mixed indentation widths in the same chain).
- Do not enforce fixed absolute spacing as a universal rule. Continuation indentation must follow the project rule idea: visual hierarchy levels (one indentation step per nesting level, with 4 spaces per step).
- If a rule example shows a specific number of spaces, treat it as illustrative context. Validate by hierarchy coherence and deterministic structure.
- If formatter output conflicts with requested styleguide rules, do not declare success; manually correct files and re-audit.

## Required Final Re-Audit Checks

Before declaring completion, run targeted checks on changed files:

1. No assignment alignment padding outside owner-approved visual-hierarchy antipattern blocks (regex example: `\$[A-Za-z0-9_]+\s{2,}=` must return zero outside those blocks).
2. No over-indented array items inside `[...]` for multiline calls.
3. No misaligned closing `]` / `)` / `]);`. 
    Closing parenthesis and brackets must be aligned 1 level inside, not at callers level, check: [](### 5) Visual hierarchy indentation in multiline payloads)
4. No chain line using a different indentation level than the rest of the same chain.
5. For multiline chains, one call per line must be explicit (`$this`, then `->actingAs(...)`, then `->get(...)`, etc.).
6. No remaining violations in any file inside the selected scope.
7. Validate continuation indentation by hierarchy consistency (4-space steps) and local block readability.

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
- Keep the same visual hierarchy depth used by multiline chain continuations in the local block.
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

### Closing `)` must be one indentation level inside call start

Why it is wrong:
- In multiline calls, the closing `)` cannot be aligned at the same column as the caller line because visual hierarchy requires one inner level.

Bad:
```php
$response = $this->post(
                $loginStoreRoute,
                [
                    'email' => $user->email,
                    'password' => 'password',
                ]
            );

$user->forceFill([
    'two_factor_secret' => $twoFactorSecret,
    'two_factor_recovery_codes' => $encryptedTwoFactorRecoveryCodes,
    'two_factor_confirmed_at' => $twoFactorConfirmedAt,
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

$user->forceFill([
        'two_factor_secret' => $twoFactorSecret,
        'two_factor_recovery_codes' => $encryptedTwoFactorRecoveryCodes,
        'two_factor_confirmed_at' => $twoFactorConfirmedAt,
    ]);
```

### Chain continuation depth must be consistent

Why it is wrong:
- When a chain starts the next call must keep a consistent continuation depth.
- Use deep jumps (for example 3 levels below) to maintain visual hierarchy.

Bad:
```php
$response = $this->actingAs($user)
    ->post($logoutRoute);
```

Good:
```php
$response = $this->actingAs($user)
                ->post($logoutRoute);
```

### Array payload must not stay inline inside multiline-call hierarchy

Why it is wrong:
- `array in line` reduce hierarchy readability and make closings hard to track.
- In multiline calls, payload arrays must open in their own line and keep inner keys one level deeper.

Bad:
```php
$this->post(
    $passwordEmailRoute,
    ['email' => $user->email]
);

$array = ['foo', 'bar'];
```

Good:
```php
$this->post(
                $passwordEmailRoute,
                [
                    'email' => $user->email,
                ]
            );

$array = [
            'foo', 
            'bar'
         ];
```

### Standalone long setup blocks may use owner-approved readability alignment

Why it is wrong:
- Dense blocks can become hard to scan when variable declarations are visually flat.
- In long blocks, owner-approved readability alignment from `.cursor/rules/method-chains-alignment.mdc` (allowed antipatterns) may be applied intentionally.

Bad:
```php
$user = User::factory()->create();
$twoFactorSecret = encrypt('test-secret');
$twoFactorRecoveryCodes = json_encode(['code1', 'code2']);
$encryptedTwoFactorRecoveryCodes = encrypt($twoFactorRecoveryCodes);
$twoFactorConfirmedAt = now();
$loginRoute = route('login');
$twoFactorLoginRoute = route('two-factor.login');
```

Good:
```php
$user                            = User::factory()->create();
$twoFactorSecret                 = encrypt('test-secret');
$twoFactorRecoveryCodes          = json_encode(['code1', 'code2']);
$encryptedTwoFactorRecoveryCodes = encrypt($twoFactorRecoveryCodes);
$twoFactorConfirmedAt            = now();
$loginRoute                      = route('login');
$twoFactorLoginRoute             = route('two-factor.login');
```

### Indentation consistency

Why it is wrong:
- Config arrays must preserve deterministic hierarchy: items one level inside `[`, closing `]` and `)` coherent with call shape.
- A common violation is mixing item depth and closing depth (`]);` one level below expected).

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

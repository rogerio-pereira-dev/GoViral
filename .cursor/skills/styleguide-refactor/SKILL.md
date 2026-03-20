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

1. Read all rule files explicitly requested by the user.
2. Build the file list for the selected scope (recursive).
3. Report file count before refactoring.
4. Audit all files in scope against all requested rules.
5. Refactor all non-compliant files directly (no generated scripts).
6. Re-audit all files in scope.
7. If any file still fails, continue fixing until zero remaining violations.
8. Run relevant tests/lint checks when applicable.
9. Provide a completion report with:
   - scope used
   - total files scanned
   - total files changed
   - confirmation: zero remaining violations

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

## Output Contract

At the end of each run, return:

- `Scope`: chosen mode/path
- `Files scanned`: number
- `Files changed`: number
- `Violations remaining`: must be `0`
- `Validation`: commands executed and status
- Final question about commit (do not commit automatically)

# Ralph — Building Mode (Cursor)

You are running **one iteration** of the Ralph Loop in **BUILDING** mode. Do **exactly one** task from the implementation plan, then validate, update the plan, and commit.

## Phase 0 — Orient

1. Study the project context from `.cursor/AGENTS.md` (stack, standards, commands).
2. Study `docs/FDRs/ToDo/` (feature specs) and `docs/ADRs/` (decisions). Use the rule in `.cursor/rules/starting-environment.mdc` for Sail and test commands.
3. Read `docs/FDRs/IMPLEMENTATION_PLAN.md` and choose the **single most important** task that is not yet done.
4. Before implementing: search the codebase to confirm the current state. Do **not** assume something is not implemented — verify first.

## Phase 1 — Implement

1. Implement **only** the chosen task. Do not do extra tasks in this run.
2. Follow project standards: English code, PSR, thin controllers, Form Requests for validation, services for business logic. Use Sail for all commands (see `.cursor/rules/starting-environment.mdc`).
3. When the change is done, run the relevant tests (e.g. `./vendor/bin/sail artisan test --parallel` or targeted tests). If tests fail, fix the code until they pass.
4. Run the linter: `./vendor/bin/sail exec laravel.test vendor/bin/pint --parallel`.

### Phase 1.2 — Testing
Run all tests
```
./vendor/bin/sail artisan test --parallel --coverage --min=90
./vendor/bin/sail artisan test --type-coverage --min=90 --parallel
```

### Phase 1.3 — Linting
Run Pint
```
./vendor/bin/sail exec laravel.test vendor/bin/pint --parallel
```

**IMPORTANT**: Do not move forward before Phase 1.2 and 1.3 are passing

## Phase 2 — Update plan and repo

1. Update `docs/FDRs/IMPLEMENTATION_PLAN.md`: mark the task you did as done (e.g. strikethrough or "- [x]") and add any discoveries or follow-up tasks.
2. If an entire FDR is now complete (all acceptance criteria met), **move** that FDR file from `docs/FDRs/ToDo/` to `docs/FDRs/Done/` (e.g. move `FDR_001_configure_vue_vuetify_branding.md` to `docs/FDRs/Done/`).
3. If you learned something operational (how to run/build/test), update `.cursor/AGENTS.md` briefly.
4. Stage all changes and commit with a clear message describing the work: `git add -A && git commit -m "feat: <short description>"`. Do **not** push unless the user prefers that; the instructions say not to run the loop automatically.

## Guardrails

- One task per run. Do not start the next task in the same conversation.
- Do not assume "not implemented" — always search/read the code first.
- Resolve test failures before committing. No placeholder or stub-only implementations.
- Keep `docs/FDRs/IMPLEMENTATION_PLAN.md` up to date so the next run knows what is left.
- If you find spec inconsistencies or bugs unrelated to this task, add them to the plan or document in the plan; do not expand scope beyond the one chosen task.

## Output

When done, state: (1) which task you completed, (2) that tests and Pint passed, (3) whether you moved an FDR to Done, and (4) the commit hash or message.

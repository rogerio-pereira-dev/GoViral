---
name: ralph-cursor
description: Run the Ralph Loop (Planning or Building) in Cursor using docs/FDRs/ToDo and IMPLEMENTATION_PLAN; one task per Building run, move completed FDRs to docs/FDRs/Done.
---

# Ralph Loop with Cursor

Use this skill when the user wants to run **Planning** or **Building** for the Ralph workflow, or when they refer to "Ralph", "Ralph Loop", or "one task from the plan".

## What is the Ralph Loop here

- **Planning:** Analyze gap between FDRs in `docs/FDRs/ToDo/` and the codebase; output is an updated `docs/FDRs/IMPLEMENTATION_PLAN.md` only (no code changes, no commits).
- **Building:** Pick the single most important task from `docs/FDRs/IMPLEMENTATION_PLAN.md`, implement it, run tests and Pint, update the plan, move the FDR to `docs/FDRs/Done/` if the whole feature is done, then commit. One task per agent run.

## How to run

1. **Planning**
   - Open or paste the contents of `.cursor/ralph/PROMPT_plan.md` into the chat, or say "Run Ralph Planning".
   - Follow the prompt: read docs, ADRs, FDRs, and code; then write/update only `docs/FDRs/IMPLEMENTATION_PLAN.md`.
   - Do not implement code or commit.

2. **Building**
   - Open or paste the contents of `.cursor/ralph/PROMPT_build.md` into the chat, or say "Run Ralph Building" or "Do one Ralph task".
   - Follow the prompt: choose one task from the plan, implement it, test, lint, update plan, move FDR to Done if applicable, commit.
   - Do not do a second task in the same run.

## Key paths

| What | Where |
|------|--------|
| Planning prompt | `.cursor/ralph/PROMPT_plan.md` |
| Building prompt | `.cursor/ralph/PROMPT_build.md` |
| Plan (state) | `docs/FDRs/IMPLEMENTATION_PLAN.md` |
| Feature specs | `docs/FDRs/ToDo/*.md` |
| Done features | `docs/FDRs/Done/*.md` |
| ADRs | `docs/ADRs/*.md` |
| Project context | `.cursor/AGENTS.md`, `.cursor/rules/starting-environment.mdc` |

## Rules to respect

- One task per Building run.
- Do not assume something is not implemented — search the code first.
- Use Sail for all commands (tests, Pint); see `.cursor/rules/starting-environment.mdc`.
- When an FDR is fully done, move its file from `docs/FDRs/ToDo/` to `docs/FDRs/Done/`.

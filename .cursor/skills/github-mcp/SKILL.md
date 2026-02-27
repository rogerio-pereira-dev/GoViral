---
name: github-mcp
description: Configure and use GitHub MCP with Docker and local secrets, then execute generic GitHub workflows (push, PR, checks, merge) across repositories. Use when the user asks about GitHub MCP setup, PAT scope, PR automation, or status checks.
---

# GitHub MCP

Use this skill for repository-agnostic GitHub MCP setup and operation.

## Setup principles

1. Prefer project-local MCP config (`.cursor/mcp.json`) when the user wants repository scoping.
2. Keep secrets in a local env file (`.cursor/.env.mcp`) and never commit it.
3. Use Docker server: `ghcr.io/github/github-mcp-server`.
4. Reload Cursor after changing config or token.

## Security rules

- Never print PAT values in chat.
- Never read secret file contents into chat output.
- Ensure `/.cursor/.env.mcp` is in `.gitignore`.
- Rotate token immediately if exposed.

## Validation workflow

1. Confirm MCP server is connected in Cursor settings.
2. Validate authentication:
   - `Use GitHub MCP get_me`
3. Validate repository access:
   - `List pull requests for <owner>/<repo>`
4. If `401/403`, check token scope, owner/repo access, and org authorization/SSO.

## Generic GitHub workflow

When feature work is complete:

1. Push feature branch.
2. Open PR to `main`.
3. Wait for required checks (for example, lint/tests).
4. Merge only when checks pass.

If branch protection is unavailable, follow the same gate manually.

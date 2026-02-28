#!/usr/bin/env bash
# Ralph Loop — execução contínua sem interação (Claude CLI ou Cursor CLI)
# Uso:
#   ./loop.sh                    # Building com Claude, loop até Ctrl+C
#   ./loop.sh cursor             # Building com Cursor Agent CLI, loop até Ctrl+C
#   ./loop.sh 20                 # Building (Claude), no máximo 20 iterações
#   ./loop.sh cursor 20          # Building (Cursor), no máximo 20 iterações
#   ./loop.sh plan               # Planning (Claude)
#   ./loop.sh cursor plan        # Planning (Cursor)
#
# Backends:
#   - Claude: https://claude.ai/download  (padrão se não passar 'cursor')
#   - Cursor: https://cursor.com/docs/cli  (use primeiro arg 'cursor')
# O script deve ser executado na raiz do projeto (onde está docs/, .cursor/, app/).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Backend: "cursor" ou "claude"
BACKEND="claude"
if [[ "${1:-}" == "cursor" ]]; then
  BACKEND="cursor"
  shift
fi

# Modo e limite de iterações
MODE="build"
MAX_ITERATIONS=0
if [[ "${1:-}" == "plan" ]]; then
  MODE="plan"
  PROMPT_FILE=".cursor/ralph/PROMPT_plan.md"
  MAX_ITERATIONS="${2:-0}"
elif [[ "${1:-}" =~ ^[0-9]+$ ]]; then
  PROMPT_FILE=".cursor/ralph/PROMPT_build.md"
  MAX_ITERATIONS="$1"
else
  PROMPT_FILE=".cursor/ralph/PROMPT_build.md"
fi

if [[ ! -f "$PROMPT_FILE" ]]; then
  echo "Erro: $PROMPT_FILE não encontrado."
  exit 1
fi

# Verificar backend e comando
if [[ "$BACKEND" == "cursor" ]]; then
  if ! command -v agent &>/dev/null; then
    echo "Erro: Cursor Agent CLI não encontrado."
    echo "Instale: curl https://cursor.com/install -fsS | bash"
    echo "Docs: https://cursor.com/docs/cli/headless"
    exit 1
  fi
else
  if ! command -v claude &>/dev/null; then
    echo "Erro: Claude CLI não encontrado."
    echo "Instale em: https://claude.ai/download"
    echo "Depois execute 'claude' para autenticar."
    exit 1
  fi
fi

CURRENT_BRANCH=$(git branch --show-current 2>/dev/null || echo "unknown")
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Ralph Loop ($BACKEND)"
echo "Modo:   $MODE"
echo "Branch: $CURRENT_BRANCH"
echo "Prompt: $PROMPT_FILE"
[[ "$MAX_ITERATIONS" -gt 0 ]] && echo "Max:    $MAX_ITERATIONS iterações"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Cada iteração = uma tarefa (Building) ou uma atualização do plano (Planning)."
echo "Para parar: Ctrl+C"
echo ""
echo "Dica: abra outro terminal na raiz do projeto e rode o comando abaixo"
echo "      para acompanhar o progresso (git + plano) a cada 3s:"
echo "  while true; do clear; date; git status -sb; echo '---'; tail -25 docs/FDRs/IMPLEMENTATION_PLAN.md; sleep 3; done"
echo ""

run_iteration() {
  if [[ "$BACKEND" == "cursor" ]]; then
    # Cursor Agent CLI: -p = headless, --force = aplicar mudanças sem confirmação
    # stdbuf (Linux) reduz buffer para você ver saída do agente em tempo real
    if command -v stdbuf &>/dev/null; then
      stdbuf -o0 -e0 agent -p --force "$(cat "$PROMPT_FILE")"
    else
      agent -p --force "$(cat "$PROMPT_FILE")"
    fi
  else
    cat "$PROMPT_FILE" | claude -p \
      --dangerously-skip-permissions \
      --model opus
  fi
}

ITERATION=0
while true; do
  if [[ "$MAX_ITERATIONS" -gt 0 && "$ITERATION" -ge "$MAX_ITERATIONS" ]]; then
    echo "Limite de $MAX_ITERATIONS iterações atingido."
    break
  fi

  echo "======================== ITERAÇÃO $((ITERATION + 1)) ========================"
  run_iteration
  CODE=$?

  if [[ "$CODE" -ne 0 ]]; then
    echo "Agente encerrou com código $CODE. Parando o loop."
    exit "$CODE"
  fi

  # Push após cada iteração (opcional; descomente se quiser)
  # git push origin "$(git branch --show-current)" 2>/dev/null || true

  ITERATION=$((ITERATION + 1))
  echo ""
  echo "Próxima iteração em 2s..."
  sleep 2
done

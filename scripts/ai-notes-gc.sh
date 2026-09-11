#!/usr/bin/env bash
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; under version 2
# of the License (non-upgradable).
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Copyright (c) 2026 (original work) Open Assessment Technologies SA.
#
# Branch-scoped local agent notes under .ai/ (gitignored).
#
# Usage (from repo root):
#   scripts/ai-notes-gc.sh              # archive+remove work dirs for deleted branches
#   scripts/ai-notes-gc.sh --checkout   # ensure workspace for HEAD, then GC
#   scripts/ai-notes-gc.sh --self-test  # isolated checks (no repo side effects)
#
# Enable hooks once per clone:
#   git config core.hooksPath .githooks

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
AI_DIR="${ROOT}/.ai"
WORK_DIR="${AI_DIR}/work"
ARCHIVE_DIR="${AI_DIR}/archive"
MODE="${1:-}"

# Long-lived branches: never auto-archive their work/ dirs.
is_protected_slug() {
  case "$1" in
    develop|master|main) return 0 ;;
    *) return 1 ;;
  esac
}

# Injective slug: % → %25, _ → %5F, / → _
# Avoids collisions between feature/a and feature__a / feature_a.
branch_to_slug() {
  local branch="$1" out
  out="${branch//%/\%25}"
  out="${out//_/\%5F}"
  out="${out//\//_}"
  printf '%s' "$out"
}

slug_to_branch() {
  local slug="$1" out
  out="${slug//_//}"
  out="${out//\%5F/_}"
  out="${out//\%25/%}"
  printf '%s' "$out"
}

ensure_readme() {
  local readme="${AI_DIR}/README.md"
  if [ -f "$readme" ]; then
    return 0
  fi
  cat >"$readme" <<'EOF'
# Local agent notes (`.ai/`)

This directory is **gitignored**. Use it for branch-scoped working notes
(spikes, decisions, scratch). Do **not** put durable project rules here —
those belong in tracked `AGENTS.md`.

## Layout

```text
.ai/
  README.md
  current -> work/<branch-slug>/
  work/<branch-slug>/     # notes for that local branch
  archive/*.tar.gz        # archived after the branch disappears
```

Branch slug (injective): `%` → `%25`, `_` → `%5F`, `/` → `_`
(e.g. `feat/AUT-4657-agents-md` → `feat_AUT-4657-agents-md`;
`feat_x` → `feat%5Fx`).

## Enable checkout hooks (once per clone)

```bash
git config core.hooksPath .githooks
```

After deleting a local branch, run:

```bash
scripts/ai-notes-gc.sh
```
EOF
}

ensure_workspace() {
  local branch slug dir
  branch="$(git -C "$ROOT" rev-parse --abbrev-ref HEAD)"
  if [ "$branch" = "HEAD" ]; then
    # Detached HEAD — skip ensure
    return 0
  fi
  slug="$(branch_to_slug "$branch")"
  dir="${WORK_DIR}/${slug}"
  mkdir -p "$dir" "$ARCHIVE_DIR"
  ensure_readme
  if [ ! -f "${dir}/INDEX.md" ]; then
    cat >"${dir}/INDEX.md" <<EOF
# Agent notes — \`${branch}\`

Local working notes for this branch. Not committed.
EOF
  fi
  ln -sfn "work/${slug}" "${AI_DIR}/current"
}

local_branch_exists() {
  local branch="$1"
  git -C "$ROOT" show-ref --verify --quiet "refs/heads/${branch}"
}

archive_and_remove() {
  local slug="$1"
  local dir="${WORK_DIR}/${slug}"
  local stamp archive current_target n
  [ -d "$dir" ] || return 0
  mkdir -p "$ARCHIVE_DIR"
  stamp="$(date +%Y%m%d-%H%M%S)"
  archive="${ARCHIVE_DIR}/${slug}--${stamp}.tar.gz"
  n=0
  while [ -e "$archive" ]; do
    n=$((n + 1))
    archive="${ARCHIVE_DIR}/${slug}--${stamp}-${n}.tar.gz"
  done
  tar -czf "$archive" -C "$WORK_DIR" "$slug"
  rm -rf "$dir"
  if [ -L "${AI_DIR}/current" ]; then
    current_target="$(readlink "${AI_DIR}/current")"
    if [ "$current_target" = "work/${slug}" ]; then
      rm -f "${AI_DIR}/current"
    fi
  fi
  echo "ai-notes: archived ${slug} -> ${archive#"$ROOT"/}"
}

gc_orphans() {
  local slug branch
  [ -d "$WORK_DIR" ] || return 0
  mkdir -p "$ARCHIVE_DIR"
  shopt -s nullglob
  for dir in "${WORK_DIR}"/*; do
    [ -d "$dir" ] || continue
    slug="$(basename "$dir")"
    if is_protected_slug "$slug"; then
      continue
    fi
    branch="$(slug_to_branch "$slug")"
    if local_branch_exists "$branch"; then
      continue
    fi
    archive_and_remove "$slug"
  done
  shopt -u nullglob
}

assert_eq() {
  local label="$1" got="$2" want="$3"
  if [ "$got" != "$want" ]; then
    echo "FAIL $label: got='$got' want='$want'" >&2
    return 1
  fi
  echo "OK   $label"
}

run_self_test() {
  local fail=0
  local tmp gitdir
  tmp="$(mktemp -d)"
  # shellcheck disable=SC2064
  trap "rm -rf '$tmp'" EXIT

  # Encoding round-trips and collision freedom
  assert_eq "slash" "$(branch_to_slug 'feat/a')" "feat_a" || fail=1
  assert_eq "underscore" "$(branch_to_slug 'feat_a')" "feat%5Fa" || fail=1
  assert_eq "double_us" "$(branch_to_slug 'feat__a')" "feat%5F%5Fa" || fail=1
  assert_eq "mixed" "$(branch_to_slug 'a_/b%c')" "a%5F_b%25c" || fail=1
  assert_eq "rt_slash" "$(slug_to_branch "$(branch_to_slug 'feat/AUT-1')")" "feat/AUT-1" || fail=1
  assert_eq "rt_us" "$(slug_to_branch "$(branch_to_slug 'feat_x')")" "feat_x" || fail=1
  assert_eq "rt_both" "$(slug_to_branch "$(branch_to_slug 'feat_/x')")" "feat_/x" || fail=1

  if [ "$(branch_to_slug 'feature/a')" = "$(branch_to_slug 'feature_a')" ]; then
    echo "FAIL collision feature/a vs feature_a" >&2
    fail=1
  else
    echo "OK   no collision feature/a vs feature_a"
  fi

  # Isolated fake repo for ensure + gc
  gitdir="${tmp}/repo"
  mkdir -p "$gitdir"
  git -C "$gitdir" init -q -b develop
  git -C "$gitdir" config user.email "test@example.com"
  git -C "$gitdir" config user.name "test"
  echo x >"${gitdir}/f"
  git -C "$gitdir" add f
  git -C "$gitdir" commit -q -m init
  git -C "$gitdir" checkout -q -b 'feat/demo'
  mkdir -p "${gitdir}/scripts"
  cp "${ROOT}/scripts/ai-notes-gc.sh" "${gitdir}/scripts/ai-notes-gc.sh"
  chmod +x "${gitdir}/scripts/ai-notes-gc.sh"

  (
    cd "$gitdir"
    ./scripts/ai-notes-gc.sh --checkout
    [ -d .ai/work/feat_demo ] || { echo "FAIL ensure work dir" >&2; exit 1; }
    [ -L .ai/current ] || { echo "FAIL current symlink" >&2; exit 1; }
    mkdir -p .ai/work/orphan_gone
    echo note >.ai/work/orphan_gone/INDEX.md
    ln -sfn "work/orphan_gone" .ai/current
    mkdir -p .ai/work/develop
    echo keep >.ai/work/develop/INDEX.md
    ./scripts/ai-notes-gc.sh
    [ ! -d .ai/work/orphan_gone ] || { echo "FAIL orphan not archived" >&2; exit 1; }
    [ ! -e .ai/current ] || { echo "FAIL dangling current after orphan archive" >&2; exit 1; }
    [ -d .ai/work/develop ] || { echo "FAIL protected develop archived" >&2; exit 1; }
    [ -d .ai/work/feat_demo ] || { echo "FAIL live branch archived" >&2; exit 1; }
    ls .ai/archive/orphan_gone--*.tar.gz >/dev/null || { echo "FAIL missing archive" >&2; exit 1; }
    # unknown option
    if ./scripts/ai-notes-gc.sh --nope 2>/dev/null; then
      echo "FAIL unknown option should fail" >&2
      exit 1
    fi
    echo "OK   checkout/gc lifecycle in temp repo"
  ) || fail=1

  # Detached HEAD: ensure should no-op (no crash)
  (
    cd "$gitdir"
    rev="$(git rev-parse HEAD)"
    git checkout -q --detach "$rev"
    ./scripts/ai-notes-gc.sh --checkout
    echo "OK   detached HEAD ensure"
  ) || fail=1

  # post-checkout hook: file checkout (flag=0) vs branch checkout (flag=1)
  (
    cd "$gitdir"
    mkdir -p .githooks
    cp "${ROOT}/.githooks/post-checkout" .githooks/post-checkout
    chmod +x .githooks/post-checkout
    spy="${tmp}/hook-spy.log"
    rm -f "$spy"
    cat >scripts/ai-notes-gc.sh <<EOF
#!/usr/bin/env bash
echo "INVOKED:\$*" >>"$spy"
EOF
    chmod +x scripts/ai-notes-gc.sh
    ./.githooks/post-checkout HEAD HEAD 0
    if [ -f "$spy" ]; then
      echo "FAIL file checkout should not invoke ai-notes-gc" >&2
      exit 1
    fi
    ./.githooks/post-checkout HEAD HEAD 1
    grep -q 'INVOKED:--checkout' "$spy" || {
      echo "FAIL branch checkout should invoke --checkout" >&2
      exit 1
    }
    echo "OK   post-checkout hook paths"
  ) || fail=1

  if [ "$fail" -ne 0 ]; then
    echo "self-test FAILED" >&2
    exit 1
  fi
  echo "self-test PASSED"
}

case "$MODE" in
  --checkout)
    ensure_workspace
    gc_orphans
    ;;
  ""|--gc)
    gc_orphans
    ;;
  --self-test)
    run_self_test
    ;;
  -h|--help)
    sed -n '1,14p' "$0"
    exit 0
    ;;
  *)
    echo "Unknown option: $MODE" >&2
    echo "Usage: scripts/ai-notes-gc.sh [--checkout|--gc|--self-test]" >&2
    exit 1
    ;;
esac

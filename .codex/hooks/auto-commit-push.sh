#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${CODEX_AUTO_UPLOAD_DISABLED:-}" == "1" ]]; then
    exit 0
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    exit 0
fi

repo_root="$(git rev-parse --show-toplevel)"
cd "${repo_root}"

log_file="$(git rev-parse --git-path codex-auto-upload.log)"
lock_dir="$(git rev-parse --git-path codex-auto-upload.lock)"

mkdir -p "$(dirname "${log_file}")"
exec >> "${log_file}" 2>&1

log() {
    printf '%s %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "$*"
}

if ! mkdir "${lock_dir}" 2>/dev/null; then
    log "skip: another Codex auto upload hook is running"
    exit 0
fi

cleanup() {
    rmdir "${lock_dir}" 2>/dev/null || true
}

trap cleanup EXIT

branch="$(git branch --show-current)"

if [[ -z "${branch}" ]]; then
    log "skip: detached HEAD cannot be pushed safely"
    exit 0
fi

if [[ -e "$(git rev-parse --git-path MERGE_HEAD)" ]] \
    || [[ -d "$(git rev-parse --git-path rebase-merge)" ]] \
    || [[ -d "$(git rev-parse --git-path rebase-apply)" ]]; then
    log "skip: repository has an active merge or rebase"
    exit 0
fi

if [[ -z "$(git status --porcelain=v1)" ]]; then
    log "skip: working tree is clean"
    exit 0
fi

git add -A

if git diff --cached --quiet --exit-code; then
    log "skip: no staged changes after git add"
    exit 0
fi

timestamp="$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
commit_subject="${CODEX_AUTO_UPLOAD_MESSAGE:-Update from Codex hook}"

if ! git commit -m "${commit_subject}" -m "Automatically committed by the Codex Stop hook at ${timestamp}."; then
    log "skip: git commit failed"
    exit 0
fi

if git rev-parse --abbrev-ref --symbolic-full-name '@{u}' >/dev/null 2>&1; then
    git push || log "warning: git push failed"
elif git remote get-url origin >/dev/null 2>&1; then
    git push -u origin "${branch}" || log "warning: git push -u origin ${branch} failed"
else
    log "warning: commit created but push skipped because no git remote is configured"
fi

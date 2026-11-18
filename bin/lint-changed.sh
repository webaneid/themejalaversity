#!/usr/bin/env bash
# ==============================================================================
# Lint hanya file PHP yang berubah terhadap HEAD (atau BASE)
# ==============================================================================
set -euo pipefail

BASE_REF="${BASE:-HEAD}"
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMR "$BASE_REF" -- '*.php' | grep -vE '^(vendor|node_modules|dist|build)/' || true)

if [[ -z "$CHANGED_FILES" ]]; then
    echo "Tidak ada file PHP yang berubah."
    exit 0
fi

echo "Menjalankan PHPCS pada file yang berubah:"
echo "$CHANGED_FILES"
# shellcheck disable=SC2086
./vendor/bin/phpcs $CHANGED_FILES

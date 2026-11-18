#!/usr/bin/env bash
# ==============================================================================
# Verifikasi paket distribusi berdasarkan .distignore
# Memastikan tidak ada file dev/rahasia/docs/scss yang bocor ke paket produksi
# ==============================================================================
set -euo pipefail

TMP_DIR=$(mktemp -d)
trap 'rm -rf "$TMP_DIR"' EXIT

PACKAGE_DIR="$TMP_DIR/jalaversity"
mkdir -p "$PACKAGE_DIR"

rsync -a --exclude-from=.distignore ./ "$PACKAGE_DIR/"

echo "=== [Jalaversity Dist Package Verification] ==="

FORBIDDEN_PATHS=(
    ".git"
    ".github"
    ".claude"
    "docs"
    "bin"
    "scss"
    "vendor"
    "node_modules"
    "composer.json"
    "composer.lock"
    "package.json"
    "package-lock.json"
    "postcss.config.js"
    "tailwind.config.js"
    "phpcs.xml.dist"
    "phpstan.neon.dist"
    "phpstan-baseline.neon"
    "phpstan-bootstrap.php"
    "AGENTS.md"
    "CLAUDE.md"
    "README.md"
    "CHANGELOG.md"
)

ERRORS=0
for item in "${FORBIDDEN_PATHS[@]}"; do
    if [[ -e "$PACKAGE_DIR/$item" ]]; then
        echo "[FAIL] File/direktori terlarang ditemukan di paket rilis: $item"
        ERRORS=$((ERRORS + 1))
    fi
done

REQUIRED_PATHS=(
    "style.css"
    "functions.php"
    "index.php"
    "header.php"
    "footer.php"
    "single.php"
    "single-agenda.php"
    "single-pengumuman.php"
    "page.php"
    "archive.php"
    "search.php"
    "sidebar.php"
    "404.php"
    "css/front.css"
    "css/admin.css"
    "js/front/main.js"
    "js/admin/admin.js"
    "fonts/Gontor-Bold.otf"
    "includes/setup.php"
    "includes/security.php"
    "includes/enqueue.php"
    "includes/seo.php"
    "includes/tracking.php"
    "includes/updater.php"
)

for req in "${REQUIRED_PATHS[@]}"; do
    if [[ ! -e "$PACKAGE_DIR/$req" ]]; then
        echo "[FAIL] File wajib tidak ditemukan di paket rilis: $req"
        ERRORS=$((ERRORS + 1))
    fi
done

if [[ $ERRORS -gt 0 ]]; then
    echo "Verifikasi distribusi GAGAL dengan $ERRORS error."
    exit 1
fi

FILE_COUNT=$(find "$PACKAGE_DIR" -type f | wc -l | tr -d ' ')
TOTAL_SIZE=$(du -sh "$PACKAGE_DIR" | cut -f1)
echo "[OK] Paket distribusi bersih! Total: $FILE_COUNT file ($TOTAL_SIZE)."

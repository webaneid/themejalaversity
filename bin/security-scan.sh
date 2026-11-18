#!/usr/bin/env bash
# ==============================================================================
# Jalaversity Security Scanner
# Mendeteksi kredensial bocor, fungsi PHP berbahaya, dan anti-pattern keamanan.
# ==============================================================================
set -euo pipefail

RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

echo "=== [Jalaversity Security Scan] ==="

SEARCH_DIRS=("functions.php" "includes" "page-templates" "template-parts" "js")

# 1. File executable/PHP mencurigakan di folder aset statis
echo -n "1. Memeriksa file mencurigakan di folder aset (css/, js/, fonts/, images/)... "
ASSET_PHP=$(find css js fonts images -type f \( -name "*.php" -o -name "*.phtml" -o -name "*.phar" -o -name "*.sh" \) 2>/dev/null || true)
if [[ -n "$ASSET_PHP" ]]; then
    echo -e "${RED}DITEMUKAN!${NC}"
    echo "$ASSET_PHP"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 2. Hardcoded Google API Keys / GitHub Tokens / AWS Keys
echo -n "2. Memeriksa kredensial & API key yang ter-hardcode... "
CRED_HITS=$(grep -rnE "(AIza[0-9A-Za-z_-]{35}|ghp_[0-9A-Za-z]{36}|github_pat_[0-9A-Za-z_]{82}|AKIA[0-9A-Z]{16})" "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$CRED_HITS" ]]; then
    echo -e "${RED}DITEMUKAN!${NC}"
    echo "$CRED_HITS"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 3. Fungsi eksekusi berbahaya di PHP (kecuali base64_encode SVG data-URI di image-helpers.php)
echo -n "3. Memeriksa fungsi PHP berbahaya (eval, exec, shell_exec, system, passthru, base64_decode)... "
DANGEROUS_PHP=$(grep -rnE "\b(eval|exec|shell_exec|system|passthru|proc_open|popen|base64_decode)\s*\(" "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$DANGEROUS_PHP" ]]; then
    echo -e "${RED}DITEMUKAN!${NC}"
    echo "$DANGEROUS_PHP"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 4. Penggunaan extract()
echo -n "4. Memeriksa penggunaan extract()... "
EXTRACT_HITS=$(grep -rnE "\bextract\s*\(" "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$EXTRACT_HITS" ]]; then
    echo -e "${YELLOW}PERINGATAN:${NC}"
    echo "$EXTRACT_HITS"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 5. Penonaktifan filter KSES (wp_filter_kses)
echo -n "5. Memeriksa penonaktifan filter KSES... "
KSES_HITS=$(grep -rnE "remove_filter\s*\([^)]*wp_filter_kses" "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$KSES_HITS" ]]; then
    echo -e "${YELLOW}PERINGATAN:${NC}"
    echo "$KSES_HITS"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 6. Superglobals langsung tanpa unslash/sanitasi
echo -n "6. Memeriksa penggunaan langsung \$_GET / \$_POST / \$_REQUEST / \$_FILES... "
SUPERGLOBALS=$(grep -rnE '\$_(GET|POST|REQUEST|FILES)\b' "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$SUPERGLOBALS" ]]; then
    echo -e "${YELLOW}CATATAN AUDIT (periksa nonce & sanitasi):${NC}"
    echo "$SUPERGLOBALS"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 7. Penggunaan query_posts()
echo -n "7. Memeriksa penggunaan query_posts()... "
QUERY_POSTS=$(grep -rnE "\bquery_posts\s*\(" "${SEARCH_DIRS[@]}" *.php 2>/dev/null || true)
if [[ -n "$QUERY_POSTS" ]]; then
    echo -e "${YELLOW}PERINGATAN:${NC}"
    echo "$QUERY_POSTS"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

# 8. Pemeriksaan ABSPATH di seluruh file PHP theme
echo -n "8. Memeriksa keberadaan ABSPATH guard di seluruh file PHP... "
MISSING_ABSPATH=()
while IFS= read -r -d '' file; do
    if ! grep -Eq "defined\s*\(\s*['\"]ABSPATH['\"]\s*\)" "$file"; then
        MISSING_ABSPATH+=("$file")
    fi
done < <(find . -maxdepth 3 -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./dist/*" -not -path "./build/*" -not -name "phpstan-bootstrap.php" -print0)

if [[ ${#MISSING_ABSPATH[@]} -gt 0 ]]; then
    echo -e "${YELLOW}PERINGATAN (${#MISSING_ABSPATH[@]} file belum memiliki ABSPATH guard):${NC}"
    printf '   - %s\n' "${MISSING_ABSPATH[@]}"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}OK${NC}"
fi

echo "----------------------------------------"
echo -e "Hasil Scan: ${RED}${ERRORS} Error${NC}, ${YELLOW}${WARNINGS} Peringatan${NC}"

if [[ "${STRICT_SECURITY:-0}" == "1" && $ERRORS -gt 0 ]]; then
    exit 1
fi

exit 0

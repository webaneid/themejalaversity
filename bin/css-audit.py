#!/usr/bin/env python3
"""
Audit & Health-check untuk CSS Jalaversity (PostCSS + SCSS + Tailwind v3).
Memeriksa:
1. Keberadaan dan ukuran file output produksi:
   - css/front.css (target gzip < 30 KB)
   - css/admin.css
2. Keseimbangan kurung kurawal '{' dan '}' di setiap file CSS output.
3. Tidak adanya konflik merge marker (<<<<<<<, =======, >>>>>>>).
4. Aturan kritis Tailwind v3: hanya scss/front/_components.scss yang boleh
   memiliki wrapper '@layer components' di dalam scss/front/.
"""

import gzip
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

CSS_TARGETS = [
    (ROOT / "css" / "front.css", 30 * 1024),  # Max 30 KB gzipped
    (ROOT / "css" / "admin.css", 20 * 1024),  # Max 20 KB gzipped
]


def check_css_file(path: Path, max_gzip_bytes: int) -> list[str]:
    errors: list[str] = []
    if not path.exists():
        errors.append(f"[MISSING] File CSS produksi tidak ditemukan: {path.relative_to(ROOT)}")
        return errors

    raw_bytes = path.read_bytes()
    content = raw_bytes.decode("utf-8", errors="replace")
    size = len(raw_bytes)
    if size == 0 or not content.strip():
        errors.append(f"[EMPTY] File CSS kosong: {path.relative_to(ROOT)}")
        return errors

    gzip_size = len(gzip.compress(raw_bytes))
    if gzip_size > max_gzip_bytes:
        errors.append(
            f"[BUDGET] Ukuran gzip {path.relative_to(ROOT)} ({gzip_size:,} B) "
            f"melebihi batas {max_gzip_bytes:,} B"
        )

    open_braces = content.count("{")
    close_braces = content.count("}")
    if open_braces != close_braces:
        errors.append(
            f"[SYNTAX] Kurung kurawal tidak seimbang di {path.relative_to(ROOT)}: "
            f"'{{'={open_braces} vs '}}'={close_braces}"
        )

    for marker in ("<<<<<<<", "=======", ">>>>>>>"):
        if marker in content:
            errors.append(
                f"[CONFLICT] Merge conflict marker '{marker}' ditemukan di {path.relative_to(ROOT)}"
            )

    print(
        f"[OK] {path.relative_to(ROOT)} "
        f"(raw: {size:,} B, gzip: {gzip_size:,} B, braces: {open_braces}/{close_braces})"
    )
    return errors


def check_scss_layer_rule() -> list[str]:
    errors: list[str] = []
    front_scss_dir = ROOT / "scss" / "front"
    if not front_scss_dir.exists():
        return errors

    layer_files: list[str] = []
    for scss_file in sorted(front_scss_dir.glob("*.scss")):
        text = scss_file.read_text(encoding="utf-8", errors="replace")
        # Hapus komentar blok /* ... */ dan komentar baris // ...
        cleaned = re.sub(r"/\*.*?\*/", "", text, flags=re.S)
        cleaned = re.sub(r"//.*", "", cleaned)
        if re.search(r"@layer\s+components\b", cleaned):
            layer_files.append(scss_file.name)

    for fname in layer_files:
        if fname != "_components.scss":
            errors.append(
                f"[LAYER] '{fname}' menggunakan '@layer components'. "
                "Di Tailwind v3, hanya '_components.scss' yang boleh memakai '@layer components' "
                "agar rule tidak di-drop diam-diam."
            )
    return errors


def main() -> int:
    print("=== [Jalaversity CSS Pipeline Check] ===")
    all_errors: list[str] = []

    for css_path, max_gzip in CSS_TARGETS:
        all_errors.extend(check_css_file(css_path, max_gzip))

    all_errors.extend(check_scss_layer_rule())

    if all_errors:
        print("\nKesalahan ditemukan:")
        for err in all_errors:
            print(f"  - {err}")
        return 1

    print("Semua file CSS produksi valid, seimbang, dan memenuhi budget ukuran.")
    return 0


if __name__ == "__main__":
    sys.exit(main())

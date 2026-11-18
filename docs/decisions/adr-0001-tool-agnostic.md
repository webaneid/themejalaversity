# ADR-0001: Aturan & Dokumentasi Tool-Agnostic (`AGENTS.md` Tunggal)

**Status:** Accepted
**Tanggal:** 2026-11-18

## Context
Tim Webane menggunakan beberapa AI coding assistant (Antigravity, Claude Code,
Codex, Cursor) sekaligus developer manusia. Sebelumnya `jalaversity` hanya
memiliki `CLAUDE.md` dan dokumen bernomor (`docs/00-project-brief.md` s/d
`docs/04-settings-schema.md`) yang berbeda struktur dari standar baku Webane
(`sekolahane`, `newsane`, `jalawarta`).

## Decision
1. **`AGENTS.md` adalah satu-satunya sumber aturan (Single Source of Truth)**
   untuk semua AI agent dan developer.
2. **`CLAUDE.md` hanya berisi `@AGENTS.md`** (import langsung).
3. Dokumen proses dan arsitektur disimpan di `docs/` (`SOP.md`, `conventions.md`,
   `PROGRESS.md`, `RETROFIT-GAPS.md`, `architecture/*`, `decisions/*`,
   `phases/*`, `lessons-learned.md`, `glossary.md`) dan dikecualikan dari ZIP
   rilis pelanggan melalui `.distignore`.

## Alternatif yang Dipertimbangkan
- Menyimpan instruksi terpisah di `CLAUDE.md` dan `AGENTS.md` — ditolak karena
  duplikasi pasti menyebabkan drift.
- Mempertahankan skema penomoran lama `docs/00-*.md` s/d `04-*.md` berdampingan
  dengan `docs/architecture/*` — ditolak karena menciptakan dua sumber kebenaran
  yang membingungkan.

## Konsekuensi
- Semua AI agent dan developer membaca aturan serta struktur dokumentasi yang
  100% konsisten di seluruh portofolio theme Webane (`sekolahane`, `newsane`,
  `jalawarta`, `jalaversity`).
- Paket rilis pelanggan tetap bersih karena `bin/check-dist.sh` memverifikasi
  tidak ada file `.md` atau folder `docs/` yang ikut terbundel.

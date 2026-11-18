# Architecture — Deployment, CI/CD & GitHub Auto-Updater

> Dokumentasi alur rilis otomatis dan pembaruan jarak jauh (*remote auto-update*)
> untuk seluruh situs kampus pelanggan yang menggunakan **Jalaversity**.

---

## 1. Alur Rilis Otomatis (CI/CD)

Repository GitHub: **`webaneid/themejalaversity`** (privat).

```text
Push / Merge ke branch main
  │
  ├─► .github/workflows/ci.yml (Quality Gates)
  │     ├── composer lint:syntax (PHP 8.2)
  │     ├── composer security:scan
  │     ├── composer analyse (PHPStan Level 5)
  │     ├── composer css:check (integritas & budget CSS)
  │     └── composer dist:check (uji paket rilis .distignore)
  │
  └─► .github/workflows/release.yml (Release Theme)
        ├── Jalankan Quality Gates + npm ci && npm run build
        ├── Tentukan versi rilis (semantic-release / tag vX.Y.Z)
        ├── Sinkronkan Version di style.css & JALAVERSITY_VERSION di functions.php
        ├── Buat paket bersih: rsync -a --exclude-from=.distignore ./ build/jalaversity/
        ├── Buat arsip: jalaversity.zip & jalaversity-{version}.zip
        └── Publikasikan GitHub Release beserta lampiran ZIP
```

---

## 2. Aturan Paket Distribusi Bersih (`.distignore`)

Hanya file yang dibutuhkan WordPress saat runtime yang boleh masuk ke ZIP pelanggan.
File/direktori berikut **wajib dikecualikan** melalui `.distignore` dan diverifikasi otomatis oleh `bin/check-dist.sh` (`composer dist:check`):
- Dotfiles & dotfolders (`.git`, `.github`, `.claude`, `.DS_Store`, `.releaserc.json`, dll.)
- Seluruh file dokumentasi Markdown (`*.md`, `docs/`)
- Source aset & skrip audit (`scss/`, `bin/`, `tests/`)
- Tooling & dependensi dev (`vendor/`, `node_modules/`, `composer.*`, `package*.json`, `postcss.config.js`, `tailwind.config.js`, `phpcs*`, `phpstan*`)
- File font `.otf` yang tidak terpakai (hanya `fonts/Gontor-Bold.otf` yang dipertahankan).

---

## 3. Mekanisme GitHub Auto-Updater (`includes/updater.php`)

- **Konstanta:**
  - `JALAVERSITY_UPDATER_REPO` = `'webaneid/themejalaversity'`
  - `JALAVERSITY_UPDATER_SLUG` = `'jalaversity'`
- **Sumber Token GitHub (Prioritas):**
  1. Konstanta `JALAVERSITY_UPDATER_TOKEN` di `wp-config.php` (jika didefinisikan).
  2. Opsi `github_token` di `jalaversity_options` (**Jalaversity Settings → Tab Update**, hanya dapat diakses oleh `manage_options`).
- **Caching & Diagnostik:**
  - Respons rilis dari `https://api.github.com/repos/webaneid/themejalaversity/releases/latest` disimpan di transient `jalaversity_github_release` selama 12 jam (atau 1 jam jika gagal).
  - Jika terjadi kegagalan (`401`, `403`, `404`, gagal jaringan, atau rilis belum memiliki aset `.zip`), alasan kegagalan disimpan di transient `jalaversity_github_error` tanpa pernah mencetak token, lalu ditampilkan di panel status Tab Update.
- **Force Check Aman:**
  - Tombol **Cek Update Sekarang** dilengkapi nonce `wp_nonce_url( ..., 'jalaversity_force_check' )`, diverifikasi dengan `check_admin_referer( 'jalaversity_force_check' )` + `current_user_can( 'update_themes' )`, menghapus transient lokal dan memanggil `wp_update_themes()` secara instan.

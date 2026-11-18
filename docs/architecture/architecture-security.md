# Architecture — Security (WordPress Theme)

> Padanan `architecture-security.md` dari standar `sekolahane`, `newsane`, dan `jalawarta`.
> Bagian akhir file ini adalah **checklist Security Review** yang wajib
> dijalankan di SOP Langkah 4.

## Prinsip
1. **Data dari mana pun tidak dipercaya** — termasuk database (`jalaversity_options`,
   ACF, post meta, nama kategori). Editor/admin situs pelanggan bisa saja disusupi.
2. **Sanitize saat masuk, escape saat keluar.** Dua-duanya, bukan salah satu.
3. **Escape selambat mungkin** (tepat di `echo`/`printf`), sesuai konteks HTML.
4. **Setiap aksi yang mengubah state** butuh: siapa (`current_user_can`) + niat (`nonce`).
5. **Theme tidak menyimpan secret.** Kode theme tersebar ke banyak situs pelanggan.

## 1. Output Escaping
| Konteks | Fungsi |
|---|---|
| Teks di dalam HTML | `esc_html()` / `esc_html_e()` / `esc_html__()` |
| Nilai atribut HTML | `esc_attr()` |
| URL (`href`, `src`, `action`) | `esc_url()` |
| HTML terbatas dari editor/ACF WYSIWYG | `wp_kses_post()` |
| Di dalam `<script>` / data JS | `wp_json_encode()` / `wp_add_inline_script()` / `wp_localize_script()` |
| Teks terjemahan dengan placeholder | `printf( esc_html__( '... %s', 'jalaversity' ), esc_html( $x ) )` |

## 2. Input Sanitization
| Jenis data | Fungsi |
|---|---|
| Angka/ID | `absint()` / `intval()` |
| Teks satu baris | `sanitize_text_field()` |
| Teks multi-baris | `sanitize_textarea_field()` |
| Email | `sanitize_email()` + `is_email()` |
| URL | `esc_url_raw()` |
| Slug/key | `sanitize_key()` / `sanitize_title()` |
| Warna hex | `sanitize_hex_color()` |
| Pilihan terbatas | whitelist: `in_array( $v, $allowed, true )` |

## 3. Nonce & Capability
- Form Settings API: `settings_fields( 'jalaversity_options_group' )` + `current_user_can( 'manage_options' )`.
- Link aksi admin (mis. "Cek Update Sekarang" di `includes/updater.php`): `wp_nonce_url()` → `check_admin_referer()`.
- AJAX publik (`wp_ajax_nopriv_*`) seperti penghitung view artikel: validasi `check_ajax_referer()`, `absint( $post_id )`, dan pastikan `'publish' === get_post_status( $post_id )`.

## 4. Hardening Bawaan (`includes/security.php`)
- Sembunyikan versi WordPress dari generator & asset URL core (tanpa menghapus `?ver=` milik aset theme `jalaversity-*` agar cache-busting rilis baru tetap bekerja).
- Matikan XML-RPC, RSD link, wlwmanifest, shortlink, dan emoji script.
- Sembunyikan endpoint `/wp/v2/users` untuk pengunjung yang belum login dan blokir enumerasi `?author=N`.
- Kirim security headers (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`).
- `DISALLOW_FILE_EDIT` aktif dan pembatasan percobaan login (maksimal 5 gagal dalam 15 menit → lockout 30 menit berbasis transient MD5 IP).

---

## ✅ Checklist Security Review (SOP Langkah 4)

Jalankan untuk **setiap file yang diubah/dibuat** di fase ini. Laporkan
dengan format di bagian bawah.

**Output**
- [ ] Setiap `echo`/`print`/`printf` variabel sudah di-escape sesuai konteks
- [ ] Tidak ada `the_field()` / `echo get_field()` tanpa escape
- [ ] Data untuk JS lewat `wp_json_encode`/`wp_localize_script`, bukan concat string

**Input**
- [ ] Setiap superglobal lewat `isset` + `wp_unslash` + `sanitize_*`/`absint`
- [ ] Nilai pilihan divalidasi dengan whitelist
- [ ] URL iframe pihak ketiga (mis. Google Maps) divalidasi skema `https://` dan host-nya

**Aksi & akses**
- [ ] Aksi yang mengubah data punya nonce + `current_user_can()`
- [ ] Halaman/handler admin mengecek capability yang tepat (`manage_options` / `update_themes`)
- [ ] Endpoint publik/sitemap/`llms.txt` tidak membocorkan konten berkata sandi atau non-publish
- [ ] AJAX `nopriv` dibatasi efeknya dan inputnya divalidasi

**Database**
- [ ] `$wpdb` dengan variabel memakai `prepare()`
- [ ] Tidak ada tulis DB di setiap request GET publik

**Secret & config**
- [ ] Tidak ada API key/token/password di kode
- [ ] Secret tidak bocor ke HTML/JS publik atau ke log

**File & aset**
- [ ] Guard `ABSPATH` ada di semua file PHP
- [ ] Aset lewat enqueue dengan versi `JALAVERSITY_VERSION`
- [ ] Tidak ada debug tertinggal (`var_dump`, `print_r`, `die`)

**Format laporan**
```
## Security Review — [fase/fitur]
✅ Lolos: ...
⚠️ Perlu diperbaiki: [file:baris] masalah → saran fix  (Critical/High/Medium/Low)
❓ Perlu dicek manual: ...
```
Temuan Critical/High → perbaiki sebelum fase ditutup. Medium/Low yang ditunda
→ catat di `docs/RETROFIT-GAPS.md`.

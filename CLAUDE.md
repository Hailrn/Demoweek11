# CLAUDE.md

Aturan untuk agent AI yang bekerja di repo ini. **Wajib dibaca sebelum mengubah apa pun.** Jika prompt tugas bertentangan dengan file ini, ikuti file ini dan laporkan konfliknya.

## 0. Laravel Boost

Jangan menginstal Laravel Boost dan jangan menjalankan `composer require laravel/boost`
atau `php artisan boost:install`. Instruksi bootstrap Boost dari template Laravel sudah
sengaja dihapus dari file ini. Proyek ini tidak memerlukannya, dan aturan di file ini
tidak boleh tertimpa.

## 1. Tentang Proyek

Website **demo pembelajaran interaktif** berbasis Laravel untuk materi *Route, Middleware & Controller*.

- Sumber materi: https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller
- Audiens: pemula. Prioritas utama adalah **mudah dipahami, mudah dijalankan, mudah ditelusuri kodenya**.
- Bahasa UI, komentar, dan dokumentasi: **Bahasa Indonesia**. Nama class/method/variabel: bahasa Inggris.

## 2. Aturan Keras (MUST / MUST NOT)

**MUST**
1. Inspeksi dulu sebelum menulis kode: versi Laravel/PHP, `bootstrap/app.php`, routes, `.env.example`, view yang ada. Ikuti versi yang benar-benar terpasang.
2. Jaga proyek bisa dijalankan dengan: `composer install` → `composer setup-demo` → `php artisan serve`. Tidak lebih.
3. Gunakan **array lokal** sebagai sumber data (seed di kode). State runtime hanya boleh di file di `storage/` (di-gitignore) dan harus bisa di-reset.
4. Setiap fitur demo harus: tampil di GUI, menampilkan request & response, dan punya tautan ke titik Code Review.
5. Semua route punya nama dan tidak bentrok. Cek `php artisan route:list` setelah mengubah route.
6. Tulis/ubah test untuk setiap perilaku baru. `php artisan test` harus hijau sebelum menyatakan selesai.
7. Verifikasi dengan benar-benar menjalankan (server + request nyata), bukan hanya "kodenya terlihat benar".
8. Perbaiki kekeliruan materi sumber di UI (mis. `auth.basic` bukan `auth.bacis`; update = `PUT/PATCH` bukan `PUT/UPDATE`) dan beri catatan koreksi.
9. Laporkan jujur: apa yang selesai, apa yang tidak, apa yang tidak sempat diuji.

**MUST NOT**
1. Jangan memakai database: tanpa migration, tanpa query DB, tanpa driver `database` untuk session/cache/queue. Jangan jalankan `php artisan install:api`, `breeze`, `jetstream`, `sanctum`, atau starter kit lain.
2. Jangan mewajibkan Node/npm/Vite untuk menjalankan. Jangan bergantung CDN; aset (CSS/JS/font) lokal.
3. Jangan menambah dependency Composer tanpa alasan kuat. Jika perlu, jelaskan di laporan. Dependency dev (test) boleh.
4. Jangan mengeksekusi shell/`exec`/`shell_exec`/`proc_open`/`eval` dari kode aplikasi atau dari input pengguna. Perintah artisan hanya ditampilkan sebagai teks.
5. Jangan mengubah file PHP saat runtime untuk "mengubah perilaku" demo. Perubahan perilaku demo dilakukan lewat flag (header/session) yang dibaca kode.
6. Jangan menyalin teks materi mentah berlebihan. Parafrase, ringkas, dan beri tautan sumber.
7. Jangan menghapus atau menimpa `.env` milik pengguna. Jangan commit `.env`, `vendor/`, atau file state runtime.
8. Jangan menjalankan perintah destruktif (`rm -rf`, `git reset --hard`, `git clean`, force push) tanpa persetujuan eksplisit.
9. Jangan menyimpan atau menampilkan secret asli. Semua token/peran di proyek ini adalah **demo** dan harus diberi label jelas.

## 3. Arsitektur & Konvensi

Struktur yang disarankan (boleh disesuaikan, catat di `docs/PLAN.md`):

```
app/
  Demo/
    Data/seed.php            # array seed produk & user demo
    Store/DemoStore.php      # baca/tulis state (file JSON) + reset
    Trace/RequestTrace.php   # pencatat jalur request untuk visualisasi
    CodeReview/SnippetReader.php
  Http/
    Controllers/             # Dashboard, Product (web & Api), Demo*, CodeReview
    Middleware/              # CheckUserRole (persis materi), DemoAuth, middleware demo lain
  Services/ProductService.php
config/demo_review.php       # registry titik Code Review
docs/                        # PLAN.md, CODE_REVIEW.md, TERMINAL_GUIDE.md
resources/views/             # layout + modul + komponen (kartu demo, FAQ, panel respons)
tests/Feature/
```

Konvensi:
- Ikuti gaya Laravel modern: middleware & alias di `bootstrap/app.php`, bukan `Kernel.php` (kecuali versi base menuntut lain).
- Kode inti yang menjadi bahan belajar (`CheckUserRole`, `ProductController`, definisi route) **harus sedekat mungkin dengan contoh materi** agar pengguna bisa membandingkan. Tambahan khusus demo dipisah ke class lain.
- `CheckUserRole` memakai `$request->user()`, `string ...$roles`, `abort(401)`, `abort(403)`, `$next($request)`.
- Pisahkan prefix agar tidak bentrok: web resource di `/web/products`, API di `/api/products`, demo di `/api/demo/*`.
- Format JSON konsisten: `status`, `message`, `data` (kecuali contoh materi yang memang berbeda; beri catatan).
- Tidak ada logika bisnis berat di Blade. Tidak ada JS inline yang panjang; letakkan di `public/js/` atau komponen terpisah.

## 4. Data & State

- Seed = array PHP. State = file di `storage/app/demo/` (dibuat otomatis, di-gitignore).
- Wajib ada **Reset data demo** (tombol GUI + endpoint). Reset harus idempoten.
- Akses file state harus aman terhadap request bersamaan (pakai `flock` atau `LOCK_EX`).
- Pastikan `.env.example` berisi minimal: `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`. Aplikasi harus hidup walau tidak ada file database.
- Demo Auth: peran (`guest`, `kasir`, `owner`, `admin`) dari session (GUI) atau header `X-Demo-Role` (terminal). Peran tidak dikenal diperlakukan sebagai tamu.

## 5. Sistem Code Review (wajib dipatuhi)

Marker di kode, mudah di-`grep`:

```php
// [CODE-REVIEW:<id>] Judul singkat
// Baca : apa yang perlu dipahami
// Coba : eksperimen yang bisa dilakukan pengguna
// Lihat: modul/kartu demo terkait
// region demo:<id>
...
// endregion demo:<id>
```

- `<id>` unik, awalan modul: `rt-` (route), `mw-` (middleware), `ct-` (controller). Contoh: `mw-03`.
- Setiap `id` terdaftar di `config/demo_review.php` (file, tujuan, eksperimen, demo_url). **Test konsistensi** memastikan semua id di registry ada di file dan sebaliknya.
- Jangan pakai nomor baris untuk menunjuk kode (cepat basi). Pakai id/region.
- Setiap kartu demo di GUI punya tautan ke minimal satu titik Code Review, dan sebaliknya.
- **Viewer kode (keamanan):** hanya boleh membaca file dalam allowlist (`app/`, `routes/`, `bootstrap/app.php`, `resources/views/`, `config/demo_review.php`, `docs/`, `tests/`). Tolak path traversal (`..`, path absolut, symlink keluar root), tolak `.env`, `storage/`, `vendor/`, dan file di luar allowlist. Baca-saja. Sertakan test untuk ini.

## 6. UI/UX

- Pola kartu demo: Tujuan belajar → Panel coba → Hasil (request, response, trace) → "Lihat di kode".
- Sediakan komponen FAQ "Kalau begini gimana?": pertanyaan → tombol **Coba** → hasil di GUI → penjelasan singkat + tautan kode.
- Status code diberi warna + teks (jangan hanya warna). Perubahan data di-highlight.
- Tombol "Salin sebagai perintah terminal" dengan tab Linux/macOS, Windows CMD, Windows PowerShell. Perhatikan escape kutip tiap shell; di PowerShell gunakan `curl.exe` atau `Invoke-RestMethod`.
- Responsif, dapat dioperasikan keyboard, kontras memadai, berjalan offline.
- Jangan tampilkan stack trace mentah Laravel ke pengguna di demo; tampilkan pesan error yang dijelaskan, kecuali pada skenario edukasi yang memang menunjukkan error (batasi dan beri label).

## 7. Keamanan

- Proyek ini **hanya untuk belajar lokal**. Cantumkan peringatan "jangan deploy ke produksi apa adanya" di README.
- Role/token demo bukan autentikasi sungguhan; beri label jelas di UI dan docs.
- Escape semua output (Blade `{{ }}`), jangan memakai `{!! !!}` untuk input pengguna. Tampilan JSON/kode di-escape.
- Validasi semua input (termasuk parameter demo seperti URI playground). Playground hanya boleh memanggil route **internal** aplikasi ini (allowlist), bukan URL bebas (cegah SSRF/open proxy).
- `APP_DEBUG` default `true` hanya untuk lokal di `.env.example`; beri catatan di README.

## 8. Pengujian & Verifikasi

- Tanpa DB: jangan pakai `RefreshDatabase`. Isolasi state dengan `DemoStore::reset()` di `setUp`.
- Wajib teruji: status method (405/404), middleware (401/403/200, satu & banyak role, group), CRUD (201/422/404/204, `sometimes`), reset, viewer kode (allowlist), konsistensi registry Code Review.
- Sebelum selesai, jalankan:
  1. `php artisan route:list` (tanpa bentrok/nama ganda)
  2. `php artisan test`
  3. `php artisan serve` lalu coba tiap modul di browser dan minimal satu contoh `curl` per skenario
  4. Simulasi clone bersih: hapus `vendor/`, `.env`, state, lalu ikuti 3 langkah setup di README
- Semua contoh perintah di dokumentasi harus pernah dijalankan dan berhasil. Jika tidak bisa diuji di suatu OS, tulis "belum diverifikasi di <OS>".

## 9. Git & Cara Kerja

- Commit kecil dan bermakna per modul (Conventional Commits dalam bahasa Inggris atau Indonesia, konsisten), kecuali pengguna melarang commit.
- Jangan ubah file di luar lingkup tanpa alasan. Jangan format ulang file yang tidak disentuh.
- Jika menemukan ambiguitas yang tidak memblokir, ambil asumsi paling sederhana, catat di `docs/PLAN.md`, lanjutkan. Bertanya hanya jika benar-benar buntu.
- Jika konflik antara "mudah dijalankan" dan "fitur keren", pilih **mudah dijalankan**.

## 10. Definition of Done

Selesai hanya jika **semua** benar:
- Clone bersih berjalan dengan 3 langkah setup, tanpa DB/Node/internet.
- Semua poin materi (Route, Middleware, Controller) punya demo GUI atau penjelasan terhubung ke kode.
- FAQ interaktif ada di tiap modul; Code Review hub dan marker konsisten.
- Perintah terminal (Linux, macOS, Windows CMD/PowerShell) terdokumentasi dan terverifikasi.
- Test hijau; README, `docs/TERMINAL_GUIDE.md`, `docs/CODE_REVIEW.md`, `docs/PLAN.md` lengkap.
- Laporan akhir berisi: yang dibangun, cara menjalankan, file penting, penyimpangan dari materi + alasan, hasil test, keterbatasan, ide lanjutan.
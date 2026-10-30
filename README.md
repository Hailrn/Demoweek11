# Demo Interaktif: Route, Middleware & Controller

Website pembelajaran Laravel untuk materi **Route, Middleware & Controller**. Semua konsep utama
bisa dicoba langsung dari browser: kirim request, lihat status dan header, baca trace lapisan
middleware, lalu buka potongan kode yang menjelaskannya.

Sumber materi: <https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller>

> **Peringatan:** proyek ini hanya untuk belajar di komputer lokal. Role, token, dan signed URL di
> sini adalah simulasi, bukan autentikasi produksi. Jangan deploy apa adanya dengan
> `APP_DEBUG=true`.

## Yang bisa dicoba

- **Modul A — Route:** GET/POST, OPTIONS/HEAD, `match`/`any`, urutan route, constraint parameter,
  method spoofing, CSRF, dan daftar route yang benar-benar terdaftar.
- **Modul B — Middleware:** alias (lengkap 11 alias bawaan), `CheckUserRole`, 401 vs 403, route group
  admin, urutan middleware, berhenti sebelum `$next`, middleware global, throttle, dan signed URL.
- **Modul C — Controller:** controller view/JSON, CRUD resource, validasi 422, route model binding,
  dependency injection, PUT/PATCH dengan `sometimes`, dan lima jenis response.
- **Latihan:** CRUD web, CRUD API dari GUI, FAQ interaktif, matriks role, dan trace request.
- **Code Review:** 24 titik review dengan marker dan region kode yang stabil.

Tidak ada database, migration, Node, npm, Vite, atau aset CDN. Data seed berupa array PHP dan state
runtime berupa file JSON.

## Cara menjalankan

### Prasyarat

- PHP **8.3+** (diuji dengan PHP 8.5.12)
- Composer
- Terminal

Database, Node, dan npm **tidak diperlukan**.

### Tiga langkah

Dari folder proyek:

```bash
composer install
composer setup-demo
php artisan serve
```

Buka <http://localhost:8000>.

`composer install` tetap memerlukan akses ke Packagist pada pemasangan pertama, kecuali paket sudah
tersedia di cache Composer. Setelah dependensi terpasang, aplikasi berjalan tanpa database dan tanpa
aset eksternal.

### Apa yang dilakukan `composer setup-demo`?

Perintah tersebut menjalankan `php artisan demo:setup`, yang akan:

1. membuat `.env` dari `.env.example` **hanya jika `.env` belum ada**;
2. mengubah hanya tiga nilai driver berikut agar tidak membutuhkan database:
   `SESSION_DRIVER=file`, `CACHE_STORE=file`, dan `QUEUE_CONNECTION=sync`;
3. membuat `APP_KEY` jika masih kosong;
4. membuat folder storage yang diperlukan; dan
5. mereset state ke 14 produk seed di `storage/app/demo/state.json`.

Isi `.env` lain tidak diubah. Jika Anda mengelola `.env` sendiri dan tidak ingin tiga driver diubah,
gunakan:

```bash
php artisan demo:setup --keep-env
```

Jika `.env` lama masih memakai driver `database`, aplikasi tidak akan berjalan tanpa database.
Jangan menyalin `.env` berisi kredensial asli ke repositori.

### Menjalankan test

```bash
php artisan test
```

Hasil verifikasi terakhir: **78 test lulus, 879 assertion**. Test menggunakan session/cache array
dan state terisolasi di `storage/framework/testing/`.

## Halaman penting

| Halaman | URL | Isi |
|---|---|---|
| Beranda | `/` | Peta materi dan ringkasan route |
| Modul Route | `/belajar/route` | Playground method, constraint, CSRF, resource route |
| Modul Middleware | `/belajar/middleware` | Matriks role, trace, throttle, signed URL |
| Modul Controller | `/belajar/controller` | CRUD API, validasi, binding, response |
| CRUD web | `/web/products` | Resource web dengan form dan redirect |
| Dashboard | `/dashboard` | Ringkasan view dan JSON |
| FAQ | `/faq` | Pertanyaan yang bisa langsung dicoba |
| Code Review | `/code-review` | Registry 24 titik review |
| Reset | tombol di topbar | Mengembalikan data seed |

Endpoint API utama berada di `/api/products`; endpoint demo berada di `/api/demo/*`; area admin demo
berada di `/api/admin/*`.

## Peran demo

Pilih peran di sidebar: `guest`, `kasir`, `owner`, atau `admin`.

- Untuk route **web**, peran dibaca dari session. Coba `/demo/session-admin` setelah mengganti peran.
- Untuk route **API dan terminal**, kirim header `X-Demo-Role: admin`.
- Token Bearer demo (`demo-admin`, `demo-owner`, `demo-kasir`) juga tersedia sebagai alternatif.
- Peran yang tidak dikenal diperlakukan sebagai `guest`.
- Tidak ada tabel user dan tidak ada autentikasi sungguhan.

Contoh:

```bash
curl -i -H "X-Demo-Role: admin" http://localhost:8000/api/admin/dashboard
curl -i -H "X-Demo-Role: kasir" http://localhost:8000/api/admin/reports
```

Detail per skenario ada di [docs/TERMINAL_GUIDE.md](docs/TERMINAL_GUIDE.md).

## Data dan reset

- Seed: `app/Demo/Data/seed.php` (14 produk, array lokal).
- State: `storage/app/demo/state.json` (di-gitignore).
- Akses file memakai `flock`; penulisan state memakai file sementara lalu rename atomik.
- Reset dari GUI: tombol **Reset data demo**.
- Reset dari terminal/API: `curl -X POST http://localhost:8000/api/demo/reset`.
- Reset bersifat idempoten dan mengembalikan jumlah produk ke 14.

Jika state rusak, tombol reset akan menggantinya dengan seed.

## Trace request

Setiap response membawa dua header bantu:

- `X-Demo-Global: aktif` dari middleware global;
- `X-Demo-Trace`: base64 JSON berisi urutan lapisan request.

Trace membantu melihat apakah request berhenti di middleware, controller, atau exception handler.
Panel hasil di GUI menerjemahkan header tersebut.

## Code Review

Marker di kode memakai format:

```php
// [CODE-REVIEW:mw-02] Judul singkat
// region demo:mw-02
...
// endregion demo:mw-02
```

Registry ada di `config/demo_review.php`. Viewer hanya membaca file dalam allowlist, menolak path
traversal/symlink keluar root, dan tidak menyediakan endpoint untuk path bebas. Rincian ada di
[docs/CODE_REVIEW.md](docs/CODE_REVIEW.md).

## Koreksi materi sumber

Beberapa kekeliruan pada materi sumber sengaja dikoreksi di UI:

- `auth.bacis` → **`auth.basic`**.
- Update resource memakai **`PUT`/`PATCH`**, bukan `PUT/UPDATE`; `UPDATE` bukan HTTP method.
- Laravel 13 dapat mengizinkan POST same-origin berdasarkan `Sec-Fetch-Site`. Agar konsep token CSRF
  tetap terlihat, route `/demo/csrf-test` memakai pemeriksa `StrictDemoCsrf` yang tetap mewajibkan
  token.
- Implicit route model binding tetap didemokan tanpa database melalui `Product` yang
  mengimplementasikan `UrlRoutable`.

## Struktur penting

```text
app/Demo/                         seed, store JSON, model, trace, snippet reader
app/Http/Controllers/             controller web, API, demo, Code Review
app/Http/Middleware/              CheckUserRole dan middleware simulasi
app/Services/ProductService.php   logika produk
bootstrap/app.php                 route registration, alias middleware, error envelope
config/demo_review.php            registry 24 titik Code Review
config/demo_faqs.php              skenario FAQ
docs/                             PLAN, CODE_REVIEW, TERMINAL_GUIDE
resources/views/                  halaman dan komponen Blade
public/css/app.css                CSS lokal
public/js/app.js                  perilaku demo di browser
routes/web.php                    route web
routes/api.php                    route API dan demo admin
tests/                            test fitur dan unit
```

## Dokumentasi

- [docs/PLAN.md](docs/PLAN.md) — rencana, keputusan, cakupan, dan verifikasi.
- [docs/CODE_REVIEW.md](docs/CODE_REVIEW.md) — cara kerja registry dan daftar titik review.
- [docs/TERMINAL_GUIDE.md](docs/TERMINAL_GUIDE.md) — contoh curl/Invoke-RestMethod per OS.

## Batasan yang diketahui

- Proyek ini bukan aplikasi produksi: tidak ada database, guard auth sungguhan, atau hardening
  deployment.
- Alur JavaScript di browser sudah diperiksa sintaksnya dan endpoint-nya diuji, tetapi belum diuji
  otomatis di browser sungguhan.
- Perintah Windows CMD/PowerShell didokumentasikan dengan hati-hati, tetapi verifikasi runtime
  dilakukan di Linux. Bagian yang belum diuji ditandai di terminal guide.
- `composer install` membutuhkan akses Packagist/cache pada pemasangan pertama.
- `APP_DEBUG=true` di `.env.example` hanya untuk local. Matikan debug dan jangan gunakan role/token
  demo di produksi.

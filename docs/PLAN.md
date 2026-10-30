# Rencana, Keputusan, dan Catatan Verifikasi

Terakhir diperbarui: 30 Oktober 2026.

## 1. Tujuan

Membangun demo pembelajaran interaktif untuk materi **Route, Middleware & Controller** agar pemula
bisa:

1. membaca konsep singkat;
2. mengirim request nyata dari GUI;
3. melihat status, header, body, dan trace;
4. membuka potongan kode yang menjelaskan hasilnya; dan
5. mengulang eksperimen lewat FAQ.

Sumber materi: <https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller>

## 2. Batasan yang Dipegang

- Tanpa database, migration, Eloquent, Node, npm, Vite, atau CDN.
- Setup utama: `composer install` → `composer setup-demo` → `php artisan serve`.
- Seed berupa array PHP; state runtime berupa JSON di `storage/`.
- Semua route punya nama dan tidak boleh bentrok.
- Kode inti `CheckUserRole` dibuat dekat dengan contoh materi.
- Demo role/token hanya simulasi dan diberi label jelas.
- Tidak ada eksekusi shell dari kode aplikasi atau input pengguna.
- Viewer kode hanya membaca allowlist dan menolak path traversal.
- Test wajib hijau sebelum pekerjaan dinyatakan selesai.

## 3. Lingkungan Acuan

| Komponen | Versi |
|---|---|
| PHP | 8.5.12 |
| Laravel | 13.39.0 |
| Composer | 2.10.3 |
| PHPUnit | 12.5.x |
| OS verifikasi | Linux |

`composer.json` mensyaratkan PHP `^8.3` dan Laravel `^13.17`.

## 4. Keputusan Arsitektur

### 4.1 Registrasi route API

Proyek awal belum memiliki `routes/api.php`. File tersebut dibuat dan didaftarkan melalui
`bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    ...
)
```

Grup API tidak memakai session, cookie, atau CSRF. Peran untuk API dibaca dari header
`X-Demo-Role`, Bearer token demo, atau fallback guest.

### 4.2 Middleware

- Alias `role` → `App\Http\Middleware\CheckUserRole`.
- Alias `auth` → `App\Http\Middleware\DemoAuthenticate` (karena tidak ada tabel user).
- Alias `demo.csrf` → `StrictDemoCsrf` untuk route web `/demo/csrf-test`.
- Middleware global: `RequestTraceMiddleware`, `DemoGlobalHeader`, `DemoGlobalRoleSwitch`.
- Middleware grup web/API: `ResolveDemoUser`.

`CheckUserRole` mempertahankan bentuk inti materi:

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
```

dengan `$request->user()`, pengecekan `in_array(..., true)`, `abort(401)`, `abort(403)`, dan
`$next($request)`. Penambahan trace dan flag `X-Demo-Stop-Before-Next` dipisahkan secara jelas
sebagai kebutuhan demo.

### 4.3 Product tanpa database

`App\Demo\Models\Product` bukan model Eloquent. Class ini mengimplementasikan `UrlRoutable` dan
mencari data lewat `DemoStore`, sehingga implicit binding `Product $product` tetap bekerja dan
menghasilkan 404 jika produk tidak ada.

Constructor memakai `array $attributes = []` karena Laravel membuat instance model lewat container
sebelum memanggil `resolveRouteBinding()`.

### 4.4 State

- `DemoStore` membaca/menulis `storage/app/demo/state.json`.
- Path bisa diganti lewat `DEMO_STATE_PATH` (dipakai test).
- Operasi baca memakai `LOCK_SH`; operasi tulis memakai `LOCK_EX`.
- Reset menulis ulang seed dan idempoten.
- GUI dan API sama-sama bisa mereset data.

### 4.5 Trace

`RequestTrace` adalah singleton per request. Middleware global memulai trace, middleware/controller
menambah entri, lalu response sukses diberi header `X-Demo-Trace` berisi base64 JSON. Response error
ditangani melalui callback `respond()` di exception handler.

### 4.6 Role dari session dan header

- Route web: session `demo_role` dari role switcher.
- Route API: header `X-Demo-Role` (utama) atau Bearer token demo.
- `/demo/session-admin` sengaja berada di grup web dengan middleware `auth` + `role:admin,owner`
  untuk membuktikan session benar-benar dipakai.

### 4.7 Keamanan viewer kode

`SnippetReader` hanya membaca:

- direktori `app/`, `routes/`, `resources/views/`, `docs/`, `tests/`;
- file `bootstrap/app.php` dan `config/demo_review.php`;
- ekstensi `.php`/`.md` di dalam allowlist.

Path traversal, path absolut, symlink keluar root, `.env`, `storage/`, dan `vendor/` ditolak.
Tidak ada route untuk membaca path bebas; endpoint hanya menerima id registry.

## 5. Cakupan Materi dan Demo

### Modul A — Route (6 titik review)

- Method GET/POST, 405 vs 404, dan `Allow`.
- OPTIONS (`Allow`) dan HEAD (body kosong, header tetap ada).
- `Route::match` vs `Route::any`.
- Urutan route statis vs dinamis.
- Parameter route, constraint `where`, parameter opsional.
- `Route::resource` web vs `apiResource`.
- Method spoofing dan CSRF.

### Modul B — Middleware (8 titik review)

- Alias middleware.
- `CheckUserRole`, parameter variadic, 401/403.
- Route group dengan prefix admin.
- Auth simulasi, urutan middleware, dan penghentian sebelum `$next`.
- Throttle dan signed URL bawaan Laravel.
- Katalog 11 alias middleware bawaan (fungsi, kapan dipakai, status demo) dengan koreksi `auth.basic`
  dan catatan `subscribed` (Spark).
- Middleware global dan efek role global pada endpoint publik.
- Perbandingan role session vs header.

### Modul C — Controller (10 titik review)

- Controller view dan JSON.
- Resource controller dan pagination.
- Validasi 422.
- Route model binding tanpa database.
- Dependency injection constructor/method.
- Lima jenis response.
- Parameter opsional.
- PUT/PATCH dan `sometimes`.
- `destroy` 204.
- Simulasi mass assignment dan `$fillable`.

Jumlah total: **24 titik review** (6 + 8 + 10), ditambah **21 skenario FAQ interaktif**
(6 route + 8 middleware + 7 controller).

## 6. Koreksi dan Penyimpangan dari Materi

1. **`auth.bacis` → `auth.basic`.** Nama middleware pada materi salah ketik. UI menampilkan koreksi.
2. **`PUT/UPDATE` → `PUT/PATCH`.** `UPDATE` bukan HTTP method. Tabel resource dan halaman produk
   memakai `PUT|PATCH`.
3. **CSRF Laravel 13.** Laravel 13 dapat mengizinkan POST same-origin berdasarkan `Sec-Fetch-Site`.
   Karena itu demo menambahkan `StrictDemoCsrf` pada satu route agar token CSRF tetap bisa
   didemonstrasikan secara eksplisit.
4. **Auth tanpa database.** Alias `auth` diarahkan ke `DemoAuthenticate`, bukan guard database.
   Ini penyimpangan yang disengaja agar proyek bisa dijalankan tanpa database.
5. **Route model binding tanpa Eloquent.** `Product` mengimplementasikan `UrlRoutable`; perilaku
   binding dan 404 dibuat mirip Eloquent.
6. **Role header adalah simulasi.** Header `X-Demo-Role` dan token demo tidak boleh dipakai sebagai
   autentikasi produksi.

## 7. Peta Halaman

| Halaman | Fungsi |
|---|---|
| `/` | Beranda dan ringkasan |
| `/belajar/route` | Modul A |
| `/belajar/middleware` | Modul B |
| `/belajar/controller` | Modul C |
| `/faq` | FAQ interaktif semua modul |
| `/code-review` | Registry titik review |
| `/code-review/{id}` | Viewer region kode |
| `/dashboard` | Contoh view + JSON |
| `/web/products` | CRUD web resource |
| `/api/products` | CRUD API |
| `/api/admin/*` | Demo role group |
| `/demo/session-admin` | Bukti role dari session |
| `/demo/csrf-test` | Demo CSRF ketat |
| `/demo/spoof` | Demo method spoofing |
| `/demo/signed` | Halaman signed URL valid |

## 8. Verifikasi yang Sudah Dilakukan

### 8.1 Test otomatis

```bash
php artisan test
```

Hasil: **78 test, 879 assertion, semuanya lulus**.

Cakupan utama:

- halaman dan tautan utama;
- route/method, 404/405, constraint, urutan route;
- middleware 401/403/200, satu dan banyak role, group, global role;
- CRUD API 201/422/404/204;
- web resource CRUD dan redirect;
- binding, pagination, `sometimes`, body kosong;
- CSRF, signed URL valid/tampered/expired, throttle 429;
- reset dan isolasi state;
- konsistensi registry Code Review dua arah;
- allowlist dan path traversal viewer;
- lima jenis response.

### 8.2 Route

`php artisan route:list` berhasil. Pemeriksaan nama/URI duplikat tidak menemukan konflik.

### 8.3 Live HTTP

Server dijalankan di Linux dengan driver file/session/cache dan diuji memakai `curl`:

- halaman utama, tiga modul, FAQ, Code Review, dashboard, web CRUD, API;
- GET/POST/DELETE dan status 404/405;
- OPTIONS mengembalikan header `Allow` dan HEAD mengembalikan body kosong;
- role matrix guest/kasir/owner/admin;
- session role vs header role;
- CSRF 419 tanpa token, 200 dengan token;
- signed URL valid 200, tampered 403, expired 403;
- throttle 6 request → 429;
- response view/JSON/redirect/download/204 dan inspector;
- binding 200/404, validasi 422, delete 204;
- reset state idempoten;
- header `X-Demo-Global` dan `X-Demo-Trace`.

### 8.4 Instalasi bersih

Disalin proyek ke direktori sementara tanpa `vendor/`, `.env`, dan state runtime. Lalu:

```bash
composer install
composer setup-demo
php artisan test
```

Berhasil. `composer install` tidak membuat `.env`; `setup-demo` membuat `.env`, mengisi `APP_KEY`,
mengatur driver file/sync, membuat folder storage, dan menulis 14 produk seed. Server hasil salinan
juga diuji lewat HTTP.

### 8.5 Perilaku `.env`

Diuji pada salinan sementara:

- `.env` dengan driver `database` diubah **hanya** pada tiga kunci driver;
- baris kustom di `.env` tetap ada;
- `APP_KEY` tidak berubah;
- `--keep-env` membiarkan driver apa adanya;
- state tetap direset ke 14 produk.

`.env` asli milik pengguna tidak ditimpa selama pengembangan.

### 8.6 Aset

- `php artisan view:cache` berhasil.
- `node --check public/js/app.js` berhasil (hanya pemeriksaan sintaks; Node tidak dibutuhkan runtime).
- JS/CSS disajikan dari `public/`, tanpa CDN.

## 9. Checklist Definition of Done

Mengacu ke kriteria selesai pada `PROMPT.md`:

- [x] Clone bersih → `composer install` → `composer setup-demo` → `php artisan serve` berjalan tanpa
  DB, Node, dan aset internet.
- [x] Semua poin materi A, B, C punya demo GUI atau penjelasan yang dikaitkan ke kode.
- [x] Setiap demo menampilkan request, response, dan trace bila relevan.
- [x] Tiap modul punya FAQ "kalau begini gimana?" yang bisa dicoba.
- [x] Setiap demo punya tautan "Lihat di kode" dan titik Code Review valid (24 titik).
- [x] Koreksi materi (`auth.basic`, `PUT/PATCH`, `subscribed`) tampil di UI.
- [x] Tombol "Salin sebagai perintah terminal" tersedia untuk tiga shell; contoh Linux terverifikasi,
  Windows ditandai belum diverifikasi.
- [x] Reset data demo berfungsi dari GUI dan API.
- [x] `php artisan test` hijau; `php artisan route:list` tanpa bentrok nama/URI.
- [x] `README.md`, `docs/TERMINAL_GUIDE.md`, `docs/CODE_REVIEW.md`, `docs/PLAN.md` lengkap.

## 10. Keterbatasan

- Alur JavaScript di browser belum diuji dengan browser otomatis/manual interaktif; endpoint yang
  dipanggil dan sintaks JS sudah diperiksa.
- Perintah Windows CMD/PowerShell belum dijalankan di Windows. Label ini juga dicantumkan di
  `docs/TERMINAL_GUIDE.md`.
- `composer install` memerlukan akses Packagist atau cache pada pemasangan pertama.
- Role/Bearer token demo bukan autentikasi nyata.
- `APP_DEBUG=true` hanya untuk local; proyek tidak siap produksi.
- Throttle memakai cache lokal; kuota dapat tersisa sampai satu menit atau sampai cache dibersihkan.

## 11. Ide Lanjutan

- Tambahkan browser test (Playwright) untuk alur panel coba, FAQ, dan matriks role.
- Tambahkan contoh middleware `can`/Policy sebagai lanjutan materi.
- Tambahkan mode “bandingkan dua request” untuk melihat trace berdampingan.
- Tambahkan ekspor/impor state demo selain reset.
- Tambahkan halaman ringkasan route per middleware.
- Verifikasi perintah Windows di mesin Windows.

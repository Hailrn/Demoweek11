# PROMPT: Demo Interaktif "Route, Middleware & Controller" (Laravel)

> Tempel isi file ini ke agent AI (Claude Code, Codex, Cursor, dll.) yang bekerja di dalam directory project Laravel.
> Pastikan `AGENTS.md` sudah ada di root project. **Baca `AGENTS.md` lebih dulu, aturannya mengikat.**

---

## 1. Peran & Tujuan

Kamu adalah engineer Laravel sekaligus pembuat materi belajar. Ubah project Laravel di directory ini menjadi **website demo pembelajaran interaktif** yang merupakan hasil praktik dari materi berikut:

**Sumber referensi (wajib jadi acuan utama):**
https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller

Langkah pertama: buka dan baca halaman itu sendiri jika kamu punya akses web. Jika tidak, pakai ringkasan di bagian 3. Jangan menambah konsep besar di luar materi, kecuali yang disebut sebagai "bonus" di bawah.

Pengguna target adalah mahasiswa/pemula yang baru belajar Laravel. Mereka harus bisa **mencoba, mengubah input, melihat hasilnya lewat GUI, lalu membuka kode yang tepat untuk memahami kenapa hasilnya begitu**.

---

## 2. Langkah Kerja (ikuti berurutan)

**Fase 0, Inspeksi (jangan menulis kode dulu)**
1. Baca `composer.json`, `bootstrap/app.php`, `routes/*`, `.env.example`, `config/session.php`, `config/cache.php`, `resources/views`, `package.json` (jika ada), `vite.config.*` (jika ada).
2. Catat: versi Laravel & PHP, apakah `routes/api.php` ada, apakah view memakai `@vite`, driver session/cache/queue default, apakah ada auth scaffolding/starter kit, dan test framework (Pest/PHPUnit).
3. Ikuti **versi Laravel yang sebenarnya terpasang**. Materi memakai gaya Laravel modern (`bootstrap/app.php` dengan `->withMiddleware(...)`). Jika versi base berbeda, sesuaikan dan beri catatan.

**Fase 1, Rencana**
Tulis rencana singkat di `docs/PLAN.md`: daftar modul, daftar route, struktur file, keputusan teknis, dan hal yang berbeda dari materi beserta alasannya. Lanjut ke implementasi tanpa menunggu persetujuan, kecuali ada hal yang benar-benar memblokir.

**Fase 2, Implementasi bertahap**
Kerjakan per modul (A Route → B Middleware → C Controller → Code Review hub). Setelah tiap modul: jalankan `php artisan route:list`, jalankan test, dan coba lewat `php artisan serve`.

**Fase 3, Dokumentasi & verifikasi akhir**
README, `docs/CODE_REVIEW.md`, `docs/TERMINAL_GUIDE.md`, checklist di bagian 9, lalu laporan akhir sesuai `AGENTS.md`.

---

## 3. Ringkasan Materi Sumber (acuan konten)

**A. Route**: method `GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD`; `Route::match(['get','post'], ...)`; `Route::any(...)` (gunakan seperlunya); tabel fungsi tiap method. Contoh URI: `/products`, `/products/{id}`, `/products/search`, `/webhook`.

**B. Middleware**: lapisan perantara sebelum/sesudah controller. Middleware bawaan (alias): `auth`, `auth.basic`, `auth.session`, `guest`, `can`, `verified`, `password.confirm`, `throttle`, `signed`, `cache.headers`, `precognitive`. Grup `web` vs `api`. Middleware global lewat `bootstrap/app.php` (`append()`/`prepend()`). Membuat middleware (`make:middleware CheckUserRole`), method `handle($request, Closure $next)`, `$next($request)` meneruskan, return sebelum `$next` menghentikan request. Alias kustom (`$middleware->alias(['role' => CheckUserRole::class])`). Parameter middleware `role:admin` dan `role:admin,owner`, diterima sebagai `string ...$roles`. `$request->user()` null → `abort(401)`, role tidak cocok → `abort(403)`. Middleware pada route group dengan `prefix('admin')`.

**C. Controller**: `make:controller`; controller biasa (`DashboardController`: `index` view + `summary` JSON); resource controller (`index, create, store, show, edit, update, destroy`) + `Route::resource`; API resource (`--api`, `Route::apiResource`, tanpa `create`/`edit`); parameter route `{id}`; parameter opsional `{category?}`; Route Model Binding `Product $product` (404 otomatis jika tidak ada); `$request->validate()` dengan aturan `required|string|max:255` dan `numeric|min:0`, gagal → 422 untuk JSON; Dependency Injection (constructor: `ProductService`, method: `Request $request` + `$request->query('q')`); jenis response (view, json, redirect, download, noContent 204); CRUD `ProductController` (paginate 10, store 201, update dengan `sometimes`, destroy 204).

### Koreksi materi yang harus kamu tangani
Sumber punya beberapa kekeliruan. **Jangan salin mentah**. Tampilkan versi yang benar dan sediakan kotak kecil "Catatan koreksi materi" di halaman terkait:
- Alias `auth.bacis` → yang benar `auth.basic`.
- Tabel resource: `update()` dipetakan ke `PUT/UPDATE` → yang benar `PUT/PATCH` (tidak ada HTTP method UPDATE).
- Materi menaruh `Route::get('/products/{id}')` dan `{product}` (binding) serta `Route::resource('products')` dan `apiResource('products')` di URI yang sama. Di project ini pisahkan prefix agar tidak bentrok (lihat bagian 5).
- Penomoran subbab di materi tidak konsisten (ada "2. Middleware Bawaan" tanpa nomor 1) dan ada paragraf ganda. Abaikan, cukup rapikan.
- Materi menyebut `subscribed` bukan middleware bawaan universal (milik Laravel Spark). Cukup masukkan sebagai catatan.

---

## 4. Batasan Teknis (WAJIB)

1. **Tanpa database.** Tidak ada migration, tidak ada Eloquent query ke DB, tidak ada koneksi DB. Di `.env.example` set `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`. Pastikan tidak ada bagian aplikasi yang diam-diam memakai DB (default session/cache Laravel baru sering `database`). Pastikan juga `php artisan serve` jalan walau file `database.sqlite` tidak ada.
2. **Data = array lokal.** Data seed (produk, user demo) berupa array PHP di satu tempat (misal `app/Demo/Data/seed.php`). State hasil perubahan (CRUD) boleh disimpan di file runtime (misal `storage/app/demo/state.json`, di-gitignore) agar GUI **dan** curl melihat data yang sama. **Wajib ada tombol & endpoint "Reset data demo"** yang mengembalikan ke seed. Jika file state tidak ada, otomatis dibuat dari seed.
3. **Tanpa Node/npm untuk menjalankan.** Pengguna cukup PHP + Composer. Jika base memakai `@vite`, buang ketergantungannya pada view demo atau commit hasil build. Pakai CSS biasa atau CSS yang sudah ter-commit, dan JS vanilla atau library yang di-*vendor* lokal di `public/vendor/`. **Tidak boleh bergantung CDN** supaya jalan offline.
4. **Setup ≤ 4 perintah** dan harus sama di Windows/Linux/macOS:
   ```
   git clone <repo> && cd <repo>
   composer install
   composer setup-demo     # buat .env dari .env.example, key:generate, buat folder storage yang perlu
   php artisan serve
   ```
   Buat script `setup-demo` di `composer.json` (cross-platform, jangan pakai perintah shell yang hanya ada di satu OS). Cek `.env` sudah ada agar tidak menimpa.
5. **Autentikasi simulasi.** Tidak ada tabel users. Buat "Demo Auth": pengguna memilih peran lewat **Role Switcher** di GUI (tamu, kasir, owner, admin) yang disimpan di session. Untuk API/terminal, peran dikirim lewat header `X-Demo-Role: admin` (atau `Authorization: Bearer demo-admin`, pilih salah satu dan dokumentasikan). Sebuah middleware demo memasang user resolver (`$request->setUserResolver(...)`) sehingga **`CheckUserRole` boleh persis seperti di materi** (memakai `$request->user()` dan `$user->role`). Objek user berupa class/array sederhana, bukan model DB.
6. **Jangan mengeksekusi perintah shell dari GUI.** Perintah artisan hanya **ditampilkan** sebagai teks yang bisa disalin. Daftar route dibaca lewat `Route::getRoutes()`.
7. Ikuti `AGENTS.md` untuk keamanan viewer kode (allowlist path).

---

## 5. Peta Route yang Disarankan

Boleh disesuaikan, tapi **tidak boleh ada bentrok URI** dan tiap route harus punya nama.

| Area | URI | Tujuan |
|---|---|---|
| Web (GUI) | `/` | Beranda + peta belajar + status peran saat ini |
| Web | `/belajar/route` | Modul A |
| Web | `/belajar/middleware` | Modul B |
| Web | `/belajar/controller` | Modul C |
| Web | `/code-review` | Hub Code Review |
| Web | `/faq` | Kumpulan FAQ "kalau begini gimana?" (juga tertanam di tiap modul) |
| Web | `/reset-demo` (POST) | Reset data demo |
| API (`routes/api.php`, prefix `/api`) | `/api/demo/*` | Endpoint praktik Modul A (method playground, match, any, head, options) |
| API | `/api/products` (`apiResource`) | CRUD API + Route Model Binding + validasi + DI |
| API | `/api/demo/products-by-id/{id}` | Contoh parameter `{id}` (string) tanpa binding |
| API | `/api/demo/products-by-category/{category?}` | Parameter opsional |
| API | `/api/admin/*` | Grup middleware `auth`/`role:admin,owner` |
| Web | `/web/products` (`Route::resource`) | Resource controller versi web (view/redirect) |
| Web | `/dashboard`, `/dashboard/summary` | Controller biasa |

Catatan: `routes/api.php` mungkin belum ada di Laravel modern. Daftarkan manual di `bootstrap/app.php` (`api: __DIR__.'/../routes/api.php'`). **Jangan menjalankan `install:api`** karena itu menambah Sanctum dan migration.

---

## 6. Fitur per Modul

Setiap demo mengikuti pola **UI standar** (bagian 7). Semua hasil harus tampil di GUI: method, URL, status code, header penting, body, dan "jalur" yang dilalui request.

### Modul A, Route
1. **HTTP Method Playground**: pilih method (GET/POST/PUT/PATCH/DELETE/OPTIONS/HEAD), URI, body opsional → kirim dengan `fetch` → tampilkan request & response. Untuk HEAD tampilkan jelas bahwa **body kosong tapi header ada**. Untuk OPTIONS tampilkan header `Allow`.
2. **Tabel method** (dari materi) yang interaktif: klik baris → playground terisi otomatis.
3. **Route::match() & Route::any()**: demo `/api/demo/products/search` (GET & POST) dan `/api/demo/webhook` (any), plus tombol mencoba semua method. Tampilkan peringatan kenapa `any()` perlu dibatasi.
4. **PUT vs PATCH**: tampilan berdampingan. Kirim data lengkap vs sebagian dan lihat hasil. Karena materi memetakan keduanya ke `update()` dengan aturan `sometimes`, jelaskan itu, lalu sediakan demo tambahan opsional yang membedakan "PUT = ganti seluruhnya" dari "PATCH = sebagian".
5. **Penjelajah Route** (route list di GUI): tabel method, URI, nama, action, middleware. Bisa difilter. Sumber: `Route::getRoutes()`.
6. **FAQ interaktif** (tiap item punya tombol "Coba sekarang" yang menjalankan skenario di GUI):
   - Kalau method salah (POST ke route GET)? → 405 + header `Allow`.
   - Kalau URI tidak ada? → 404.
   - Kalau `{id}` diisi `abc`? Bandingkan tanpa dan dengan `->where('id','[0-9]+')`.
   - Kalau dua route mirip, mana yang menang? Tunjukkan urutan pendaftaran.
   - Kalau form HTML cuma bisa GET/POST, bagaimana kirim PUT/DELETE? → method spoofing `_method` (tampilkan di form demo) — jelaskan sebagai tambahan singkat.
   - Kenapa POST via browser kena 419 tapi via curl tidak? → CSRF (grup `web`) vs `api`. Tunjukkan di demo.

### Modul B, Middleware
1. **Visualisasi alur request (inti modul ini)**: diagram horizontal/lapis "bawang": `Request → Global → Group (web/api) → Route middleware → Controller → Response`. Saat request dikirim, tiap lapis **menyala berurutan** dengan status ✔ lolos / ✖ berhenti (beserta alasannya dan kode status). Tampilkan juga fase **sebelum** dan **sesudah** `$next` (response melewati lapisan yang sama dengan arah sebaliknya).
   - Implementasi: sebuah `RequestTrace` (singleton per request) yang dicatat oleh middleware & controller, dikembalikan lewat header (misal `X-Demo-Trace`, JSON ringkas ter-encode) agar body response tetap sesuai contoh materi. Dokumentasikan format header ini.
2. **Role Switcher + Endpoint Tester**: ganti peran (tamu/kasir/owner/admin), lalu uji `/api/admin/dashboard` (`role:admin`), `/api/admin/reports` (`role:admin,owner`), dan route group `prefix('admin')` dengan `['auth','role:admin,owner']`. Tampilkan **matriks akses** (peran × endpoint) yang terisi otomatis dari hasil nyata, bukan hardcode.
3. **Middleware Parameters**: tampilkan `role:admin` vs `role:admin,owner`, dan tunjukkan isi `$roles` yang diterima `handle()` (array hasil parse) — misal lewat trace.
4. **401 vs 403**: tamu → 401, peran salah → 403. Jelaskan bedanya di UI.
5. **Playground "bagaimana kalau"** (tombol toggle yang mengubah perilaku middleware demo, ditampilkan di GUI, tanpa edit file): 
   - Kalau middleware **tidak memanggil `$next`**? Request berhenti, controller tidak jalan.
   - Kalau urutan middleware ditukar (`auth` dulu atau `role` dulu)? Lihat perbedaan hasil tamu.
   - Kalau alias belum didaftarkan? Tampilkan error yang muncul (`Target class [...] does not exist`) lewat endpoint demo yang aman dan terkontrol.
   - Kalau `role` dipasang global? Endpoint publik ikut terblokir (peringatan dari materi).
   Implementasi toggle: kontrol via header demo / session flag yang dibaca middleware khusus demo. **Dilarang** mengubah file PHP saat runtime.
6. **Middleware bawaan**: katalog dari materi (11 alias) berisi fungsi, kapan dipakai, dan koreksi `auth.basic`. Demo hidup untuk yang tidak butuh DB/login asli: **`throttle`** (misal `throttle:5,1`, tombol "kirim 6x" → tampil 429 + `Retry-After`, tampil counter) dan **`signed`** (generate URL bertanda tangan → buka → ubah 1 karakter → 403). Sisanya (`auth.session`, `verified`, `password.confirm`, `can`, `precognitive`, dll.) cukup penjelasan + contoh kode, ditandai "tidak didemokan karena butuh database/starter kit".
7. **Global vs Group `web`/`api`**: demo yang menunjukkan perbedaan (session/cookie/CSRF di `web`, tidak ada di `api`) dan middleware global (misal middleware demo yang menambah header `X-Demo-Global: aktif`) ditampilkan di GUI.

### Modul C, Controller
1. **Controller biasa**: `DashboardController@index` (view) dan `@summary` (JSON `total_products` diambil dari jumlah data nyata).
2. **Resource vs API Resource**: toggle yang menampilkan tabel route hasil `Route::resource` (7 route) vs `Route::apiResource` (5 route), dengan baris `create`/`edit` ditandai "tidak ada di API". Tabel dibuat dari route yang benar-benar terdaftar. Tampilkan koreksi `PUT/PATCH`.
3. **CRUD GUI penuh** untuk produk (array lokal): daftar dengan **pagination (10/halaman)**, tambah, lihat detail, ubah (PUT dan PATCH), hapus. Tiap aksi menampilkan response mentahnya (status code, body) di panel samping. Respons: store → 201, destroy → 204 (jelaskan "kenapa body kosong").
4. **Route Model Binding**: input ID → hasil. ID tidak ada → 404 otomatis. Bandingkan berdampingan dengan versi `string $id` manual. Karena tidak ada Eloquent, implementasikan binding tanpa DB (misal class `Product` non-Eloquent yang mengimplementasikan `UrlRoutable` atau `Route::bind('product', fn)` yang membaca array) dan **jelaskan di UI/komentar bahwa di project dengan database ini otomatis lewat Eloquent**.
5. **Parameter**: `{id}`, opsional `{category?}` (`/products` dan `/products/electronics`).
6. **Validasi**: form dengan aturan dari materi. Tampilkan pesan error per field (422) dan JSON mentahnya. Sediakan **contoh input cepat** ("nama kosong", "harga negatif", "harga huruf", "nama 300 karakter") yang sekali klik mengisi form. Jelaskan perbedaan respons untuk request JSON (422) vs form web (redirect back + error session). Tunjukkan `sometimes` pada update: field tidak dikirim → tidak divalidasi.
7. **Dependency Injection**: panel yang menunjukkan `ProductService` disuntik lewat constructor dan `Request` lewat method (`/api/demo/search?q=...`). Tampilkan di trace/panel "objek apa yang diinjeksi ke method ini" (nama class).
8. **Jenis response**: lima tombol (view, JSON, redirect, download, noContent) yang masing-masing menampilkan status, header (`Content-Type`, `Location`, `Content-Disposition`), dan body/ukuran.
9. **FAQ interaktif**: 
   - Kalau `update` kirim field kosong dengan `sometimes`? 
   - Kalau `required` dipakai pada update, bedanya apa? 
   - Kalau `$fillable` tidak diisi (penjelasan + demo simulasi "mass assignment" dengan array: field tak diizinkan diabaikan).
   - Kalau parameter route dan dependency diinjeksi urutannya ditukar? 
   - Kalau controller mengembalikan array biasa? (Laravel otomatis jadi JSON)
   - Kalau format JSON tiap endpoint tidak konsisten? (tunjukkan contoh envelope `status/message/data`)

### Code Review Hub (`/code-review`)
Lihat bagian 8.

---

## 7. Pola UI Standar (semua demo)

Setiap kartu demo punya empat bagian:
1. **Tujuan belajar** satu kalimat.
2. **Panel coba** (GUI): kontrol input, tombol kirim, **tombol reset skenario**.
3. **Hasil**: Request yang dikirim, Response (status berwarna, header, body ter-format), dan **jalur request** (trace) bila relevan. Perubahan data harus terlihat jelas (highlight baris yang berubah).
4. **"Lihat di kode"**: tautan/komponen ke Code Review (bagian 8) untuk demo tersebut.

Tambahan UI:
- **Salin sebagai perintah terminal**: tiap request punya tombol dengan tab **Linux/macOS (bash)**, **Windows CMD**, **Windows PowerShell** yang menghasilkan perintah yang benar untuk request yang sedang dicoba (perhatikan perbedaan escape kutip antar shell; di PowerShell gunakan `curl.exe` atau `Invoke-RestMethod`, karena `curl` adalah alias di Windows PowerShell 5.1).
- Navigasi samping sesuai bab materi, ada indikator peran aktif, tombol "Reset data demo" selalu terlihat.
- Bahasa Indonesia, nada ramah, istilah teknis tetap dalam bahasa Inggris. Responsif, kontras baik, bisa dipakai keyboard.
- Tampilkan **"Kalau begini gimana?"** sebagai komponen FAQ yang bisa dipakai ulang (pertanyaan → tombol "Coba" → hasil di GUI → penjelasan singkat + tautan kode).

---

## 8. Sistem Code Review (di website DAN di kode)

Tujuannya agar pengguna tahu **persis bagian kode mana** yang harus dibuka dan eksperimen apa yang bisa dilakukan.

**Di kode (wajib):**
- Tandai bagian penting dengan marker yang bisa di-`grep`:
  ```php
  // [CODE-REVIEW:mw-03] CheckUserRole menerima parameter variadic
  // Baca : bagaimana string ...$roles menampung 'admin,owner'
  // Coba : ubah in_array(..., true) jadi false, lalu uji role 'ADMIN'
  // Lihat: Modul B → "Middleware Parameters"
  // region demo:mw-03
  ... kode ...
  // endregion demo:mw-03
  ```
  Di Blade gunakan `{{-- region demo:xxx --}}`. ID unik dengan awalan modul (`rt-`, `mw-`, `ct-`).
- Komentar dalam Bahasa Indonesia, singkat, menjelaskan **kenapa**, bukan mengulang kode.
- Perintah cepat untuk pengguna: `grep -rn "CODE-REVIEW" app routes bootstrap resources` (sediakan padanan Windows PowerShell: `Select-String -Path ... -Pattern "CODE-REVIEW" -Recurse`).

**Di website:**
- Registry satu sumber kebenaran: `config/demo_review.php` (array): `id`, `modul`, `judul`, `file`, `tujuan`, `yang_dilihat`, `eksperimen[]` (langkah: "ubah X → refresh → harapkan Y"), `demo_url`, `faq_terkait[]`.
- **Viewer kode** membaca file asli berdasarkan marker `region` (bukan salinan hardcode, supaya tidak basi). Tampilkan dengan penomoran baris, nama file lengkap, dan tombol "salin path". Taati allowlist path di `AGENTS.md` (anti path traversal).
- Halaman `/code-review`: peta modul → daftar titik review → buka titik: kode + tujuan + eksperimen + tombol "Coba di demo" (menuju kartu demo terkait).
- Di tiap kartu demo ada tautan balik ke titik review yang relevan.
- Buat juga `docs/CODE_REVIEW.md` berisi tabel ringkas ID → file → apa yang dipelajari.
- Tambahkan **"Urutan baca kode yang disarankan"** (misal: `bootstrap/app.php` → `routes/api.php` → `CheckUserRole` → `ProductController` → `ProductService`), karena itu alur yang paling dilewati request.

---

## 9. Pengujian & Dokumentasi

**Test (tanpa DB, tanpa `RefreshDatabase`)**: Feature test untuk minimal:
- status code tiap method dan 405/404 sesuai skenario;
- middleware: tamu 401, peran salah 403, peran benar 200, parameter satu dan banyak role, group;
- CRUD: store 201, validasi 422, show 404 (binding), update `sometimes`, destroy 204;
- reset data demo mengembalikan seed;
- viewer kode menolak path di luar allowlist;
- semua marker `CODE-REVIEW:*` di registry benar-benar ada di file (test konsistensi registry ↔ kode).
Jalankan: `php artisan test` (harus hijau).

**Dokumentasi:**
- `README.md`: apa ini, 4 langkah menjalankan, screenshot/deskripsi fitur, struktur folder, cara reset, troubleshooting singkat (port dipakai → `php artisan serve --port=8001`, ekstensi PHP yang dibutuhkan, folder `storage` tidak writable).
- `docs/TERMINAL_GUIDE.md`: cara menguji API lewat terminal untuk **Linux, macOS, Windows CMD, Windows PowerShell**, berisi contoh GET/POST/PUT/PATCH/DELETE/OPTIONS/HEAD, kirim header peran `X-Demo-Role`, skenario 401/403/422/404/405/429, cara melihat header (`-i`), dan catatan perbedaan escape kutip. Semua contoh harus sudah kamu verifikasi jalan.
- `docs/CODE_REVIEW.md` dan `docs/PLAN.md` (lihat di atas).

---

## 10. Kriteria Selesai (Definition of Done)

Centang semuanya sebelum melapor:
- [ ] Clone bersih → `composer install` → `composer setup-demo` → `php artisan serve` → semua halaman terbuka tanpa error, **tanpa DB, tanpa Node, tanpa internet** (untuk aset).
- [ ] Semua poin materi A, B, C punya demo GUI atau penjelasan yang dikaitkan ke kode.
- [ ] Setiap demo menampilkan request, response, dan (bila relevan) jalur trace.
- [ ] Tiap modul punya FAQ "kalau begini gimana?" yang bisa dicoba di GUI.
- [ ] Setiap demo punya tautan "Lihat di kode" dan titik Code Review yang valid.
- [ ] Koreksi materi (`auth.basic`, `PUT/PATCH`, dll.) tampil di UI.
- [ ] Tombol "Salin sebagai perintah terminal" bekerja untuk 3 shell dan perintahnya terverifikasi.
- [ ] Reset data demo berfungsi dari GUI dan API.
- [ ] `php artisan test` hijau; `php artisan route:list` tanpa bentrok.
- [ ] `README.md`, `docs/TERMINAL_GUIDE.md`, `docs/CODE_REVIEW.md`, `docs/PLAN.md` lengkap.

## 11. Format Laporan Akhir

Ringkas, berisi: (1) apa yang dibangun per modul, (2) cara menjalankan, (3) daftar file penting, (4) hal yang menyimpang dari materi + alasan, (5) hasil test, (6) keterbatasan yang diketahui, (7) id# PROMPT: Demo Interaktif "Route, Middleware & Controller" (Laravel)

> Tempel isi file ini ke agent AI (Claude Code, Codex, Cursor, dll.) yang bekerja di dalam directory project Laravel.
> Pastikan `AGENTS.md` sudah ada di root project. **Baca `AGENTS.md` lebih dulu, aturannya mengikat.**

---

## 1. Peran & Tujuan

Kamu adalah engineer Laravel sekaligus pembuat materi belajar. Ubah project Laravel di directory ini menjadi **website demo pembelajaran interaktif** yang merupakan hasil praktik dari materi berikut:

**Sumber referensi (wajib jadi acuan utama):**
https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller

Langkah pertama: buka dan baca halaman itu sendiri jika kamu punya akses web. Jika tidak, pakai ringkasan di bagian 3. Jangan menambah konsep besar di luar materi, kecuali yang disebut sebagai "bonus" di bawah.

Pengguna target adalah mahasiswa/pemula yang baru belajar Laravel. Mereka harus bisa **mencoba, mengubah input, melihat hasilnya lewat GUI, lalu membuka kode yang tepat untuk memahami kenapa hasilnya begitu**.

---

## 2. Langkah Kerja (ikuti berurutan)

**Fase 0, Inspeksi (jangan menulis kode dulu)**
1. Baca `composer.json`, `bootstrap/app.php`, `routes/*`, `.env.example`, `config/session.php`, `config/cache.php`, `resources/views`, `package.json` (jika ada), `vite.config.*` (jika ada).
2. Catat: versi Laravel & PHP, apakah `routes/api.php` ada, apakah view memakai `@vite`, driver session/cache/queue default, apakah ada auth scaffolding/starter kit, dan test framework (Pest/PHPUnit).
3. Ikuti **versi Laravel yang sebenarnya terpasang**. Materi memakai gaya Laravel modern (`bootstrap/app.php` dengan `->withMiddleware(...)`). Jika versi base berbeda, sesuaikan dan beri catatan.

**Fase 1, Rencana**
Tulis rencana singkat di `docs/PLAN.md`: daftar modul, daftar route, struktur file, keputusan teknis, dan hal yang berbeda dari materi beserta alasannya. Lanjut ke implementasi tanpa menunggu persetujuan, kecuali ada hal yang benar-benar memblokir.

**Fase 2, Implementasi bertahap**
Kerjakan per modul (A Route → B Middleware → C Controller → Code Review hub). Setelah tiap modul: jalankan `php artisan route:list`, jalankan test, dan coba lewat `php artisan serve`.

**Fase 3, Dokumentasi & verifikasi akhir**
README, `docs/CODE_REVIEW.md`, `docs/TERMINAL_GUIDE.md`, checklist di bagian 9, lalu laporan akhir sesuai `AGENTS.md`.

---

## 3. Ringkasan Materi Sumber (acuan konten)

**A. Route**: method `GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD`; `Route::match(['get','post'], ...)`; `Route::any(...)` (gunakan seperlunya); tabel fungsi tiap method. Contoh URI: `/products`, `/products/{id}`, `/products/search`, `/webhook`.

**B. Middleware**: lapisan perantara sebelum/sesudah controller. Middleware bawaan (alias): `auth`, `auth.basic`, `auth.session`, `guest`, `can`, `verified`, `password.confirm`, `throttle`, `signed`, `cache.headers`, `precognitive`. Grup `web` vs `api`. Middleware global lewat `bootstrap/app.php` (`append()`/`prepend()`). Membuat middleware (`make:middleware CheckUserRole`), method `handle($request, Closure $next)`, `$next($request)` meneruskan, return sebelum `$next` menghentikan request. Alias kustom (`$middleware->alias(['role' => CheckUserRole::class])`). Parameter middleware `role:admin` dan `role:admin,owner`, diterima sebagai `string ...$roles`. `$request->user()` null → `abort(401)`, role tidak cocok → `abort(403)`. Middleware pada route group dengan `prefix('admin')`.

**C. Controller**: `make:controller`; controller biasa (`DashboardController`: `index` view + `summary` JSON); resource controller (`index, create, store, show, edit, update, destroy`) + `Route::resource`; API resource (`--api`, `Route::apiResource`, tanpa `create`/`edit`); parameter route `{id}`; parameter opsional `{category?}`; Route Model Binding `Product $product` (404 otomatis jika tidak ada); `$request->validate()` dengan aturan `required|string|max:255` dan `numeric|min:0`, gagal → 422 untuk JSON; Dependency Injection (constructor: `ProductService`, method: `Request $request` + `$request->query('q')`); jenis response (view, json, redirect, download, noContent 204); CRUD `ProductController` (paginate 10, store 201, update dengan `sometimes`, destroy 204).

### Koreksi materi yang harus kamu tangani
Sumber punya beberapa kekeliruan. **Jangan salin mentah**. Tampilkan versi yang benar dan sediakan kotak kecil "Catatan koreksi materi" di halaman terkait:
- Alias `auth.bacis` → yang benar `auth.basic`.
- Tabel resource: `update()` dipetakan ke `PUT/UPDATE` → yang benar `PUT/PATCH` (tidak ada HTTP method UPDATE).
- Materi menaruh `Route::get('/products/{id}')` dan `{product}` (binding) serta `Route::resource('products')` dan `apiResource('products')` di URI yang sama. Di project ini pisahkan prefix agar tidak bentrok (lihat bagian 5).
- Penomoran subbab di materi tidak konsisten (ada "2. Middleware Bawaan" tanpa nomor 1) dan ada paragraf ganda. Abaikan, cukup rapikan.
- Materi menyebut `subscribed` bukan middleware bawaan universal (milik Laravel Spark). Cukup masukkan sebagai catatan.

---

## 4. Batasan Teknis (WAJIB)

1. **Tanpa database.** Tidak ada migration, tidak ada Eloquent query ke DB, tidak ada koneksi DB. Di `.env.example` set `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`. Pastikan tidak ada bagian aplikasi yang diam-diam memakai DB (default session/cache Laravel baru sering `database`). Pastikan juga `php artisan serve` jalan walau file `database.sqlite` tidak ada.
2. **Data = array lokal.** Data seed (produk, user demo) berupa array PHP di satu tempat (misal `app/Demo/Data/seed.php`). State hasil perubahan (CRUD) boleh disimpan di file runtime (misal `storage/app/demo/state.json`, di-gitignore) agar GUI **dan** curl melihat data yang sama. **Wajib ada tombol & endpoint "Reset data demo"** yang mengembalikan ke seed. Jika file state tidak ada, otomatis dibuat dari seed.
3. **Tanpa Node/npm untuk menjalankan.** Pengguna cukup PHP + Composer. Jika base memakai `@vite`, buang ketergantungannya pada view demo atau commit hasil build. Pakai CSS biasa atau CSS yang sudah ter-commit, dan JS vanilla atau library yang di-*vendor* lokal di `public/vendor/`. **Tidak boleh bergantung CDN** supaya jalan offline.
4. **Setup ≤ 4 perintah** dan harus sama di Windows/Linux/macOS:
   ```
   git clone <repo> && cd <repo>
   composer install
   composer setup-demo     # buat .env dari .env.example, key:generate, buat folder storage yang perlu
   php artisan serve
   ```
   Buat script `setup-demo` di `composer.json` (cross-platform, jangan pakai perintah shell yang hanya ada di satu OS). Cek `.env` sudah ada agar tidak menimpa.
5. **Autentikasi simulasi.** Tidak ada tabel users. Buat "Demo Auth": pengguna memilih peran lewat **Role Switcher** di GUI (tamu, kasir, owner, admin) yang disimpan di session. Untuk API/terminal, peran dikirim lewat header `X-Demo-Role: admin` (atau `Authorization: Bearer demo-admin`, pilih salah satu dan dokumentasikan). Sebuah middleware demo memasang user resolver (`$request->setUserResolver(...)`) sehingga **`CheckUserRole` boleh persis seperti di materi** (memakai `$request->user()` dan `$user->role`). Objek user berupa class/array sederhana, bukan model DB.
6. **Jangan mengeksekusi perintah shell dari GUI.** Perintah artisan hanya **ditampilkan** sebagai teks yang bisa disalin. Daftar route dibaca lewat `Route::getRoutes()`.
7. Ikuti `AGENTS.md` untuk keamanan viewer kode (allowlist path).

---

## 5. Peta Route yang Disarankan

Boleh disesuaikan, tapi **tidak boleh ada bentrok URI** dan tiap route harus punya nama.

| Area | URI | Tujuan |
|---|---|---|
| Web (GUI) | `/` | Beranda + peta belajar + status peran saat ini |
| Web | `/belajar/route` | Modul A |
| Web | `/belajar/middleware` | Modul B |
| Web | `/belajar/controller` | Modul C |
| Web | `/code-review` | Hub Code Review |
| Web | `/faq` | Kumpulan FAQ "kalau begini gimana?" (juga tertanam di tiap modul) |
| Web | `/reset-demo` (POST) | Reset data demo |
| API (`routes/api.php`, prefix `/api`) | `/api/demo/*` | Endpoint praktik Modul A (method playground, match, any, head, options) |
| API | `/api/products` (`apiResource`) | CRUD API + Route Model Binding + validasi + DI |
| API | `/api/demo/products-by-id/{id}` | Contoh parameter `{id}` (string) tanpa binding |
| API | `/api/demo/products-by-category/{category?}` | Parameter opsional |
| API | `/api/admin/*` | Grup middleware `auth`/`role:admin,owner` |
| Web | `/web/products` (`Route::resource`) | Resource controller versi web (view/redirect) |
| Web | `/dashboard`, `/dashboard/summary` | Controller biasa |

Catatan: `routes/api.php` mungkin belum ada di Laravel modern. Daftarkan manual di `bootstrap/app.php` (`api: __DIR__.'/../routes/api.php'`). **Jangan menjalankan `install:api`** karena itu menambah Sanctum dan migration.

---

## 6. Fitur per Modul

Setiap demo mengikuti pola **UI standar** (bagian 7). Semua hasil harus tampil di GUI: method, URL, status code, header penting, body, dan "jalur" yang dilalui request.

### Modul A, Route
1. **HTTP Method Playground**: pilih method (GET/POST/PUT/PATCH/DELETE/OPTIONS/HEAD), URI, body opsional → kirim dengan `fetch` → tampilkan request & response. Untuk HEAD tampilkan jelas bahwa **body kosong tapi header ada**. Untuk OPTIONS tampilkan header `Allow`.
2. **Tabel method** (dari materi) yang interaktif: klik baris → playground terisi otomatis.
3. **Route::match() & Route::any()**: demo `/api/demo/products/search` (GET & POST) dan `/api/demo/webhook` (any), plus tombol mencoba semua method. Tampilkan peringatan kenapa `any()` perlu dibatasi.
4. **PUT vs PATCH**: tampilan berdampingan. Kirim data lengkap vs sebagian dan lihat hasil. Karena materi memetakan keduanya ke `update()` dengan aturan `sometimes`, jelaskan itu, lalu sediakan demo tambahan opsional yang membedakan "PUT = ganti seluruhnya" dari "PATCH = sebagian".
5. **Penjelajah Route** (route list di GUI): tabel method, URI, nama, action, middleware. Bisa difilter. Sumber: `Route::getRoutes()`.
6. **FAQ interaktif** (tiap item punya tombol "Coba sekarang" yang menjalankan skenario di GUI):
   - Kalau method salah (POST ke route GET)? → 405 + header `Allow`.
   - Kalau URI tidak ada? → 404.
   - Kalau `{id}` diisi `abc`? Bandingkan tanpa dan dengan `->where('id','[0-9]+')`.
   - Kalau dua route mirip, mana yang menang? Tunjukkan urutan pendaftaran.
   - Kalau form HTML cuma bisa GET/POST, bagaimana kirim PUT/DELETE? → method spoofing `_method` (tampilkan di form demo) — jelaskan sebagai tambahan singkat.
   - Kenapa POST via browser kena 419 tapi via curl tidak? → CSRF (grup `web`) vs `api`. Tunjukkan di demo.

### Modul B, Middleware
1. **Visualisasi alur request (inti modul ini)**: diagram horizontal/lapis "bawang": `Request → Global → Group (web/api) → Route middleware → Controller → Response`. Saat request dikirim, tiap lapis **menyala berurutan** dengan status ✔ lolos / ✖ berhenti (beserta alasannya dan kode status). Tampilkan juga fase **sebelum** dan **sesudah** `$next` (response melewati lapisan yang sama dengan arah sebaliknya).
   - Implementasi: sebuah `RequestTrace` (singleton per request) yang dicatat oleh middleware & controller, dikembalikan lewat header (misal `X-Demo-Trace`, JSON ringkas ter-encode) agar body response tetap sesuai contoh materi. Dokumentasikan format header ini.
2. **Role Switcher + Endpoint Tester**: ganti peran (tamu/kasir/owner/admin), lalu uji `/api/admin/dashboard` (`role:admin`), `/api/admin/reports` (`role:admin,owner`), dan route group `prefix('admin')` dengan `['auth','role:admin,owner']`. Tampilkan **matriks akses** (peran × endpoint) yang terisi otomatis dari hasil nyata, bukan hardcode.
3. **Middleware Parameters**: tampilkan `role:admin` vs `role:admin,owner`, dan tunjukkan isi `$roles` yang diterima `handle()` (array hasil parse) — misal lewat trace.
4. **401 vs 403**: tamu → 401, peran salah → 403. Jelaskan bedanya di UI.
5. **Playground "bagaimana kalau"** (tombol toggle yang mengubah perilaku middleware demo, ditampilkan di GUI, tanpa edit file): 
   - Kalau middleware **tidak memanggil `$next`**? Request berhenti, controller tidak jalan.
   - Kalau urutan middleware ditukar (`auth` dulu atau `role` dulu)? Lihat perbedaan hasil tamu.
   - Kalau alias belum didaftarkan? Tampilkan error yang muncul (`Target class [...] does not exist`) lewat endpoint demo yang aman dan terkontrol.
   - Kalau `role` dipasang global? Endpoint publik ikut terblokir (peringatan dari materi).
   Implementasi toggle: kontrol via header demo / session flag yang dibaca middleware khusus demo. **Dilarang** mengubah file PHP saat runtime.
6. **Middleware bawaan**: katalog dari materi (11 alias) berisi fungsi, kapan dipakai, dan koreksi `auth.basic`. Demo hidup untuk yang tidak butuh DB/login asli: **`throttle`** (misal `throttle:5,1`, tombol "kirim 6x" → tampil 429 + `Retry-After`, tampil counter) dan **`signed`** (generate URL bertanda tangan → buka → ubah 1 karakter → 403). Sisanya (`auth.session`, `verified`, `password.confirm`, `can`, `precognitive`, dll.) cukup penjelasan + contoh kode, ditandai "tidak didemokan karena butuh database/starter kit".
7. **Global vs Group `web`/`api`**: demo yang menunjukkan perbedaan (session/cookie/CSRF di `web`, tidak ada di `api`) dan middleware global (misal middleware demo yang menambah header `X-Demo-Global: aktif`) ditampilkan di GUI.

### Modul C, Controller
1. **Controller biasa**: `DashboardController@index` (view) dan `@summary` (JSON `total_products` diambil dari jumlah data nyata).
2. **Resource vs API Resource**: toggle yang menampilkan tabel route hasil `Route::resource` (7 route) vs `Route::apiResource` (5 route), dengan baris `create`/`edit` ditandai "tidak ada di API". Tabel dibuat dari route yang benar-benar terdaftar. Tampilkan koreksi `PUT/PATCH`.
3. **CRUD GUI penuh** untuk produk (array lokal): daftar dengan **pagination (10/halaman)**, tambah, lihat detail, ubah (PUT dan PATCH), hapus. Tiap aksi menampilkan response mentahnya (status code, body) di panel samping. Respons: store → 201, destroy → 204 (jelaskan "kenapa body kosong").
4. **Route Model Binding**: input ID → hasil. ID tidak ada → 404 otomatis. Bandingkan berdampingan dengan versi `string $id` manual. Karena tidak ada Eloquent, implementasikan binding tanpa DB (misal class `Product` non-Eloquent yang mengimplementasikan `UrlRoutable` atau `Route::bind('product', fn)` yang membaca array) dan **jelaskan di UI/komentar bahwa di project dengan database ini otomatis lewat Eloquent**.
5. **Parameter**: `{id}`, opsional `{category?}` (`/products` dan `/products/electronics`).
6. **Validasi**: form dengan aturan dari materi. Tampilkan pesan error per field (422) dan JSON mentahnya. Sediakan **contoh input cepat** ("nama kosong", "harga negatif", "harga huruf", "nama 300 karakter") yang sekali klik mengisi form. Jelaskan perbedaan respons untuk request JSON (422) vs form web (redirect back + error session). Tunjukkan `sometimes` pada update: field tidak dikirim → tidak divalidasi.
7. **Dependency Injection**: panel yang menunjukkan `ProductService` disuntik lewat constructor dan `Request` lewat method (`/api/demo/search?q=...`). Tampilkan di trace/panel "objek apa yang diinjeksi ke method ini" (nama class).
8. **Jenis response**: lima tombol (view, JSON, redirect, download, noContent) yang masing-masing menampilkan status, header (`Content-Type`, `Location`, `Content-Disposition`), dan body/ukuran.
9. **FAQ interaktif**: 
   - Kalau `update` kirim field kosong dengan `sometimes`? 
   - Kalau `required` dipakai pada update, bedanya apa? 
   - Kalau `$fillable` tidak diisi (penjelasan + demo simulasi "mass assignment" dengan array: field tak diizinkan diabaikan).
   - Kalau parameter route dan dependency diinjeksi urutannya ditukar? 
   - Kalau controller mengembalikan array biasa? (Laravel otomatis jadi JSON)
   - Kalau format JSON tiap endpoint tidak konsisten? (tunjukkan contoh envelope `status/message/data`)

### Code Review Hub (`/code-review`)
Lihat bagian 8.

---

## 7. Pola UI Standar (semua demo)

Setiap kartu demo punya empat bagian:
1. **Tujuan belajar** satu kalimat.
2. **Panel coba** (GUI): kontrol input, tombol kirim, **tombol reset skenario**.
3. **Hasil**: Request yang dikirim, Response (status berwarna, header, body ter-format), dan **jalur request** (trace) bila relevan. Perubahan data harus terlihat jelas (highlight baris yang berubah).
4. **"Lihat di kode"**: tautan/komponen ke Code Review (bagian 8) untuk demo tersebut.

Tambahan UI:
- **Salin sebagai perintah terminal**: tiap request punya tombol dengan tab **Linux/macOS (bash)**, **Windows CMD**, **Windows PowerShell** yang menghasilkan perintah yang benar untuk request yang sedang dicoba (perhatikan perbedaan escape kutip antar shell; di PowerShell gunakan `curl.exe` atau `Invoke-RestMethod`, karena `curl` adalah alias di Windows PowerShell 5.1).
- Navigasi samping sesuai bab materi, ada indikator peran aktif, tombol "Reset data demo" selalu terlihat.
- Bahasa Indonesia, nada ramah, istilah teknis tetap dalam bahasa Inggris. Responsif, kontras baik, bisa dipakai keyboard.
- Tampilkan **"Kalau begini gimana?"** sebagai komponen FAQ yang bisa dipakai ulang (pertanyaan → tombol "Coba" → hasil di GUI → penjelasan singkat + tautan kode).

---

## 8. Sistem Code Review (di website DAN di kode)

Tujuannya agar pengguna tahu **persis bagian kode mana** yang harus dibuka dan eksperimen apa yang bisa dilakukan.

**Di kode (wajib):**
- Tandai bagian penting dengan marker yang bisa di-`grep`:
  ```php
  // [CODE-REVIEW:mw-03] CheckUserRole menerima parameter variadic
  // Baca : bagaimana string ...$roles menampung 'admin,owner'
  // Coba : ubah in_array(..., true) jadi false, lalu uji role 'ADMIN'
  // Lihat: Modul B → "Middleware Parameters"
  // region demo:mw-03
  ... kode ...
  // endregion demo:mw-03
  ```
  Di Blade gunakan `{{-- region demo:xxx --}}`. ID unik dengan awalan modul (`rt-`, `mw-`, `ct-`).
- Komentar dalam Bahasa Indonesia, singkat, menjelaskan **kenapa**, bukan mengulang kode.
- Perintah cepat untuk pengguna: `grep -rn "CODE-REVIEW" app routes bootstrap resources` (sediakan padanan Windows PowerShell: `Select-String -Path ... -Pattern "CODE-REVIEW" -Recurse`).

**Di website:**
- Registry satu sumber kebenaran: `config/demo_review.php` (array): `id`, `modul`, `judul`, `file`, `tujuan`, `yang_dilihat`, `eksperimen[]` (langkah: "ubah X → refresh → harapkan Y"), `demo_url`, `faq_terkait[]`.
- **Viewer kode** membaca file asli berdasarkan marker `region` (bukan salinan hardcode, supaya tidak basi). Tampilkan dengan penomoran baris, nama file lengkap, dan tombol "salin path". Taati allowlist path di `AGENTS.md` (anti path traversal).
- Halaman `/code-review`: peta modul → daftar titik review → buka titik: kode + tujuan + eksperimen + tombol "Coba di demo" (menuju kartu demo terkait).
- Di tiap kartu demo ada tautan balik ke titik review yang relevan.
- Buat juga `docs/CODE_REVIEW.md` berisi tabel ringkas ID → file → apa yang dipelajari.
- Tambahkan **"Urutan baca kode yang disarankan"** (misal: `bootstrap/app.php` → `routes/api.php` → `CheckUserRole` → `ProductController` → `ProductService`), karena itu alur yang paling dilewati request.

---

## 9. Pengujian & Dokumentasi

**Test (tanpa DB, tanpa `RefreshDatabase`)**: Feature test untuk minimal:
- status code tiap method dan 405/404 sesuai skenario;
- middleware: tamu 401, peran salah 403, peran benar 200, parameter satu dan banyak role, group;
- CRUD: store 201, validasi 422, show 404 (binding), update `sometimes`, destroy 204;
- reset data demo mengembalikan seed;
- viewer kode menolak path di luar allowlist;
- semua marker `CODE-REVIEW:*` di registry benar-benar ada di file (test konsistensi registry ↔ kode).
Jalankan: `php artisan test` (harus hijau).

**Dokumentasi:**
- `README.md`: apa ini, 4 langkah menjalankan, screenshot/deskripsi fitur, struktur folder, cara reset, troubleshooting singkat (port dipakai → `php artisan serve --port=8001`, ekstensi PHP yang dibutuhkan, folder `storage` tidak writable).
- `docs/TERMINAL_GUIDE.md`: cara menguji API lewat terminal untuk **Linux, macOS, Windows CMD, Windows PowerShell**, berisi contoh GET/POST/PUT/PATCH/DELETE/OPTIONS/HEAD, kirim header peran `X-Demo-Role`, skenario 401/403/422/404/405/429, cara melihat header (`-i`), dan catatan perbedaan escape kutip. Semua contoh harus sudah kamu verifikasi jalan.
- `docs/CODE_REVIEW.md` dan `docs/PLAN.md` (lihat di atas).

---

## 10. Kriteria Selesai (Definition of Done)

Centang semuanya sebelum melapor:
- [ ] Clone bersih → `composer install` → `composer setup-demo` → `php artisan serve` → semua halaman terbuka tanpa error, **tanpa DB, tanpa Node, tanpa internet** (untuk aset).
- [ ] Semua poin materi A, B, C punya demo GUI atau penjelasan yang dikaitkan ke kode.
- [ ] Setiap demo menampilkan request, response, dan (bila relevan) jalur trace.
- [ ] Tiap modul punya FAQ "kalau begini gimana?" yang bisa dicoba di GUI.
- [ ] Setiap demo punya tautan "Lihat di kode" dan titik Code Review yang valid.
- [ ] Koreksi materi (`auth.basic`, `PUT/PATCH`, dll.) tampil di UI.
- [ ] Tombol "Salin sebagai perintah terminal" bekerja untuk 3 shell dan perintahnya terverifikasi.
- [ ] Reset data demo berfungsi dari GUI dan API.
- [ ] `php artisan test` hijau; `php artisan route:list` tanpa bentrok.
- [ ] `README.md`, `docs/TERMINAL_GUIDE.md`, `docs/CODE_REVIEW.md`, `docs/PLAN.md` lengkap.

## 11. Format Laporan Akhir

Ringkas, berisi: (1) apa yang dibangun per modul, (2) cara menjalankan, (3) daftar file penting, (4) hal yang menyimpang dari materi + alasan, (5) hasil test, (6) keterbatasan yang diketahui, (7) ide lanjutan.e lanjutan.
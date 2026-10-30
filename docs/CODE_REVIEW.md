# Code Review: Registry, Marker, dan Viewer

Halaman `/code-review` adalah peta dari konsep ke kode asli. Setiap titik review punya id stabil,
misalnya `mw-02`, dan menunjuk ke satu region di file tertentu. Nomor baris hanya ditampilkan sebagai
informasi; tautan tidak bergantung pada nomor baris.

## 1. Format marker

Marker dan region ditulis langsung di dekat kode:

```php
// [CODE-REVIEW:mw-02] CheckUserRole: parameter variadic dan $next
// Baca : parameter role diterima sebagai array.
// Coba : uji role admin, owner, kasir, dan guest.
// Lihat: Modul B -> "role:admin,owner menjadi array"
// region demo:mw-02
...
// endregion demo:mw-02
```

Aturan:

- id unik dengan awalan modul: `rt-` (route), `mw-` (middleware), `ct-` (controller);
- setiap id terdaftar tepat satu kali di `config/demo_review.php`;
- setiap id punya marker dan pasangan `region`/`endregion` di file yang sama;
- jangan memakai nomor baris sebagai target tautan;
- setiap `demo_url` harus menunjuk halaman yang bisa dibuka dan anchor yang benar-benar ada.

## 2. Registry

File: `config/demo_review.php`.

Field setiap titik:

| Field | Arti |
|---|---|
| `id` | id unik, mis. `mw-02` |
| `modul` | kelompok halaman |
| `judul` | judul singkat |
| `file` | path relatif file yang dibaca |
| `tujuan` | ringkasan tujuan belajar |
| `yang_dilihat` | daftar hal yang perlu diperhatikan |
| `eksperimen` | langkah yang bisa dicoba |
| `demo_url` | tautan ke panel GUI |
| `faq_terkait` | pertanyaan FAQ yang relevan (boleh kosong) |

`reading_order` menentukan urutan 12 titik yang disarankan di sidebar.

## 3. Daftar 24 titik review

### Modul A — Route

| ID | Judul | File | Demo |
|---|---|---|---|
| `rt-01` | Route per HTTP method: GET dan POST | `app/Http/Controllers/Api/DemoRouteController.php` | `/belajar/route#metode-playground` |
| `rt-02` | Route::match() dan Route::any() | `app/Http/Controllers/Api/DemoRouteController.php` | `/belajar/route#match-any` |
| `rt-03` | Urutan registrasi route mirip | `app/Http/Controllers/Api/DemoRouteController.php` | `/belajar/route#urutan-route` |
| `rt-04` | Parameter route dan constraint where() | `app/Http/Controllers/Api/DemoRouteController.php` | `/belajar/route#constraint` |
| `rt-05` | Route::resource di area web | `routes/web.php` | `/belajar/controller#resource` |
| `rt-06` | Method spoofing dan CSRF di grup web | `app/Http/Controllers/DemoController.php` | `/belajar/route#csrf` |

### Modul B — Middleware

| ID | Judul | File | Demo |
|---|---|---|---|
| `mw-01` | Registrasi alias middleware | `bootstrap/app.php` | `/belajar/middleware#alias` |
| `mw-02` | CheckUserRole: parameter variadic dan $next | `app/Http/Middleware/CheckUserRole.php` | `/belajar/middleware#parameter` |
| `mw-03` | Middleware pada route group prefix admin | `routes/api.php` | `/belajar/middleware#group-admin` |
| `mw-04` | Auth simulasi dan urutan middleware | `app/Http/Middleware/DemoAuthenticate.php` | `/belajar/middleware#urutan` |
| `mw-05` | Middleware bawaan throttle | `app/Http/Controllers/Api/MiddlewareDemoController.php` | `/belajar/middleware#throttle` |
| `mw-06` | Middleware bawaan signed | `app/Http/Controllers/Api/MiddlewareDemoController.php` | `/belajar/middleware#signed` |
| `mw-07` | Middleware global: trace dan header | `bootstrap/app.php` | `/belajar/middleware#global` |
| `mw-08` | Role global dapat memblokir endpoint publik | `app/Http/Middleware/DemoGlobalRoleSwitch.php` | `/belajar/middleware#global-role` |

### Modul C — Controller

| ID | Judul | File | Demo |
|---|---|---|---|
| `ct-01` | Controller biasa: view dan JSON | `app/Http/Controllers/DashboardController.php` | `/belajar/controller#controller-biasa` |
| `ct-02` | Resource controller: index dan pagination | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#crud` |
| `ct-03` | Validasi request dan 422 | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#validasi` |
| `ct-04` | Route Model Binding tanpa database | `app/Demo/Models/Product.php` | `/belajar/controller#binding` |
| `ct-05` | Dependency Injection constructor dan method | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#di` |
| `ct-06` | Lima jenis response controller | `app/Http/Controllers/Api/ResponseDemoController.php` | `/belajar/controller#response` |
| `ct-07` | Parameter opsional {category?} | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#parameter-opsional` |
| `ct-08` | Update dengan sometimes | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#put-patch` |
| `ct-09` | destroy dan response 204 | `app/Http/Controllers/Api/ProductApiController.php` | `/belajar/controller#response` |
| `ct-10` | Simulasi mass assignment dan $fillable | `app/Http/Controllers/Api/DemoRouteController.php` | `/belajar/controller#mass-assignment` |

Jumlah: **6 route + 8 middleware + 10 controller = 24 titik review**.

## 4. Urutan baca yang disarankan

1. `mw-07` — middleware global dan trace;
2. `mw-01` — registrasi alias middleware;
3. `rt-01` — route dan method HTTP;
4. `rt-05` — resource web vs API;
5. `mw-02` — `CheckUserRole` inti materi;
6. `mw-03` — middleware pada route group;
7. `ct-01` — controller view dan JSON;
8. `ct-02` — resource controller dan pagination;
9. `ct-03` — validasi;
10. `ct-04` — route model binding;
11. `ct-05` — dependency injection;
12. `ct-06` — lima jenis response.

## 5. Viewer dan allowlist

`App\Demo\CodeReview\SnippetReader` hanya menerima path dari registry. Viewer tidak menyediakan
parameter path bebas. Path divalidasi berlapis:

1. menolak path kosong, NUL byte, path absolut, dan segmen `..`/`.`;
2. menolak `.env`, `storage/`, dan `vendor/`;
3. hanya mengizinkan direktori `app/`, `routes/`, `resources/views/`, `docs/`, `tests/`;
4. hanya mengizinkan file `bootstrap/app.php` dan `config/demo_review.php`;
5. hanya mengizinkan ekstensi `.php` dan `.md`;
6. memakai `realpath()` dan memastikan file hasil resolve masih berada di root proyek;
7. membaca file tanpa menulis atau mengeksekusinya.

Karena itu symlink yang keluar root juga ditolak.

## 6. Menambah titik review

1. Tambahkan region dan marker di file kode:
   `[CODE-REVIEW:xx-99]`, `region demo:xx-99`, `endregion demo:xx-99`.
2. Tambahkan entri ke `config/demo_review.php`.
3. Tambahkan anchor yang sesuai di halaman GUI.
4. Pastikan ada tautan dari kartu demo ke id tersebut (atau dari FAQ).
5. Jalankan test konsistensi dan test anchor:

```bash
php artisan test --filter=CodeReviewTest
```

Test memeriksa:

- jumlah dan keunikan id;
- marker ↔ registry dua arah;
- keberadaan file dan region;
- halaman detail bisa dibuka;
- halaman demo dan anchor benar-benar ada;
- FAQ menunjuk id yang valid.

## 7. Catatan keamanan

- Viewer hanya untuk membaca kode lokal.
- Jangan menambahkan path yang berasal langsung dari query string pengguna.
- Jangan menampilkan stack trace mentah di halaman demo; error dijelaskan lewat envelope JSON.
- Kode dan output ditampilkan dengan escape Blade; jangan memakai `{!! !!}` untuk input pengguna.

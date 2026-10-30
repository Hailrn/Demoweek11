# Panduan Terminal

Panduan ini memakai server lokal di `http://localhost:8000`. Jalankan `php artisan serve` di terminal
pertama, lalu buka terminal kedua untuk perintah `curl`.

> **Status verifikasi:** perintah Linux sudah dijalankan pada PHP 8.5.12/Laravel 13.39.0. Perintah
> macOS memakai sintaks shell yang sama, tetapi **belum diverifikasi di macOS**. Perintah Windows CMD
> dan Windows PowerShell **belum diverifikasi di Windows**. Bagian yang berbeda ditandai.

## 0. Perbedaan shell

| Shell | Catatan |
|---|---|
| Linux/macOS | `curl`, tanda kutip tunggal untuk JSON, `"$BASE"` untuk variabel |
| Windows CMD | Gunakan `curl` bawaan Windows 10+; JSON dibungkus kutip ganda dan tanda kutip di dalamnya di-escape; variabel memakai `%BASE%` |
| Windows PowerShell | Gunakan `curl.exe`, bukan alias `curl` (di Windows PowerShell 5.1 alias `curl` adalah `Invoke-WebRequest`); variabel memakai `$BASE` |

Untuk PowerShell modern, `Invoke-RestMethod` juga disediakan pada contoh tertentu. Perintah yang
mengharapkan status error (401/403/404/419/429) lebih mudah dibaca dengan `curl.exe -i`.

## 1. Setup dan menjalankan server

Perintahnya sama di tiga shell (jalankan dari folder proyek):

```bash
composer install
composer setup-demo
php artisan serve
```

Buka <http://localhost:8000>. Hentikan server dengan `Ctrl+C`.

Jika `.env` sudah ada dan Anda tidak ingin tiga driver diubah:

```bash
php artisan demo:setup --keep-env
```

Jangan gunakan `--keep-env` jika `.env` masih menunjuk database yang tidak tersedia.

## 2. Menyiapkan BASE

Jalankan sekali di terminal yang dipakai:

**Linux/macOS**

```bash
BASE=http://localhost:8000
```

**Windows CMD**

```bat
set BASE=http://localhost:8000
```

**Windows PowerShell**

```powershell
$BASE = "http://localhost:8000"
```

## 3. Route dan method HTTP

### Linux/macOS

```bash
curl -i "$BASE/api/demo/hello"
curl -i -X POST "$BASE/api/demo/hello"
curl -i -X DELETE "$BASE/api/demo/echo"
curl -i -X DELETE "$BASE/api/demo/webhook"
curl -i "$BASE/api/demo/where/abc"
curl -i "$BASE/api/demo/where-numeric/abc"
```

### Windows CMD

```bat
curl -i "%BASE%/api/demo/hello"
curl -i -X POST "%BASE%/api/demo/hello"
curl -i -X DELETE "%BASE%/api/demo/echo"
curl -i -X DELETE "%BASE%/api/demo/webhook"
curl -i "%BASE%/api/demo/where/abc"
curl -i "%BASE%/api/demo/where-numeric/abc"
```

### Windows PowerShell

```powershell
curl.exe -i "$BASE/api/demo/hello"
curl.exe -i -X POST "$BASE/api/demo/hello"
curl.exe -i -X DELETE "$BASE/api/demo/echo"
curl.exe -i -X DELETE "$BASE/api/demo/webhook"
curl.exe -i "$BASE/api/demo/where/abc"
curl.exe -i "$BASE/api/demo/where-numeric/abc"
```

Status yang diharapkan:

| Request | Status |
|---|---|
| `GET /api/demo/hello` | 200 |
| `POST /api/demo/hello` | 405 + header `Allow` |
| `DELETE /api/demo/echo` | 405 |
| `DELETE /api/demo/webhook` | 200 (route `any`) |
| `/api/demo/where/abc` | 200 |
| `/api/demo/where-numeric/abc` | 404 |

Urutan route juga bisa dibandingkan:

```bash
curl -i "$BASE/api/demo/order/special"
curl -i "$BASE/api/demo/order/apa-saja"
```

### OPTIONS dan HEAD

`HEAD` adalah `GET` tanpa body: status dan header tetap ada, body kosong. Laravel menambahkan `HEAD`
secara otomatis untuk setiap route `GET`. `OPTIONS` dipakai klien untuk menanyakan method yang
diizinkan; jawabannya ada di header `Allow`. Jika sebuah URI ditangani `Route::any()`, `OPTIONS`
ikut masuk ke controller sehingga `Allow` tidak muncul — itu bahan pembanding yang bagus.

**Linux/macOS**

```bash
curl -i -X HEAD "$BASE/api/demo/hello"          # 200, body kosong, header tetap ada
curl -sS -X HEAD "$BASE/api/demo/hello" | wc -c # 0 (body kosong)
curl -i -X OPTIONS "$BASE/api/demo/hello"       # Allow: GET,HEAD
curl -i -X OPTIONS "$BASE/api/products"         # Allow: GET,HEAD,POST
curl -i -X OPTIONS "$BASE/api/products/1"       # Allow: GET,HEAD,PUT,PATCH,DELETE
curl -i -X OPTIONS "$BASE/api/demo/echo"        # Allow: GET,HEAD,POST (hasil Route::match)
curl -i -X OPTIONS "$BASE/api/demo/webhook"     # 200 dari controller (Route::any menangkap OPTIONS)
```

**Windows CMD**

```bat
curl -i -X HEAD "%BASE%/api/demo/hello"
curl -i -X OPTIONS "%BASE%/api/demo/hello"
curl -i -X OPTIONS "%BASE%/api/products"
curl -i -X OPTIONS "%BASE%/api/products/1"
curl -i -X OPTIONS "%BASE%/api/demo/echo"
curl -i -X OPTIONS "%BASE%/api/demo/webhook"
```

**Windows PowerShell**

```powershell
curl.exe -i -X HEAD "$BASE/api/demo/hello"
curl.exe -i -X OPTIONS "$BASE/api/demo/hello"
curl.exe -i -X OPTIONS "$BASE/api/products"
curl.exe -i -X OPTIONS "$BASE/api/products/1"
curl.exe -i -X OPTIONS "$BASE/api/demo/echo"
curl.exe -i -X OPTIONS "$BASE/api/demo/webhook"
```

Di PowerShell 5.1, `curl` adalah alias `Invoke-WebRequest`, jadi selalu pakai `curl.exe`. Untuk
`HEAD`, `curl.exe -I "$BASE/api/demo/hello"` juga bisa dan hasilnya sama.

Status yang diharapkan:

| Request | Status | Catatan |
|---|---|---|
| `HEAD /api/demo/hello` | 200 | Body kosong, `Content-Type: application/json` |
| `OPTIONS /api/demo/hello` | 200 | `Allow: GET,HEAD` |
| `OPTIONS /api/products` | 200 | `Allow: GET,HEAD,POST` |
| `OPTIONS /api/products/1` | 200 | `Allow: GET,HEAD,PUT,PATCH,DELETE` |
| `OPTIONS /api/demo/echo` | 200 | `Allow: GET,HEAD,POST` (`Route::match`) |
| `OPTIONS /api/demo/webhook` | 200 | Tanpa `Allow`; `Route::any` menangkap OPTIONS |

Semua baris di atas sudah dijalankan di Linux (PHP 8.5.12/Laravel 13.39.0). Sisi Windows belum
diverifikasi.

## 4. Middleware dan peran

Role demo hanya simulasi. Untuk API, sumber utamanya header `X-Demo-Role`.

### Linux/macOS

```bash
curl -i "$BASE/api/admin/dashboard"
curl -i -H "X-Demo-Role: admin" "$BASE/api/admin/dashboard"
curl -i -H "X-Demo-Role: kasir" "$BASE/api/admin/reports"
curl -i -H "X-Demo-Role: owner" "$BASE/api/admin/reports"
curl -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" "$BASE/api/demo/global-public"
curl -i -H "X-Demo-Role: admin" -H "X-Demo-Stop-Before-Next: 1" "$BASE/api/demo/middleware/stop"
```

### Windows CMD

```bat
curl -i "%BASE%/api/admin/dashboard"
curl -i -H "X-Demo-Role: admin" "%BASE%/api/admin/dashboard"
curl -i -H "X-Demo-Role: kasir" "%BASE%/api/admin/reports"
curl -i -H "X-Demo-Role: owner" "%BASE%/api/admin/reports"
curl -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" "%BASE%/api/demo/global-public"
curl -i -H "X-Demo-Role: admin" -H "X-Demo-Stop-Before-Next: 1" "%BASE%/api/demo/middleware/stop"
```

### Windows PowerShell

```powershell
curl.exe -i "$BASE/api/admin/dashboard"
curl.exe -i -H "X-Demo-Role: admin" "$BASE/api/admin/dashboard"
curl.exe -i -H "X-Demo-Role: kasir" "$BASE/api/admin/reports"
curl.exe -i -H "X-Demo-Role: owner" "$BASE/api/admin/reports"
curl.exe -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" "$BASE/api/demo/global-public"
curl.exe -i -H "X-Demo-Role: admin" -H "X-Demo-Stop-Before-Next: 1" "$BASE/api/demo/middleware/stop"
```

Hasil matriks:

| Endpoint | guest | kasir | owner | admin |
|---|---:|---:|---:|---:|
| `/api/admin/dashboard` | 401 | 403 | 403 | 200 |
| `/api/admin/reports` | 401 | 403 | 200 | 200 |
| `/api/admin/area/info` | 401 | 403 | 200 | 200 |

`X-Demo-Stop-Before-Next: 1` membuat middleware role mengembalikan response `stopped` tanpa
menjalankan controller. Bandingkan body dan trace dengan request tanpa header.

### Peran dari session

Peran session hanya berlaku di browser. Buka halaman web, pilih peran di sidebar, lalu jalankan
`GET /demo/session-admin` dari panel GUI atau dari tab browser. Guest → 401, kasir → 403,
owner/admin → 200. Panel API tidak mengirim session karena grup API tidak memakai cookie/session.

Alternatif Bearer demo:

```bash
curl -i -H "Authorization: Bearer demo-admin" "$BASE/api/admin/dashboard"
```

## 5. Controller dan CRUD API

### Melihat data dan binding

Linux/macOS:

```bash
curl -i "$BASE/api/products"
curl -i "$BASE/api/products?page=2"
curl -i "$BASE/api/products/1"
curl -i "$BASE/api/products/999"
```

Windows CMD: ganti `"$BASE/..."` menjadi `"%BASE%/..."` seperti contoh sebelumnya.
PowerShell: ganti `curl` dengan `curl.exe` dan tetap gunakan `"$BASE/..."`.

`/api/products/999` harus 404 karena implicit route model binding tidak menemukan produk.
Bandingkan dengan pencarian manual `/api/demo/products-by-id/999`, yang juga 404 tetapi melewati
controller sebelum `abort(404)`.

### Membuat produk (201)

**Linux/macOS**

```bash
curl -i -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  --data '{"name":"Produk Terminal","category":"makanan","price":12000,"stock":5,"description":"Dibuat dari terminal"}' \
  "$BASE/api/products"
```

**Windows CMD**

```bat
curl -i -X POST -H "Accept: application/json" -H "Content-Type: application/json" --data "{\"name\":\"Produk Terminal\",\"category\":\"makanan\",\"price\":12000,\"stock\":5,\"description\":\"Dibuat dari CMD\"}" "%BASE%/api/products"
```

**Windows PowerShell**

```powershell
curl.exe -i -X POST -H "Accept: application/json" -H "Content-Type: application/json" --data '{"name":"Produk Terminal","category":"makanan","price":12000,"stock":5,"description":"Dibuat dari PowerShell"}' "$BASE/api/products"
```

Catat `data.id` pada response. Jika data baru saja direset, id biasanya `15`. Gunakan id tersebut
pada PATCH dan DELETE berikut.

### Update parsial dan body kosong

```bash
curl -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data '{"stock":7}' "$BASE/api/products/15"
curl -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data '{}' "$BASE/api/products/1"
curl -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data '{"name":""}' "$BASE/api/products/1"
```

Status: `{"stock":7}` → 200, `{}` → 200 tanpa perubahan, `{"name":""}` → 422.

### Hapus produk (204)

```bash
curl -i -X DELETE -H "Accept: application/json" "$BASE/api/products/15"
curl -i "$BASE/api/products/15"
```

Status: DELETE → 204 tanpa body; GET setelahnya → 404. Di Windows, ganti `curl`/path sesuai shell.

### Validasi 422

```bash
curl -i -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  --data '{"name":"","category":"makanan","price":-1,"stock":0}' \
  "$BASE/api/products"
```

Envelope error berisi `status`, `message`, `data: null`, dan `errors` per field.

### Lima jenis response

```bash
curl -i "$BASE/api/demo/response/json"
curl -i "$BASE/api/demo/response/redirect"
curl -i "$BASE/api/demo/response/download"
curl -i "$BASE/api/demo/response/no-content"
curl -i "$BASE/api/demo/response/inspect/redirect"
```

Perhatikan `Content-Type`, `Location`, `Content-Disposition`, dan body kosong pada 204. Endpoint
`inspect/*` berguna karena `curl -L` atau browser akan mengikuti redirect.

### Mengembalikan data seed

```bash
curl -i -X POST "$BASE/api/demo/reset"
```

Endpoint API ini tidak memakai CSRF. Reset juga tersedia lewat tombol di GUI.

## 6. CSRF hanya di route web

Route API tidak memakai CSRF. Route `/demo/csrf-test` sengaja memakai `StrictDemoCsrf` agar token
tetap terlihat. Tanpa token:

```bash
curl -i -X POST -H "Accept: application/json" "$BASE/demo/csrf-test"
```

Status yang diharapkan: **419**.

### Ambil token di Linux/macOS

Perintah ini mengambil cookie session dan token dari meta tag, lalu mengirim POST:

```bash
jar=$(mktemp)
token=$(curl -sS -c "$jar" "$BASE/belajar/route" | php -r '$h=stream_get_contents(STDIN); preg_match("/meta name=\"csrf-token\" content=\"([^\"]+)\"/", $h, $m); echo $m[1] ?? "";')
curl -i -X POST -b "$jar" -H "X-CSRF-TOKEN: $token" -H "Accept: application/json" "$BASE/demo/csrf-test"
```

Status yang diharapkan: **200**. Hapus file cookie setelah selesai jika diinginkan. Jalur ini sudah
diuji di Linux; sintaks macOS belum diuji.

### PowerShell

```powershell
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$page = Invoke-WebRequest -Uri "$BASE/belajar/route" -WebSession $session -UseBasicParsing
$token = ([regex]::Match($page.Content, 'name="csrf-token" content="([^"]+)"')).Groups[1].Value
Invoke-WebRequest -Uri "$BASE/demo/csrf-test" -Method POST -WebSession $session -Headers @{ "X-CSRF-TOKEN" = $token; "Accept" = "application/json" }
```

Belum diverifikasi di Windows.

### Windows CMD

Alur token paling mudah dilakukan di GUI Modul Route: tombol **Tanpa token → 419** dan
**Dengan token → 200**. Ekstraksi token + cookie di CMD tidak disertakan karena rawan perbedaan
kutip; gunakan PowerShell atau GUI.

## 7. Signed URL

Buat URL valid dan URL yang signature-nya sengaja diubah:

```bash
curl -sS "$BASE/api/demo/signed/generate"
```

Response berisi `valid_url` dan `tampered_url`. Buka keduanya di browser atau pakai `curl`.

### Linux/macOS: ekstrak otomatis

```bash
json=$(curl -sS "$BASE/api/demo/signed/generate")
valid=$(printf '%s' "$json" | php -r '$j=json_decode(stream_get_contents(STDIN), true); echo $j["data"]["valid_url"] ?? "";')
tampered=$(printf '%s' "$json" | php -r '$j=json_decode(stream_get_contents(STDIN), true); echo $j["data"]["tampered_url"] ?? "";')
curl -i "$valid"
curl -i "$tampered"
```

Status: valid → **200**, tampered → **403**. Jalur ini diuji di Linux.

PowerShell dapat memakai:

```powershell
$signed = Invoke-RestMethod -Uri "$BASE/api/demo/signed/generate"
curl.exe -i $signed.data.valid_url
curl.exe -i $signed.data.tampered_url
```

Belum diverifikasi di Windows. Untuk CMD, jalankan endpoint generator, salin `valid_url` dan
`tampered_url` ke perintah `curl`.

## 8. Throttle 429

Route `/api/demo/throttle` memakai `throttle:5,1`. Kirim enam request cepat.

**Linux/macOS**

```bash
for i in $(seq 1 6); do
  curl -sS -o /dev/null -w "%{http_code}\n" "$BASE/api/demo/throttle"
done
```

**Windows CMD**

```bat
for /L %i in (1,1,6) do @curl -s -o NUL -w "%{http_code}\n" "%BASE%/api/demo/throttle"
```

**Windows PowerShell**

```powershell
1..6 | ForEach-Object { curl.exe -s -o NUL -w "%{http_code}\n" "$BASE/api/demo/throttle" }
```

Hasil yang diharapkan: lima kali `200`, lalu `429`. Respons 429 memiliki `Retry-After`.
Jika kuota masih terpakai, tunggu satu menit atau bersihkan cache lokal:

```bash
php artisan cache:clear
```

Perintah `cache:clear` menggunakan driver cache yang aktif (di setup demo: file).

## 9. Pemeriksaan lokal

```bash
php artisan route:list
php artisan route:list --path=api
php artisan test
php artisan view:cache
```

`php artisan test` tidak membutuhkan database. Test memakai session dan cache array serta state
terpisah di `storage/framework/testing/`.

## 10. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| 419 pada POST web | Token CSRF tidak dikirim/tidak cocok | Gunakan token dari halaman, atau uji endpoint API yang memang tanpa CSRF |
| 500 setelah setup | `.env` masih memakai driver `database` | Jalankan `composer setup-demo` (tanpa `--keep-env`) |
| 429 terus muncul | Kuota throttle satu menit belum habis | Tunggu satu menit atau `php artisan cache:clear` |
| 404 pada produk | Produk sudah dihapus atau state berubah | `POST /api/demo/reset` atau tombol reset |
| Signed URL 403 | Signature diubah/kedaluwarsa | Buat ulang dari `/api/demo/signed/generate` |
| `curl` di PowerShell aneh | `curl` adalah alias `Invoke-WebRequest` | Gunakan `curl.exe` |
| Perubahan config tidak terbaca | Config cache lama | `php artisan config:clear` |
| Port 8000 dipakai | Server lain masih berjalan | Hentikan server lama atau `php artisan serve --port=8001` |

Jika memakai port lain, ganti `BASE` dan URL di panduan ini.

## 11. Ringkasan status yang diharapkan

| Skenario | Status |
|---|---|
| GET hello | 200 |
| HEAD hello | 200, body kosong |
| OPTIONS hello | 200 + `Allow: GET,HEAD` |
| OPTIONS `/api/products/1` | 200 + `Allow: GET,HEAD,PUT,PATCH,DELETE` |
| POST ke route GET | 405 |
| DELETE echo | 405 |
| DELETE webhook | 200 |
| where-numeric dengan `abc` | 404 |
| Admin dashboard tanpa user | 401 |
| Kasir ke reports | 403 |
| Owner/admin ke reports | 200 |
| Global role aktif + kasir | 403 |
| GET produk ada | 200 |
| GET produk tidak ada | 404 |
| POST valid | 201 |
| POST tidak valid | 422 |
| PATCH body `{}` | 200 tanpa perubahan |
| DELETE | 204 |
| POST web tanpa CSRF | 419 |
| POST web dengan token | 200 |
| Signed URL valid | 200 |
| Signed URL tampered | 403 |
| Request throttle keenam | 429 |
| Reset API | 200 |

## 12. Catatan keamanan

- `X-Demo-Role`, Bearer token demo, dan signed URL di proyek ini adalah simulasi.
- Jangan memakai header role sebagai autentikasi di produksi.
- Jangan deploy dengan `APP_DEBUG=true`.
- Jangan menjalankan perintah yang mengubah file aplikasi saat runtime; demo memakai flag request.
- Semua data produk adalah data lokal, bukan data pengguna nyata.

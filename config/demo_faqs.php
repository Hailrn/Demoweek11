<?php

/*
| FAQ interaktif. Setiap item punya skenario yang bisa dijalankan GUI.
| "kind" khusus dipakai JavaScript untuk skenario multi-langkah/spoof/CSRF.
*/
return [
    'route' => [
        [
            'id' => 'faq-rt-405',
            'q' => 'Kalau method salah, misalnya POST ke route GET?',
            'a' => 'Laravel menemukan URI-nya tetapi method tidak cocok. Hasilnya 405 Method Not Allowed, dan header Allow berisi method yang tersedia. Controller tidak dijalankan.',
            'scenario' => ['method' => 'POST', 'path' => '/api/demo/hello'],
            'code' => 'rt-01',
        ],
        [
            'id' => 'faq-rt-404',
            'q' => 'Kalau URI-nya tidak ada sama sekali?',
            'a' => 'Tidak ada route yang cocok, sehingga hasilnya 404. Bandingkan dengan 405: 405 berarti URI ada tetapi method salah.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/tidak-ada'],
            'code' => 'rt-01',
        ],
        [
            'id' => 'faq-rt-abc',
            'q' => 'Kalau parameter {id} diisi "abc"?',
            'a' => 'Tanpa constraint, "abc" tetap diterima sebagai string. Dengan ->where(\'id\', \'[0-9]+\'), route tidak cocok dan menghasilkan 404 sebelum controller berjalan. Coba juga /api/demo/where-numeric/abc.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/where/abc'],
            'code' => 'rt-04',
        ],
        [
            'id' => 'faq-rt-order',
            'q' => 'Kalau ada dua route mirip, mana yang menang?',
            'a' => 'Route yang didaftarkan lebih dulu dan cocok akan menang. Di demo ini /api/demo/order/special didaftarkan sebelum /api/demo/order/{slug}, jadi "special" masuk ke route statis.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/order/special'],
            'code' => 'rt-03',
        ],
        [
            'id' => 'faq-rt-spoof',
            'q' => 'Form HTML hanya bisa GET/POST. Bagaimana mengirim PUT/DELETE?',
            'a' => 'Gunakan method spoofing: form mengirim POST dengan field tersembunyi _method=PUT. Laravel membaca field itu lalu memilih route PUT/PATCH/DELETE. Ini tambahan singkat di luar contoh materi.',
            'scenario' => ['kind' => 'spoof', 'method' => 'POST', 'path' => '/demo/spoof', 'body' => ['_method' => 'PUT', 'name' => 'Produk dari form HTML']],
            'code' => 'rt-06',
        ],
        [
            'id' => 'faq-rt-csrf',
            'q' => 'Kenapa POST browser bisa kena 419, sedangkan curl ke API tidak?',
            'a' => 'Route web dilindungi CSRF; route API tidak. Laravel 13 menambahkan izin same-origin lewat Sec-Fetch-Site, jadi demo ini memakai pemeriksa CSRF ketat agar token tetap terlihat. Tanpa token → 419. Di API, tidak ada pemeriksaan token.',
            'scenario' => ['kind' => 'csrf', 'method' => 'POST', 'path' => '/demo/csrf-test', 'body' => ['name' => 'tanpa token']],
            'code' => 'rt-06',
        ],
    ],

    'middleware' => [
        [
            'id' => 'faq-mw-401',
            'q' => 'Kalau tamu membuka endpoint admin?',
            'a' => 'Tidak ada user, jadi $request->user() null. CheckUserRole (dan auth simulasi) menghentikan request dengan abort(401). Controller tidak dijalankan.',
            'scenario' => ['method' => 'GET', 'path' => '/api/admin/dashboard', 'role' => 'guest'],
            'code' => 'mw-03',
        ],
        [
            'id' => 'faq-mw-403',
            'q' => 'Kalau kasir membuka laporan yang hanya untuk admin/owner?',
            'a' => 'User ada, jadi bukan 401. Tetapi role kasir tidak ada di parameter role:admin,owner, sehingga middleware memanggil abort(403). Inilah perbedaan 401 (belum terautentikasi) dan 403 (terautentikasi tetapi tidak berwenang).',
            'scenario' => ['method' => 'GET', 'path' => '/api/admin/reports', 'role' => 'kasir'],
            'code' => 'mw-03',
        ],
        [
            'id' => 'faq-mw-stop',
            'q' => 'Apa yang terjadi kalau middleware tidak memanggil $next?',
            'a' => 'Request berhenti di middleware. Controller tidak pernah dijalankan, tetapi response tetap bisa dikembalikan middleware. Bandingkan body dan trace dengan request normal.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/middleware/stop', 'role' => 'admin', 'headers' => ['X-Demo-Stop-Before-Next' => '1']],
            'code' => 'mw-02',
        ],
        [
            'id' => 'faq-mw-order',
            'q' => 'Apa bedanya urutan auth lalu role, atau role lalu auth?',
            'a' => 'Status akhir bisa sama, tetapi lapisan yang berhenti berbeda. Untuk tamu, role-first berhenti di CheckUserRole; auth-first berhenti di demo.auth. Lihat urutan entri di trace.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/middleware-order/role-first', 'role' => 'guest'],
            'code' => 'mw-04',
        ],
        [
            'id' => 'faq-mw-alias',
            'q' => 'Kalau alias middleware belum didaftarkan?',
            'a' => 'Container tidak menemukan class yang dituju. Laravel melempar error seperti Target class [...] does not exist. Endpoint demo ini menangkap pesannya agar tidak menampilkan stack trace mentah.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/middleware/missing-alias'],
            'code' => 'mw-01',
        ],
        [
            'id' => 'faq-mw-global',
            'q' => 'Kalau role dipasang sebagai middleware global?',
            'a' => 'Middleware global berjalan untuk semua route, termasuk endpoint publik. Demo ini mengaktifkan role global lewat flag header; endpoint publik pun ikut 403 untuk role yang tidak diizinkan. Ini alasan role sebaiknya dipasang pada route/group tertentu.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/global-public', 'role' => 'kasir', 'headers' => ['X-Demo-Global-Role' => '1']],
            'code' => 'mw-08',
        ],
        [
            'id' => 'faq-mw-throttle',
            'q' => 'Bagaimana cara melihat 429 dari throttle?',
            'a' => 'Route demo memakai throttle:5,1. Kirim 6 request cepat; request keenam menghasilkan 429 dengan header Retry-After. Di GUI, counter dan sisa kuota ikut ditampilkan.',
            'scenario' => ['kind' => 'throttle', 'method' => 'GET', 'path' => '/api/demo/throttle'],
            'code' => 'mw-05',
        ],
        [
            'id' => 'faq-mw-signed',
            'q' => 'Bagaimana signed URL membuktikan URL tidak diubah?',
            'a' => 'Signature adalah hash dari URL dan expires. URL valid membuka halaman; satu karakter yang diubah membuat signature tidak cocok sehingga Laravel mengembalikan 403.',
            'scenario' => ['kind' => 'signed', 'method' => 'GET', 'path' => '/api/demo/signed/generate'],
            'code' => 'mw-06',
        ],
    ],

    'controller' => [
        [
            'id' => 'faq-ct-empty',
            'q' => 'Kalau update mengirim body kosong dan memakai sometimes?',
            'a' => 'Aturan sometimes melewati validasi untuk field yang tidak ada. Request tetap 200, tetapi tidak ada field yang berubah. Ini berbeda dari required yang akan menolak body kosong.',
            'scenario' => ['method' => 'PATCH', 'path' => '/api/products/1', 'body' => []],
            'code' => 'ct-08',
        ],
        [
            'id' => 'faq-ct-required',
            'q' => 'Kalau required dipakai pada update, bedanya apa?',
            'a' => 'required tetap menuntut field ada dan tidak kosong pada setiap request update. PATCH body {"name":""} menghasilkan 422 dengan detail error per field. Ini membuat update parsial menjadi sulit.',
            'scenario' => ['method' => 'PATCH', 'path' => '/api/products/1', 'body' => ['name' => '']],
            'code' => 'ct-03',
        ],
        [
            'id' => 'faq-ct-fillable',
            'q' => 'Kalau $fillable tidak diisi, apa risikonya?',
            'a' => 'Tanpa allowlist, field tambahan dari input bisa ikut masuk ke create()/update() (mass assignment). Demo ini mensimulasikan fillable: name dan price diterima, is_admin diabaikan.',
            'scenario' => ['method' => 'POST', 'path' => '/api/demo/mass-assignment', 'body' => ['name' => 'Produk Baru', 'price' => 10000, 'is_admin' => true]],
            'code' => 'ct-10',
        ],
        [
            'id' => 'faq-ct-di',
            'q' => 'Bagaimana urutan parameter route dan dependency injection?',
            'a' => 'Laravel mencocokkan parameter route berdasarkan nama dan tipe. Letakkan dependency seperti Request/ProductService di posisi yang konsisten; parameter route dengan nama yang sama akan di-resolve sebagai model/nilai. Panel DI menunjukkan class yang diinjeksi.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/di?q=kopi'],
            'code' => 'ct-05',
        ],
        [
            'id' => 'faq-ct-array',
            'q' => 'Kalau controller mengembalikan array biasa?',
            'a' => 'Laravel otomatis mengubah array menjadi response JSON dengan Content-Type application/json. Tidak perlu response()->json() jika hanya butuh JSON sederhana, tetapi envelope eksplisit lebih mudah dikontrol.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/array-response'],
            'code' => 'ct-06',
        ],
        [
            'id' => 'faq-ct-envelope',
            'q' => 'Bagaimana menjaga format JSON tetap konsisten?',
            'a' => 'Gunakan envelope yang sama: status, message, data, dan errors (khusus validasi). Semua endpoint demo mengikuti pola ini, termasuk error API. UI menampilkan contoh sukses dan error berdampingan.',
            'scenario' => ['method' => 'GET', 'path' => '/api/demo/envelope'],
            'code' => 'ct-06',
        ],
        [
            'id' => 'faq-ct-binding',
            'q' => 'Apa bedanya Route Model Binding dan parameter string manual?',
            'a' => 'Binding Product $product otomatis mencari produk dan mengembalikan 404 jika tidak ada. Parameter string $id harus dicari manual dan rawan lupa menangani null. Coba /api/products/999 dan /api/demo/products-by-id/999.',
            'scenario' => ['method' => 'GET', 'path' => '/api/products/999'],
            'code' => 'ct-04',
        ],
    ],
];

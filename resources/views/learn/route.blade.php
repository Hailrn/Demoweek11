@extends('layouts.app')

@section('title', 'Modul A · Route')

@section('content')
<section class="page-hero">
    <p class="eyebrow">Modul A</p>
    <h1>Route</h1>
    <p class="lead" style="color:#d5e8df; max-width: 70ch;">
        Route adalah peta dari <strong>method + URI</strong> ke sebuah aksi. Di modul ini Anda bisa
        mengubah method, menguji route yang mirip, memasang constraint, dan melihat route yang benar-benar
        terdaftar di aplikasi.
    </p>
    <div class="hero-actions">
        <a class="btn" href="#metode-playground">Coba method playground</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="#daftar-route">Lihat daftar route</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.index') }}">Code Review</a>
    </div>
</section>

<x-correction-note>
    <p><strong>Koreksi materi:</strong> update resource memakai <code>PUT/PATCH</code>, bukan <code>PUT/UPDATE</code>. <code>UPDATE</code> bukan method HTTP.</p>
    <p><strong>Catatan Laravel 13:</strong> request POST same-origin dapat lolos pemeriksaan CSRF berdasarkan <code>Sec-Fetch-Site</code>. Supaya konsep token tetap terlihat, route demo CSRF memakai pemeriksa ketat tambahan.</p>
</x-correction-note>

<section class="section-head anchor-offset" id="metode-playground">
    <p class="eyebrow">Konsep 1 · HTTP method</p>
    <h2>Method menentukan method controller</h2>
    <p>GET ke <code>/api/demo/hello</code> dilayani <code>hello()</code>. POST ke URI yang sama tidak punya route POST, sehingga menghasilkan 405. Perhatikan header <code>Allow</code>.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="GET vs POST pada URI yang sama" purpose="Membedakan 405 (URI ada, method salah) dengan 404 (URI tidak ada)." code="rt-01" tag="Method">
        <div class="quick-examples" aria-label="Contoh request route">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/hello"}'>GET hello</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"POST","path":"/api/demo/hello"}'>POST hello → 405</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/echo"}'>GET echo</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"POST","path":"/api/demo/echo","body":{"message":"Halo dari panel"}}'>POST echo</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"OPTIONS","path":"/api/demo/hello"}'>OPTIONS → Allow</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"HEAD","path":"/api/demo/hello"}'>HEAD → body kosong</button>
        </div>
        <x-demo-runner path="/api/demo/hello" method="GET" show-body="true" :body="['message' => 'Halo dari panel']" editable-path="true" note="Coba ganti method select lalu tekan Kirim. Untuk OPTIONS, perhatikan header Allow (GET,HEAD). Untuk HEAD, status 200 tetap ada tetapi body kosong: header tetap dikirim, hanya isinya yang tidak." />
    </x-demo-card>

    <x-demo-card id="match-any" title="match() dan any()" purpose="Melihat route yang menerima beberapa method, dan risiko memakai any() terlalu longgar." code="rt-02" tag="Method">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"DELETE","path":"/api/demo/webhook"}'>DELETE webhook</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"DELETE","path":"/api/demo/echo"}'>DELETE echo → 405</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"POST","path":"/api/demo/products/search","body":{"q":"kopi"}}'>POST search</button>
        </div>
        <x-demo-runner path="/api/demo/webhook" method="DELETE" editable-path="true" note="webhook memakai Route::any(), sedangkan echo memakai Route::match(['get','post'])." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="tabel-method">
    <p class="eyebrow">Referensi cepat</p>
    <h2>Tabel method HTTP</h2>
    <p>Klik <strong>Isi playground</strong> pada satu baris; panel di bawah tabel terisi dan langsung mencoba request itu.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Tujuh method dan contohnya" purpose="Menyamakan method dengan contoh URI nyata, termasuk OPTIONS dan HEAD." code="rt-01" tag="Referensi">
        <div class="table-wrap">
            <table class="data-table method-table">
                <thead>
                    <tr><th>Method</th><th>Kegunaan</th><th>Contoh di demo</th><th></th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="method-badge method-GET">GET</span></td>
                        <td>Mengambil data; aman diulang tanpa mengubah state.</td>
                        <td><code class="tiny">GET /api/demo/hello</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"GET","path":"/api/demo/hello"}' aria-label="Isi playground dengan GET">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-POST">POST</span></td>
                        <td>Mengirim data baru; biasanya mengubah state atau membuat resource.</td>
                        <td><code class="tiny">POST /api/demo/echo</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"POST","path":"/api/demo/echo","body":{"message":"Halo dari tabel method"}}' aria-label="Isi playground dengan POST">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-PUT">PUT</span></td>
                        <td>Mengganti seluruh resource (full replacement); semua field dikirim ulang.</td>
                        <td><code class="tiny">PUT /api/demo/semantic-put/1</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"PUT","path":"/api/demo/semantic-put/1","body":{"name":"Kopi Arabika Gayo","category":"minuman","price":98000,"stock":12,"description":"PUT mengganti seluruh resource"}}' aria-label="Isi playground dengan PUT">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-PATCH">PATCH</span></td>
                        <td>Mengubah sebagian field; field lain dibiarkan seperti semula.</td>
                        <td><code class="tiny">PATCH /api/demo/semantic-patch/1</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"PATCH","path":"/api/demo/semantic-patch/1","body":{"stock":9}}' aria-label="Isi playground dengan PATCH">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-DELETE">DELETE</span></td>
                        <td>Menghapus resource.</td>
                        <td><code class="tiny">DELETE /api/demo/webhook</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"DELETE","path":"/api/demo/webhook"}' aria-label="Isi playground dengan DELETE">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-OPTIONS">OPTIONS</span></td>
                        <td>Menanyakan method yang diizinkan pada sebuah URI; jawabannya ada di header <code>Allow</code>.</td>
                        <td><code class="tiny">OPTIONS /api/demo/hello → Allow: GET,HEAD</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"OPTIONS","path":"/api/demo/hello"}' aria-label="Isi playground dengan OPTIONS">Isi playground</button></td>
                    </tr>
                    <tr>
                        <td><span class="method-badge method-HEAD">HEAD</span></td>
                        <td>Sama seperti GET tetapi body tidak dikirim; header tetap ada.</td>
                        <td><code class="tiny">HEAD /api/demo/hello → body kosong</code></td>
                        <td><button type="button" class="btn btn-small btn-ghost fill-btn" data-runner-preset='{"method":"HEAD","path":"/api/demo/hello"}' aria-label="Isi playground dengan HEAD">Isi playground</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="tiny">DELETE memakai <code>/api/demo/webhook</code> karena route itu memang menerima semua method.
        Untuk melihat 405, arahkan method yang salah ke <code>/api/demo/echo</code>.</p>
        <x-demo-runner path="/api/demo/hello" method="GET" show-body="true" editable-path="true" note="Klik salah satu tombol di tabel untuk mengisi panel ini. Status 200 dengan body kosong pada HEAD itu normal." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="urutan-route">
    <p class="eyebrow">Konsep 2 · Prioritas route</p>
    <h2>Dua route yang mirip</h2>
    <p>Route statis yang didaftarkan lebih awal menang. <code>/api/demo/order/special</code> tidak dianggap sebagai <code>{slug}</code>.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Statis sebelum dinamis" purpose="Memahami mengapa satu route bisa “mencuri” request dari route lain berdasarkan urutan registrasi." code="rt-03" tag="Urutan">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/order/special"}'>order/special</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/order/apa-saja"}'>order/{slug}</button>
        </div>
        <x-demo-runner path="/api/demo/order/special" method="GET" editable-path="true" note="Buka routes/api.php dan bandingkan urutan pendaftarannya." />
    </x-demo-card>

    <x-demo-card id="constraint" title="Constraint parameter" purpose="Membatasi nilai parameter sebelum controller dijalankan." code="rt-04" tag="Parameter">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/where/abc"}'>where/abc</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/where-numeric/abc"}'>numeric/abc → 404</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/where-numeric/42"}'>numeric/42</button>
        </div>
        <x-demo-runner path="/api/demo/where/abc" method="GET" editable-path="true" note="where('[0-9]+') menolak 'abc' di level route; controller tidak pernah berjalan." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="csrf">
    <p class="eyebrow">Konsep 3 · Form HTML</p>
    <h2>Method spoofing dan CSRF</h2>
    <p>Form HTML hanya mengirim GET atau POST. <code>_method=PUT</code> membuat Laravel memilih route PUT. Route web juga dilindungi token CSRF.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="POST berubah menjadi PUT" purpose="Membedakan method yang dikirim browser dari method yang dipakai router." code="rt-06" tag="Spoofing">
        <x-demo-runner
            path="/demo/spoof"
            method="POST"
            :body="['_method' => 'PUT', 'name' => 'Produk dari form HTML']"
            show-body="true"
            note="Panel mengirim POST. Field _method dibaca Laravel; respons melaporkan method setelah spoofing."
        />
    </x-demo-card>

    <x-demo-card title="Token CSRF: tanpa dan dengan" purpose="Melihat kenapa request web yang mengubah data bisa mendapat 419." code="rt-06" tag="Keamanan">
        <p class="tiny">Tombol pertama sengaja tidak mengirim token. Tombol kedua mengambil token dari meta tag halaman. Perhatikan status dan trace.</p>
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-csrf-mode="without">Tanpa token → 419</button>
            <button type="button" class="btn btn-small" data-csrf-mode="with">Dengan token → 200</button>
        </div>
        <div class="faq-result" data-csrf-result hidden aria-live="polite"></div>
    </x-demo-card>
</div>

<div class="notice">
    Route API tidak memakai CSRF karena tidak berbasis cookie/session. Itu sebabnya <code>curl -X POST /api/demo/echo</code>
    berhasil tanpa token. Tetap jangan menganggap API otomatis aman: autentikasi dan otorisasi tetap harus dirancang.
</div>

<section class="section-head anchor-offset" id="daftar-route">
    <p class="eyebrow">Route nyata</p>
    <h2>Daftar route yang terdaftar</h2>
    <p>Data diambil dari <code>Route::getRoutes()</code>, bukan daftar statis. Filter berdasarkan URI, nama, atau action.</p>
</section>

<div class="filter-bar">
    <label class="field">
        <span>Cari route</span>
        <input type="search" placeholder="Contoh: api/demo, products, middleware" data-route-filter>
    </label>
    <p class="tiny" data-route-count>{{ count($routes) }} route demo ditampilkan.</p>
</div>

<div class="table-wrap">
    <table class="route-table">
        <thead>
            <tr><th>Method</th><th>URI</th><th>Nama</th><th>Action</th><th>Middleware</th></tr>
        </thead>
        <tbody data-route-rows>
            @foreach ($routes as $route)
                <tr data-route-row data-search="{{ strtolower($route['uri'].' '.$route['name'].' '.$route['action'].' '.implode(' ', $route['methods'])) }}">
                    <td>
                        <div class="middleware-list">
                            @foreach ($route['methods'] as $method)
                                <span class="method-badge method-{{ $method }}">{{ $method }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="uri">{{ $route['uri'] !== '' ? $route['uri'] : '/' }}</td>
                    <td class="mono tiny">{{ $route['name'] ?: '—' }}</td>
                    <td>{{ $route['action'] }}</td>
                    <td>
                        <div class="middleware-list">
                            @forelse ($route['middleware'] as $middleware)
                                <span class="middleware-item">{{ $middleware }}</span>
                            @empty
                                <span class="tiny">—</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<section class="section-head">
    <p class="eyebrow">Terminal</p>
    <h2>Contoh yang bisa Anda jalankan</h2>
    <p>Jalankan server lebih dulu di terminal lain. Contoh memakai <code>http://localhost:8000</code>.</p>
</section>
<x-terminal-commands :commands="[
    'Linux / macOS' => 'curl -i http://localhost:8000/api/demo/hello'.PHP_EOL.'curl -i -X POST http://localhost:8000/api/demo/hello'.PHP_EOL.'curl -i -X DELETE http://localhost:8000/api/demo/echo'.PHP_EOL.'curl -i http://localhost:8000/api/demo/where-numeric/abc',
    'Windows CMD' => 'curl -i http://localhost:8000/api/demo/hello'.PHP_EOL.'curl -i -X POST http://localhost:8000/api/demo/hello'.PHP_EOL.'curl -i -X DELETE http://localhost:8000/api/demo/echo'.PHP_EOL.'curl -i http://localhost:8000/api/demo/where-numeric/abc',
    'Windows PowerShell' => 'curl.exe -i http://localhost:8000/api/demo/hello'.PHP_EOL.'curl.exe -i -X POST http://localhost:8000/api/demo/hello'.PHP_EOL.'curl.exe -i -X DELETE http://localhost:8000/api/demo/echo'.PHP_EOL.'curl.exe -i http://localhost:8000/api/demo/where-numeric/abc',
]" />

<section style="margin-top:2rem;">
    <x-faq-list :items="$faqs" />
</section>

<x-correction-note title="Ingat">
    <p>Request method yang tidak cocok menghasilkan <strong>405</strong>; URI yang tidak ada menghasilkan <strong>404</strong>. Keduanya bukan error controller, karena controller belum tentu dijalankan.</p>
</x-correction-note>
@endsection

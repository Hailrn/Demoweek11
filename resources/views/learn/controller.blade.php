@extends('layouts.app')

@section('title', 'Modul C · Controller')

@section('content')
@php
    $controllerCommands = [
        'Linux / macOS' => implode(PHP_EOL, [
            'curl -i http://localhost:8000/api/products',
            'curl -i http://localhost:8000/api/products/1',
            'curl -i http://localhost:8000/api/products/999',
            'curl -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data \'{}\' http://localhost:8000/api/products/1',
        ]),
        'Windows CMD' => implode(PHP_EOL, [
            'curl -i http://localhost:8000/api/products',
            'curl -i http://localhost:8000/api/products/1',
            'curl -i http://localhost:8000/api/products/999',
            'curl -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data "{}" http://localhost:8000/api/products/1',
        ]),
        'Windows PowerShell' => implode(PHP_EOL, [
            'curl.exe -i http://localhost:8000/api/products',
            'curl.exe -i http://localhost:8000/api/products/1',
            'curl.exe -i http://localhost:8000/api/products/999',
            'curl.exe -i -X PATCH -H "Accept: application/json" -H "Content-Type: application/json" --data \'{}\' http://localhost:8000/api/products/1',
        ]),
    ];
@endphp

<section class="page-hero">
    <p class="eyebrow">Modul C</p>
    <h1>Controller</h1>
    <p class="lead" style="color:#d5e8df; max-width: 70ch;">
        Controller menerima request yang sudah lolos middleware, memanggil logika aplikasi, lalu
        mengembalikan response. Modul ini membahas controller biasa, CRUD resource, validasi,
        route model binding, dependency injection, dan jenis response.
    </p>
    <div class="hero-actions">
        <a class="btn" href="#crud">Coba CRUD API</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('web.products.index') }}">Buka CRUD web</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.index') }}">Code Review</a>
    </div>
</section>

<x-correction-note>
    <p><strong>Koreksi materi:</strong> update memakai <code>PUT</code> atau <code>PATCH</code>, bukan <code>PUT/UPDATE</code>. <code>UPDATE</code> bukan HTTP method.</p>
    <p><strong>Catatan:</strong> browser mengikuti redirect secara otomatis, jadi status 302 jarang terlihat di tab Network. Panel response memakai endpoint inspector agar status dan header asli tetap terlihat.</p>
    <p><strong>Tanpa database:</strong> <code>Product $product</code> tetap memakai route model binding karena <code>Product</code> mengimplementasikan <code>UrlRoutable</code>.</p>
</x-correction-note>

<section class="section-head anchor-offset" id="controller-biasa">
    <p class="eyebrow">Konsep 1 · Controller biasa</p>
    <h2>Satu controller, dua jenis output</h2>
    <p><code>DashboardController@index</code> mengembalikan view; <code>@summary</code> mengembalikan JSON. Keduanya memakai service produk yang sama.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Response view HTML" purpose="Melihat controller mengembalikan view yang dirender menjadi HTML." code="ct-01" tag="View">
        <x-demo-runner path="/dashboard" method="GET" note="Panel menampilkan HTML mentah yang cukup panjang; perhatikan Content-Type text/html." />
        <p><a class="code-link" href="{{ route('dashboard') }}">Buka halaman dashboard →</a></p>
    </x-demo-card>
    <x-demo-card title="Response JSON" purpose="Membandingkan controller yang sama dengan output JSON." code="ct-01" tag="JSON">
        <x-demo-runner path="/dashboard/summary" method="GET" note="Route ini masuk grup web, tetapi response-nya JSON." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="crud">
    <p class="eyebrow">Konsep 2 · Resource CRUD</p>
    <h2>Product studio: request API nyata</h2>
    <p>Cari, tambah, ubah, dan hapus produk. Setiap aksi menampilkan status, body, dan trace. Data berubah di state lokal dan bisa direset dari topbar.</p>
</section>

<div class="products-layout" data-product-app>
    <section class="card">
        <div class="filter-bar" style="margin-bottom:.5rem;">
            <label class="field">
                <span>Cari produk</span>
                <input type="search" placeholder="nama, kategori, deskripsi" data-product-search>
            </label>
            <button type="button" class="btn btn-small" data-product-search-run>Cari</button>
            <button type="button" class="btn btn-small btn-ghost" data-product-refresh>Refresh</button>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
                </thead>
                <tbody data-product-rows>
                    <tr><td colspan="6" class="empty-row">Memuat produk…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pagination" data-product-pagination></div>
        <p class="tiny">Delete mengembalikan 204 tanpa body. Itu perilaku yang benar, bukan response kosong yang rusak.</p>
    </section>

    <aside class="card">
        <h3 data-product-form-title>Tambah produk</h3>
        <p class="tiny" data-product-form-note>Form mengirim POST ke <code>/api/products</code>. Kolom yang kosong akan ditolak validasi.</p>
        <form class="web-form" data-product-form novalidate>
            <input type="hidden" data-product-id value="">
            <label class="field">
                <span>Nama</span>
                <input type="text" name="name" data-product-field="name" placeholder="Contoh: Kopi Susu" autocomplete="off">
            </label>
            <label class="field">
                <span>Kategori</span>
                <input type="text" name="category" data-product-field="category" placeholder="minuman / makanan / merchandise" autocomplete="off">
            </label>
            <div class="form-grid">
                <label class="field">
                    <span>Harga</span>
                    <input type="number" name="price" data-product-field="price" min="0" step="100" placeholder="15000">
                </label>
                <label class="field">
                    <span>Stok</span>
                    <input type="number" name="stock" data-product-field="stock" min="0" step="1" placeholder="10">
                </label>
            </div>
            <label class="field">
                <span>Deskripsi (opsional)</span>
                <textarea name="description" data-product-field="description" rows="3" placeholder="Deskripsi singkat"></textarea>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-small" data-product-submit>Tambah via API</button>
                <button type="button" class="btn btn-small btn-ghost" data-product-cancel>Batal edit</button>
            </div>
        </form>
        <div class="response-panel" data-product-response hidden aria-live="polite"></div>
        <p class="tiny">Tip: coba kirim nama kosong atau harga negatif untuk melihat envelope 422 dan <code>errors</code> per field.</p>
    </aside>
</div>

<div class="demo-grid" style="margin-top:1rem;">
    <x-demo-card id="validasi" title="Validasi 422" purpose="Melihat validate() menghentikan request sebelum data disimpan." code="ct-03" tag="Validasi">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"POST","path":"/api/products","body":{"name":"","category":"makanan","price":-1,"stock":0}}'>Body tidak valid</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"POST","path":"/api/products","body":{"name":"Produk dari panel","category":"makanan","price":15000,"stock":3}}'>Body valid</button>
        </div>
        <x-demo-runner path="/api/products" method="POST" :body="['name' => '', 'category' => 'makanan', 'price' => -1, 'stock' => 0]" show-body="true" editable-path="true" note="Periksa status 422, data null, dan errors per field. Reset data demo untuk mengembalikan seed." />
    </x-demo-card>

    <x-demo-card id="mass-assignment" title="Mass assignment dan $fillable" purpose="Melihat field di luar allowlist diabaikan." code="ct-10" tag="Keamanan">
        <x-demo-runner path="/api/demo/mass-assignment" method="POST" :body="['name' => 'Produk Baru', 'price' => 10000, 'is_admin' => true]" show-body="true" note="Hanya name dan price yang masuk ke daftar diterima. is_admin diabaikan." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="binding">
    <p class="eyebrow">Konsep 3 · Route Model Binding</p>
    <h2>Product $product di-resolusi otomatis</h2>
    <p>Implicit binding memanggil <code>resolveRouteBinding()</code>. Jika hasilnya null, Laravel menghasilkan 404 sebelum method controller dijalankan.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Binding otomatis" purpose="Melihat perbedaan 200 dan 404 pada route dengan Product $product." code="ct-04" tag="Binding">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/products/1"}'>Produk #1</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/products/999"}'>Produk #999 → 404</button>
        </div>
        <x-demo-runner path="/api/products/1" method="GET" editable-path="true" note="Bandingkan dengan pencarian manual di kartu sebelah." />
    </x-demo-card>

    <x-demo-card title="Pencarian manual string $id" purpose="Melihat controller harus menangani null sendiri saat tidak memakai binding." code="ct-04" tag="Manual">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/products-by-id/1"}'>Manual #1</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/products-by-id/999"}'>Manual #999</button>
        </div>
        <x-demo-runner path="/api/demo/products-by-id/1" method="GET" editable-path="true" note="Route ini menerima string lalu mencari lewat ProductService." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="parameter-opsional">
    <p class="eyebrow">Konsep 4 · Parameter opsional</p>
    <h2>{category?} boleh tidak diisi</h2>
    <p>Satu route melayani semua produk dan filter kategori. Method menerima <code>?string $category = null</code>.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Tanpa kategori" purpose="Melihat parameter opsional bernilai null." code="ct-07" tag="Opsional">
        <x-demo-runner path="/api/demo/products-by-category" method="GET" editable-path="true" note="Controller mengembalikan semua produk." />
    </x-demo-card>
    <x-demo-card title="Dengan kategori" purpose="Membandingkan respons saat parameter diisi." code="ct-07" tag="Opsional">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/products-by-category/minuman"}'>minuman</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/products-by-category/tidak-ada"}'>tidak-ada</button>
        </div>
        <x-demo-runner path="/api/demo/products-by-category/minuman" method="GET" editable-path="true" note="Kategori tidak ada menghasilkan 200 dengan array kosong, bukan 404." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="put-patch">
    <p class="eyebrow">Konsep 5 · PUT, PATCH, sometimes</p>
    <h2>Update parsial dan body kosong</h2>
    <p>Route <code>apiResource</code> mengarahkan PUT dan PATCH ke method update yang sama. Aturan <code>sometimes</code> membuat field yang tidak dikirim dilewati.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="PATCH body kosong" purpose="Memahami bahwa sometimes melewati validasi field yang tidak ada." code="ct-08" tag="PATCH">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"PATCH","path":"/api/products/1","body":{}}'>Body {}</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"PATCH","path":"/api/products/1","body":{"name":""}}'>name kosong → 422</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"PATCH","path":"/api/products/1","body":{"stock":99}}'>Hanya stok</button>
        </div>
        <x-demo-runner path="/api/products/1" method="PATCH" :body="['stock' => 99]" show-body="true" editable-path="true" note="Coba juga PUT dengan body parsial; route yang sama akan dijalankan." />
    </x-demo-card>

    <x-demo-card title="PUT sebagai full replacement" purpose="Membandingkan semantik PUT dan PATCH pada endpoint demo terpisah." code="ct-08" tag="PUT">
        <x-demo-runner path="/api/demo/semantic-put/1" method="PUT" :body="['name' => 'Kopi Arabika Gayo', 'category' => 'minuman', 'price' => 85000, 'stock' => 12, 'description' => 'Diperbarui dengan PUT']" show-body="true" editable-path="true" note="PUT demo mewajibkan semua field karena dianggap penggantian penuh." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="di">
    <p class="eyebrow">Konsep 6 · Dependency Injection</p>
    <h2>Service lewat constructor, Request lewat method</h2>
    <p>Controller tidak membuat <code>new ProductService()</code> sendiri. Container menyuntikkan instance yang dibutuhkan.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Constructor + method injection" purpose="Melihat class yang diinjeksi dan hasil pencarian." code="ct-05" tag="DI">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/di?q=kopi"}'>q=kopi</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/di?q=tidak-ada"}'>q=tidak-ada</button>
        </div>
        <x-demo-runner path="/api/demo/di?q=kopi" method="GET" editable-path="true" note="Perhatikan meta constructor_injection dan method_injection." />
    </x-demo-card>
    <x-demo-card title="Search service" purpose="Melihat Request dan ProductService bekerja bersama dalam satu method." code="ct-05" tag="DI">
        <x-demo-runner path="/api/demo/search?q=kopi" method="GET" editable-path="true" note="Keyword kosong mengembalikan semua produk." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="response">
    <p class="eyebrow">Konsep 7 · Jenis response</p>
    <h2>Lima response, lima header</h2>
    <p>Klik tombol “Periksa header” untuk melihat status dan header target tanpa diikuti browser. Tombol “Buka” membuka response asli.</p>
</section>

<div class="card" data-response-demo>
    <div class="quick-examples">
        <button type="button" class="btn btn-small btn-ghost" data-response-type="view">Periksa view</button>
        <button type="button" class="btn btn-small btn-ghost" data-response-type="json">Periksa JSON</button>
        <button type="button" class="btn btn-small btn-ghost" data-response-type="redirect">Periksa redirect</button>
        <button type="button" class="btn btn-small btn-ghost" data-response-type="download">Periksa download</button>
        <button type="button" class="btn btn-small btn-ghost" data-response-type="no-content">Periksa 204</button>
    </div>
    <div class="quick-examples" style="margin-top:.5rem;">
        <a class="btn btn-small btn-ghost" href="{{ url('/api/demo/response/view') }}" target="_blank" rel="noreferrer">Buka view</a>
        <a class="btn btn-small btn-ghost" href="{{ url('/api/demo/response/json') }}" target="_blank" rel="noreferrer">Buka JSON</a>
        <a class="btn btn-small btn-ghost" href="{{ url('/api/demo/response/redirect') }}" target="_blank" rel="noreferrer">Buka redirect</a>
        <a class="btn btn-small btn-ghost" href="{{ url('/api/demo/response/download') }}" target="_blank" rel="noreferrer">Buka download</a>
        <a class="btn btn-small btn-ghost" href="{{ url('/api/demo/response/no-content') }}" target="_blank" rel="noreferrer">Buka 204</a>
    </div>
    <div class="response-result" data-response-result hidden aria-live="polite"></div>
    <p class="tiny">Status 204 memang tidak punya body. Untuk redirect, header <code>Location</code> menunjukkan tujuan; browser akan mengikutinya otomatis.</p>
</div>

<section class="section-head anchor-offset" id="resource">
    <p class="eyebrow">Konsep 8 · Resource route</p>
    <h2>Web resource vs API resource</h2>
    <p>Route di bawah dibaca dari aplikasi yang sedang berjalan. Perhatikan update memakai <strong>PUT|PATCH</strong>, dan web resource punya tambahan create/edit.</p>
</section>

<div class="card">
    <div class="resource-tabs" data-tabs>
        <button type="button" class="tab-btn is-active" data-tab-button="web-resource" aria-selected="true">Web resource ({{ count($webResourceRoutes) }})</button>
        <button type="button" class="tab-btn" data-tab-button="api-resource" aria-selected="false">API resource ({{ count($apiResourceRoutes) }})</button>
    </div>

    <div data-tab-panel="web-resource">
        <div class="table-wrap">
            <table class="route-table">
                <thead><tr><th>Method</th><th>URI</th><th>Nama</th></tr></thead>
                <tbody>
                    @foreach ($webResourceRoutes as $route)
                        <tr>
                            <td><div class="middleware-list">@foreach ($route['methods'] as $method)<span class="method-badge method-{{ $method }}">{{ $method }}</span>@endforeach</div></td>
                            <td class="uri">{{ $route['uri'] }}</td>
                            <td class="mono tiny">{{ $route['name'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="tiny"><code>Route::resource</code> membuat 7 route. Form HTML memakai POST + <code>_method</code> untuk update/delete.</p>
    </div>

    <div data-tab-panel="api-resource" hidden>
        <div class="table-wrap">
            <table class="route-table">
                <thead><tr><th>Method</th><th>URI</th><th>Nama</th></tr></thead>
                <tbody>
                    @foreach ($apiResourceRoutes as $route)
                        <tr>
                            <td><div class="middleware-list">@foreach ($route['methods'] as $method)<span class="method-badge method-{{ $method }}">{{ $method }}</span>@endforeach</div></td>
                            <td class="uri">{{ $route['uri'] }}</td>
                            <td class="mono tiny">{{ $route['name'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="tiny"><code>Route::apiResource</code> membuat 5 route tanpa create/edit karena API tidak merender form.</p>
    </div>
</div>

<section class="section-head">
    <p class="eyebrow">Terminal</p>
    <h2>Uji controller dari terminal</h2>
    <p>Semua endpoint di bawah tidak memakai CSRF karena berada di grup API. Jangan lupa header <code>Accept: application/json</code> saat mengirim body.</p>
</section>
<x-terminal-commands :commands="$controllerCommands" />

<section style="margin-top:2rem;">
    <x-faq-list :items="$faqs" />
</section>

<x-correction-note title="Ingat">
    <p>Status 204 dari destroy bukan response rusak. Status 422 berarti validasi gagal dan controller tidak menyimpan data.
    Status 404 dari route model binding berarti Laravel tidak menemukan model sebelum controller dijalankan.</p>
</x-correction-note>
@endsection

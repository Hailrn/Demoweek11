@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<section class="page-hero">
    <div class="hero-grid">
        <div>
            <p class="eyebrow">Demo pembelajaran interaktif · Laravel</p>
            <h1>Route, Middleware &amp; Controller</h1>
            <p class="lead" style="color:#d5e8df; max-width: 62ch;">
                Belajar alur request Laravel dengan mencoba langsung: pilih route, kirim request,
                lihat status dan trace, lalu buka bagian kode yang menjelaskannya.
            </p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('learn.route') }}">Mulai dari Route</a>
                <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.index') }}">Buka Code Review</a>
                <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('faq') }}">FAQ interaktif</a>
            </div>
        </div>
        <aside class="hero-note">
            <h2>Tiga langkah</h2>
            <ol class="tiny-list" style="color:#d5e8df; padding-left:1.2rem;">
                <li>Pilih peran demo di sidebar.</li>
                <li>Kirim request dari panel <em>Coba</em>.</li>
                <li>Baca status, body, trace, lalu buka kode.</li>
            </ol>
            <p style="margin-bottom:0;">Tidak perlu database, Node, npm, atau koneksi internet.</p>
        </aside>
    </div>
</section>

<div class="stat-grid" aria-label="Ringkasan demo">
    <div class="stat">
        <div class="value">{{ number_format($routeCount) }}</div>
        <div class="label">Route terdaftar</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($reviewCount) }}</div>
        <div class="label">Titik Code Review</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($productCount) }}</div>
        <div class="label">Produk seed (array lokal)</div>
    </div>
    <div class="stat">
        <div class="value">0</div>
        <div class="label">Tabel database</div>
    </div>
</div>

<div class="section-head">
    <p class="eyebrow">Peta materi</p>
    <h2>Tiga modul, satu alur request</h2>
    <p>Setiap modul punya panel coba, contoh terminal, FAQ, dan tautan ke baris kode aslinya.</p>
</div>

<div class="module-grid">
    <article class="module-card">
        <span class="letter">A</span>
        <h3>Route</h3>
        <p>Method HTTP, match/any, urutan route, parameter, constraint, resource route, sampai method spoofing.</p>
        <a href="{{ route('learn.route') }}">Buka Modul Route →</a>
    </article>
    <article class="module-card">
        <span class="letter">B</span>
        <h3>Middleware</h3>
        <p>Alias, role, auth, group, urutan, stop sebelum controller, global, throttle, dan signed URL.</p>
        <a href="{{ route('learn.middleware') }}">Buka Modul Middleware →</a>
    </article>
    <article class="module-card">
        <span class="letter">C</span>
        <h3>Controller</h3>
        <p>Controller biasa, CRUD, validasi, route model binding, dependency injection, dan lima jenis response.</p>
        <a href="{{ route('learn.controller') }}">Buka Modul Controller →</a>
    </article>
</div>

<div class="section-head">
    <p class="eyebrow">Latihan nyata</p>
    <h2>Bukan hanya tampilan: request benar-benar dikirim</h2>
</div>
<div class="demo-grid">
    <article class="card">
        <p class="eyebrow">Data lokal</p>
        <h3>CRUD tanpa database</h3>
        <p class="muted">Produk dibaca dari seed PHP, lalu perubahan disimpan di JSON dengan penguncian file. Reset kapan saja dari tombol di atas.</p>
        <p><a class="code-link" href="{{ route('web.products.index') }}">Coba CRUD web →</a></p>
    </article>
    <article class="card">
        <p class="eyebrow">Role demo</p>
        <h3>401, 403, dan 200</h3>
        <p class="muted">Ganti peran lewat sidebar atau header <code class="code-inline">X-Demo-Role</code>. Middleware role akan menunjukkan lapisan yang berhenti.</p>
        <p><a class="code-link" href="{{ route('learn.middleware') }}#group-admin">Buka matriks role →</a></p>
    </article>
    <article class="card">
        <p class="eyebrow">Transparansi</p>
        <h3>Trace setiap respons</h3>
        <p class="muted">Setiap respons sukses/error membawa header <code class="code-inline">X-Demo-Trace</code>. Panel hasil menerjemahkannya menjadi urutan lapisan.</p>
        <p><a class="code-link" href="{{ route('code-review.show', 'mw-07') }}">Lihat middleware trace →</a></p>
    </article>
</div>

<div class="section-head">
    <p class="eyebrow">Jalankan sendiri</p>
    <h2>Setup tiga langkah</h2>
    <p>Untuk clone bersih, cukup tiga perintah. Tidak ada migrasi atau build front-end.</p>
</div>
<x-terminal-commands :commands="[
    'Linux / macOS' => 'composer install'.PHP_EOL.'composer setup-demo'.PHP_EOL.'php artisan serve',
    'Windows CMD' => 'composer install'.PHP_EOL.'composer setup-demo'.PHP_EOL.'php artisan serve',
    'Windows PowerShell' => 'composer install'.PHP_EOL.'composer setup-demo'.PHP_EOL.'php artisan serve',
]" />

<div class="notice">
    <strong>Penting:</strong> ini proyek belajar lokal. Role, token, dan URL bertanda tangan di sini adalah simulasi.
    Jangan deploy apa adanya dengan <code>APP_DEBUG=true</code>.
</div>
@endsection

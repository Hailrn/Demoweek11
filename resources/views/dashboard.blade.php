@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="page-hero">
    <div class="hero-grid">
        <div>
            <p class="eyebrow">Controller biasa · response view</p>
            <h1>Dashboard</h1>
            <p class="lead" style="color:#d5e8df;">
                Halaman ini dikembalikan sebagai <strong>view HTML</strong> dari
                <code>DashboardController@index</code>. Endpoint <code>/dashboard/summary</code>
                memakai data yang sama tetapi mengembalikan JSON.
            </p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('dashboard.summary') }}" target="_blank" rel="noreferrer">Buka JSON summary</a>
                <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.show', 'ct-01') }}">Lihat Controller</a>
            </div>
        </div>
        <aside class="hero-note">
            <h2>Peran aktif</h2>
            <p><strong>{{ config('demo.roles.'.session('demo_role', 'guest').'.label', 'Tamu') }}</strong></p>
            <p class="tiny">Sidebar mengubah session web. Endpoint API membaca <code>X-Demo-Role</code> atau Bearer token demo.</p>
        </aside>
    </div>
</section>

<div class="stat-grid" aria-label="Ringkasan produk">
    <div class="stat"><div class="value">{{ number_format($summary['total_products']) }}</div><div class="label">Total produk</div></div>
    <div class="stat"><div class="value">{{ number_format($summary['total_stock']) }}</div><div class="label">Total stok</div></div>
    <div class="stat"><div class="value">Rp {{ number_format($summary['inventory_value'], 0, ',', '.') }}</div><div class="label">Nilai inventaris</div></div>
    <div class="stat"><div class="value">{{ $summary['total_categories'] }}</div><div class="label">Kategori</div></div>
</div>

<section class="card">
    <div class="section-head" style="margin-top:0;">
        <p class="eyebrow">Data nyata dari state lokal</p>
        <h2>Lima produk terbaru</h2>
        <p>Data ini dibaca dari <code class="code-inline">storage/app/demo/state.json</code>, bukan database.</p>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th></tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="mono">#{{ $product->id }}</td>
                        <td><a href="{{ route('web.products.show', $product->id) }}">{{ $product->name }}</a></td>
                        <td>{{ $product->category }}</td>
                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-row">Belum ada produk. Klik Reset data demo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="section-actions">
        <a class="btn" href="{{ route('web.products.index') }}">Kelola produk</a>
        <a class="btn btn-ghost" href="{{ route('learn.controller') }}#crud">Coba CRUD API</a>
    </div>
</section>

<div class="demo-grid" style="margin-top:1rem;">
    <x-demo-card title="View atau JSON?" purpose="Satu controller, dua method, dua jenis response." code="ct-01" tag="Response">
        <x-demo-runner path="/dashboard/summary" method="GET" note="Perhatikan Content-Type application/json pada panel hasil." />
    </x-demo-card>
    <x-demo-card title="Middleware global ikut berjalan" purpose="Header X-Demo-Global dan X-Demo-Trace selalu ditambahkan oleh middleware global." code="mw-07" tag="Trace">
        <x-demo-runner path="/api/demo/global-public" method="GET" note="Endpoint API ini tetap melewati middleware global." />
    </x-demo-card>
</div>
@endsection

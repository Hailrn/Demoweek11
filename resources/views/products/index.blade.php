@extends('layouts.app')

@section('title', 'CRUD web · Produk')

@section('content')
<section class="page-hero">
    <div class="hero-grid">
        <div>
            <p class="eyebrow">Route::resource · web</p>
            <h1>CRUD web</h1>
            <p class="lead" style="color:#d5e8df;">
                Halaman ini memakai <strong>7 route web resource</strong> dan merender view.
                Untuk melihat status API mentah (201, 422, 204), gunakan Product studio di Modul Controller.
            </p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('web.products.create') }}">Tambah produk</a>
                <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('learn.controller') }}#crud">Buka CRUD API</a>
            </div>
        </div>
        <aside class="hero-note">
            <h2>Tanpa database</h2>
            <p>{{ number_format($summary['total_products']) }} produk · {{ number_format($summary['total_stock']) }} stok · nilai Rp {{ number_format($summary['inventory_value'], 0, ',', '.') }}</p>
            <p class="tiny">State disimpan dengan flock di <span class="mono">storage/app/demo/state.json</span>. Tombol reset ada di topbar.</p>
        </aside>
    </div>
</section>

@if ($errors->any())
    <div class="validation-errors" role="alert" style="margin-top:1rem;">
        <strong>Periksa input:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="card" style="margin-top:1rem;">
    <div class="section-actions" style="margin-top:0;justify-content:space-between;align-items:center;">
        <div>
            <p class="eyebrow">Halaman {{ $paginator['page'] }} dari {{ $paginator['last_page'] }}</p>
            <h2 style="margin:0;">Daftar produk</h2>
        </div>
        <a class="btn" href="{{ route('web.products.create') }}">+ Produk baru</a>
    </div>

    <div class="table-wrap" style="margin-top:.7rem;">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($paginator['items'] as $product)
                    <tr>
                        <td class="mono">#{{ $product->id }}</td>
                        <td><a href="{{ route('web.products.show', $product->id) }}">{{ $product->name }}</a><div class="table-sub">{{ \Illuminate\Support\Str::limit($product->description, 64) }}</div></td>
                        <td><span class="chip">{{ $product->category }}</span></td>
                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            <div class="product-actions">
                                <a class="btn btn-small btn-ghost" href="{{ route('web.products.show', $product->id) }}">Lihat</a>
                                <a class="btn btn-small btn-ghost" href="{{ route('web.products.edit', $product->id) }}">Edit</a>
                                <form action="{{ route('web.products.destroy', $product->id) }}" method="POST" data-confirm="Hapus produk {{ $product->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-row">Belum ada produk. Tambah produk atau reset data demo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <nav class="pagination" aria-label="Navigasi halaman produk">
        @if ($paginator['page'] > 1)
            <a href="{{ route('web.products.index', ['page' => $paginator['page'] - 1]) }}">← Sebelumnya</a>
        @endif
        @for ($page = 1; $page <= $paginator['last_page']; $page++)
            @if ($page === $paginator['page'])
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ route('web.products.index', ['page' => $page]) }}">{{ $page }}</a>
            @endif
        @endfor
        @if ($paginator['page'] < $paginator['last_page'])
            <a href="{{ route('web.products.index', ['page' => $paginator['page'] + 1]) }}">Berikutnya →</a>
        @endif
        <span class="tiny">{{ $paginator['total'] }} total · {{ $paginator['per_page'] }} per halaman</span>
    </nav>
</section>

<div class="notice">
    <strong>Catatan request/response web:</strong> POST form berhasil menghasilkan redirect (302) ke halaman detail,
    lalu browser mengikuti redirect dan menampilkan 200. Flash message disimpan di session. Endpoint API di Modul C
    menunjukkan status 201/422/204 tanpa redirect.
</div>

<x-correction-note title="Koreksi materi">
    <p>Route update resource selalu memakai <code>PUT|PATCH</code>. Tidak ada HTTP method bernama <code>UPDATE</code>.</p>
</x-correction-note>
@endsection

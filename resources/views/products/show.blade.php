@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="section-actions" style="margin-top:0;">
    <a href="{{ route('web.products.index') }}">← Daftar produk</a>
    <span class="chip">Route Model Binding</span>
    <span class="chip mono">#{{ $product->id }}</span>
</div>

<div class="demo-grid" style="margin-top:.7rem;">
    <section class="card">
        <p class="eyebrow">Detail produk</p>
        <h1 style="font-size:clamp(1.8rem,3vw,2.6rem);">{{ $product->name }}</h1>
        <p class="lead">{{ $product->description ?: 'Tidak ada deskripsi.' }}</p>
        <div class="stat-grid">
            <div class="stat"><div class="value">Rp {{ number_format($product->price, 0, ',', '.') }}</div><div class="label">Harga</div></div>
            <div class="stat"><div class="value">{{ $product->stock }}</div><div class="label">Stok</div></div>
            <div class="stat"><div class="value">{{ $product->category }}</div><div class="label">Kategori</div></div>
        </div>
        <div class="product-actions">
            <a class="btn btn-small" href="{{ route('web.products.edit', $product->id) }}">Edit produk</a>
            <form action="{{ route('web.products.destroy', $product->id) }}" method="POST" data-confirm="Hapus produk {{ $product->name }}?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-small btn-danger">Hapus</button>
            </form>
        </div>
    </section>

    <aside class="card">
        <p class="eyebrow">Di balik layar</p>
        <h3>Bagaimana produk ditemukan?</h3>
        <p>Route <code class="code-inline">/web/products/{product}</code> membuat Laravel memanggil
            <code>Product::resolveRouteBinding()</code>. Method itu mencari lewat <code>DemoStore</code>.
            Jika tidak ada, Laravel mengembalikan 404 sebelum view dirender.</p>
        <dl class="kv">
            <dt>ID</dt><dd>{{ $product->id }}</dd>
            <dt>Dibuat</dt><dd class="mono">{{ $product->created_at ?? '—' }}</dd>
            <dt>Diubah</dt><dd class="mono">{{ $product->updated_at ?? '—' }}</dd>
            <dt>State</dt><dd class="mono">storage/app/demo/state.json</dd>
        </dl>
        <div class="section-actions">
            <a class="btn btn-small btn-ghost" href="{{ route('code-review.show', 'ct-04') }}">Lihat binding</a>
            <a class="btn btn-small btn-ghost" href="{{ url('/api/products/'.$product->id) }}" target="_blank" rel="noreferrer">Response JSON</a>
        </div>
    </aside>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit produk')

@section('content')
<div class="products-layout">
    <section class="card">
        <p class="eyebrow">Route::resource · edit</p>
        <h1>Edit produk</h1>
        <p class="muted">Form mengirim POST dengan field <code class="code-inline">_method=PUT</code>. Browser tidak bisa mengirim PUT secara native dari form HTML.</p>

        <form action="{{ route('web.products.update', $product->id) }}" method="POST" class="web-form">
            @csrf
            @method('PUT')
            @include('products._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </section>
    <aside class="card">
        <p class="eyebrow">Produk aktif</p>
        <h2>#{{ $product->id }}</h2>
        <p>{{ $product->name }}</p>
        <dl class="kv">
            <dt>Kategori</dt><dd>{{ $product->category }}</dd>
            <dt>Harga</dt><dd>Rp {{ number_format($product->price, 0, ',', '.') }}</dd>
            <dt>Stok</dt><dd>{{ $product->stock }}</dd>
            <dt>Dibuat</dt><dd>{{ $product->created_at ?? '—' }}</dd>
            <dt>Diubah</dt><dd>{{ $product->updated_at ?? '—' }}</dd>
        </dl>
        <div class="section-actions">
            <a class="btn btn-small btn-ghost" href="{{ route('web.products.show', $product->id) }}">Lihat detail</a>
            <a class="btn btn-small btn-ghost" href="{{ route('web.products.index') }}">Kembali</a>
        </div>
    </aside>
</div>
@endsection

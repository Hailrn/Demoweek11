@extends('layouts.app')

@section('title', 'Tambah produk')

@section('content')
<div class="products-layout">
    <section class="card">
        <p class="eyebrow">Route::resource · create</p>
        <h1>Tambah produk</h1>
        <p class="muted">Form mengirim POST ke <code class="code-inline">{{ route('web.products.store') }}</code>.
            Controller memvalidasi, menyimpan ke state JSON, lalu redirect ke halaman detail.</p>

        <form action="{{ route('web.products.store') }}" method="POST" class="web-form">
            @csrf
            @include('products._form', ['submitLabel' => 'Simpan produk'])
        </form>
    </section>
    <aside class="card">
        <p class="eyebrow">Yang terjadi</p>
        <h3>Alur request web</h3>
        <ol class="experiment-list">
            <li>GET <code>/web/products/create</code> menampilkan form.</li>
            <li>POST <code>/web/products</code> mengirim field dan token CSRF.</li>
            <li>Validasi gagal → redirect kembali dengan errors.</li>
            <li>Validasi lolos → 302 ke <code>/web/products/{id}</code>.</li>
            <li>Browser mengikuti redirect → 200 halaman detail.</li>
        </ol>
        <p class="tiny">Bandingkan dengan API: status 201 terlihat langsung tanpa redirect.</p>
        <p><a class="code-link" href="{{ route('code-review.show', 'rt-05') }}">Lihat route resource →</a></p>
    </aside>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Code Review')

@section('content')
<section class="page-hero">
    <p class="eyebrow">Referensi kode</p>
    <h1>Code Review</h1>
    <p class="lead" style="color:#d5e8df; max-width: 68ch;">
        Setiap titik review menunjuk ke region kode asli lewat id, bukan nomor baris.
        Jadi tautan tetap stabil saat file berubah. Halaman ini hanya membaca allowlist
        <code>app/</code>, <code>routes/</code>, <code>docs/</code>, <code>tests/</code>, dan beberapa file konfigurasi.
    </p>
</section>

<div class="code-review-layout" style="margin-top:1rem;">
    <aside class="code-review-nav" aria-label="Daftar titik review">
        <strong>Urutan baca</strong>
        @foreach ($readingOrder as $step)
            @php($item = $points->firstWhere('id', $step['id']))
            @if ($item)
                <a href="{{ route('code-review.show', $item['id']) }}">
                    <span class="mono">{{ $item['id'] }}</span> · {{ $item['judul'] }}
                </a>
            @endif
        @endforeach
    </aside>

    <div>
        <div class="notice">
            <strong>{{ $points->count() }} titik review</strong> tersebar di {{ $grouped->count() }} modul.
            Kode inti materi sengaja dibuat dekat dengan contoh, sedangkan kode demo tambahan dipisahkan.
        </div>

        @foreach ($grouped as $module => $items)
            <section class="card" style="margin-bottom:1rem;">
                <div class="section-head" style="margin-top:0;">
                    <p class="eyebrow">{{ $module }}</p>
                    <h2>{{ $module }}</h2>
                    <p>{{ $items->count() }} titik review. Buka salah satu untuk membaca region kode dan eksperimennya.</p>
                </div>
                <div class="point-list">
                    @foreach ($items as $point)
                        <article class="card point-card" style="box-shadow:none; padding:.8rem;">
                            <div>
                                <h3><a href="{{ route('code-review.show', $point['id']) }}">{{ $point['judul'] }}</a></h3>
                                <p>{{ $point['tujuan'] }}</p>
                                <p class="point-meta">{{ $point['file'] }} · {{ $point['id'] }}</p>
                            </div>
                            <a class="btn btn-small btn-ghost" href="{{ route('code-review.show', $point['id']) }}">Baca →</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection

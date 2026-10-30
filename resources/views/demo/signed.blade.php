@extends('layouts.app')

@section('title', 'Signed URL valid')

@section('content')
<section class="page-hero">
    <p class="eyebrow">Middleware bawaan · signed</p>
    <h1>URL bertanda tangan valid</h1>
    <p class="lead" style="color:#d5e8df;">
        Middleware <code>signed</code> menerima request ini karena signature dan waktu kedaluwarsa cocok.
        Coba ubah satu karakter pada signature di address bar; Laravel akan menolak dengan 403.
    </p>
</section>

<section class="card" style="margin-top:1rem;">
    <p class="eyebrow">Query yang diterima</p>
    <dl class="kv">
        <dt>expires</dt><dd class="mono">{{ $expires ?? '—' }}</dd>
        <dt>signature</dt><dd class="mono" style="word-break:break-all;">{{ $signature ?? '—' }}</dd>
    </dl>
    <div class="notice">Signature bergantung pada URL dan expires. Ini bukan autentikasi user; URL tetap bisa dibagikan ke orang lain sampai kedaluwarsa.</div>
    <div class="section-actions">
        <a class="btn" href="{{ route('learn.middleware') }}#signed">Kembali ke demo signed</a>
        <a class="btn btn-ghost" href="{{ route('learn.middleware') }}#signed">Coba URL tampered</a>
    </div>
</section>
@endsection

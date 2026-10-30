@extends('layouts.app')

@section('title', 'FAQ interaktif')

@section('content')
<section class="page-hero">
    <p class="eyebrow">Kalau begini gimana?</p>
    <h1>FAQ interaktif</h1>
    <p class="lead" style="color:#d5e8df; max-width: 68ch;">
        Setiap pertanyaan bisa langsung dicoba. Panel akan mengirim request nyata ke aplikasi ini,
        menampilkan status, body, dan trace, lalu memberi tautan ke kode yang berperan.
    </p>
</section>

<div class="notice" style="margin-top:1rem;">
    Peran demo bisa diubah dari sidebar. Untuk API, panel FAQ mengirim header <code>X-Demo-Role</code>
    sesuai skenario; tidak ada autentikasi produksi di proyek ini.
</div>

@foreach ([
    'route' => ['judul' => 'Modul A · Route', 'route' => 'learn.route', 'warna' => 'A'],
    'middleware' => ['judul' => 'Modul B · Middleware', 'route' => 'learn.middleware', 'warna' => 'B'],
    'controller' => ['judul' => 'Modul C · Controller', 'route' => 'learn.controller', 'warna' => 'C'],
] as $group => $meta)
    <div class="section-head">
        <p class="eyebrow">Modul {{ $meta['warna'] }}</p>
        <h2>{{ $meta['judul'] }}</h2>
        <p>Pertanyaan, skenario yang bisa dijalankan, dan penjelasan singkat. <a href="{{ route($meta['route']) }}">Buka modul →</a></p>
    </div>
    <x-faq-list :items="$groups[$group] ?? []" />
@endforeach

<x-correction-note>
    <p><strong>Koreksi materi sumber:</strong> contoh di materi menulis <code>auth.bacis</code>; nama middleware Laravel yang benar adalah <code>auth.basic</code>. Proyek demo ini memakai alias <code>auth</code> yang diarahkan ke middleware simulasi karena tidak ada database user.</p>
    <p>Selain itu, update HTTP memakai <code>PUT</code> atau <code>PATCH</code>, bukan <code>PUT/UPDATE</code>. <code>UPDATE</code> bukan HTTP method.</p>
</x-correction-note>
@endsection

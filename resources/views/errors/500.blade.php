@extends('layouts.error')

@section('title', 'Terjadi kesalahan pada server demo')
@section('code', '500')
@section('message', 'Stack trace mentah tidak ditampilkan di halaman demo. Periksa log lokal bila Anda sedang mengembangkan aplikasi.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        Untuk API, error tetap dikirim sebagai JSON dengan envelope <code>{status, message, data}</code>.
    </div>
@endsection

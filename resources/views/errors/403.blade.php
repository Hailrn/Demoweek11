@extends('layouts.error')

@section('title', 'Akses ditolak')
@section('code', '403')
@section('message', 'User sudah dikenali, tetapi perannya tidak ada dalam daftar role yang diizinkan. Ini bukan masalah login, melainkan kewenangan.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        Coba peran lain di sidebar atau bandingkan dengan FAQ “Kalau kasir membuka laporan?”.
    </div>
@endsection

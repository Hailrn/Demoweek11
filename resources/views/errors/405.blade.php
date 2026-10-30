@extends('layouts.error')

@section('title', 'Method tidak diizinkan')
@section('code', '405')
@section('message', 'URI ditemukan, tetapi HTTP method tidak cocok dengan route yang terdaftar. Header Allow biasanya menyebutkan method yang tersedia.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        Coba ganti method di panel demo, misalnya POST ke route GET, lalu baca header <code>Allow</code>.
    </div>
@endsection

@extends('layouts.error')

@section('title', 'Halaman atau resource tidak ditemukan')
@section('code', '404')
@section('message', 'Route tidak cocok, atau resource yang dicari tidak ada. Untuk API, body JSON tetap memakai envelope status/message/data.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        <strong>404 vs 405:</strong> 404 berarti URI tidak cocok; 405 berarti URI ada tetapi HTTP method-nya tidak diizinkan.
    </div>
@endsection

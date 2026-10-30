@extends('layouts.error')

@section('title', 'Perlu autentikasi')
@section('code', '401')
@section('message', 'Request belum memiliki user. Pilih peran demo lewat Role Switcher atau kirim header X-Demo-Role, lalu coba lagi.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        <strong>401 vs 403:</strong> 401 berarti belum terautentikasi; 403 berarti user sudah ada tetapi perannya tidak diizinkan.
    </div>
@endsection

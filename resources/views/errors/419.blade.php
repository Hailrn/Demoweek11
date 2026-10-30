@extends('layouts.error')

@section('title', 'Token CSRF tidak cocok')
@section('code', '419')
@section('message', 'Request web mengubah data tanpa token CSRF yang valid. Route API tidak memakai CSRF; route web yang mengubah data memerlukannya.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        Buka demo CSRF di Modul Route untuk membandingkan request tanpa token dan dengan token.
    </div>
@endsection

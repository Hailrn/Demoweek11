@extends('layouts.app')

@section('title', 'Response view')

@section('content')
<div class="page-hero">
    <p class="eyebrow">Response HTML</p>
    <h1>{{ $message }}</h1>
    <p style="color:#d5e8df;">Route <code>/api/demo/response/view</code> mengembalikan view ini dengan Content-Type <code>text/html</code>. Endpoint ini tetap berada di grup API, jadi JSON error dan middleware API tetap berlaku.</p>
    <div class="hero-actions">
        <a class="btn" href="{{ route('learn.controller') }}#response">Bandingkan lima response</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.show', 'ct-06') }}">Lihat kode</a>
    </div>
</div>
@endsection

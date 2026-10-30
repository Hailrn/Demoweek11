@extends('layouts.error')

@section('title', 'Terlalu banyak request')
@section('code', '429')
@section('message', 'Batas throttle terlampaui. Tunggu sesuai header Retry-After, atau reset skenario dengan menunggu satu menit.')
@section('detail')
    <div class="notice" style="margin-top:1rem;">
        Demo throttle memakai <code>throttle:5,1</code>: lima request per menit untuk key yang sama.
    </div>
@endsection

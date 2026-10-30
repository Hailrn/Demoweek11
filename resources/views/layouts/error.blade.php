<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi kesalahan') · {{ config('demo.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="error-page">
    <main class="error-card">
        <p class="eyebrow">Demo Route, Middleware &amp; Controller</p>
        <div class="error-code">@yield('code', 'Error')</div>
        <h1>@yield('title', 'Terjadi kesalahan')</h1>
        <p>@yield('message', 'Request tidak bisa diproses.')</p>
        @yield('detail')
        <div class="error-actions">
            <a class="btn" href="{{ url('/') }}">Kembali ke beranda</a>
            <a class="btn btn-ghost" href="{{ url('/faq') }}">Buka FAQ</a>
        </div>
        <p class="tiny">Halaman error ini disederhanakan agar stack trace mentah tidak ditampilkan pada demo.</p>
    </main>
</body>
</html>

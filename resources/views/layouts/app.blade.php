<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="demo-role" content="{{ session('demo_role', 'guest') }}">
    <title>@yield('title', 'Belajar') · {{ config('demo.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $activeRole = session('demo_role', 'guest');
    $roleLabel = config('demo.roles.'.$activeRole.'.label', 'Tamu');
    $roleName = config('demo.roles.'.$activeRole.'.name');
@endphp

<a class="skip-link" href="#konten">Lompat ke konten</a>

<div class="app-shell">
    <aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark">R·M·C</span>
            <span>
                <strong>Demo Laravel</strong>
                <small>Route · Middleware · Controller</small>
            </span>
        </a>

        <nav class="nav">
            <p class="nav-label">Mulai</p>
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>

            <p class="nav-label">Materi</p>
            <a href="{{ route('learn.route') }}" class="nav-link {{ request()->routeIs('learn.route') ? 'is-active' : '' }}"><span class="nav-badge">A</span> Route</a>
            <a href="{{ route('learn.middleware') }}" class="nav-link {{ request()->routeIs('learn.middleware') ? 'is-active' : '' }}"><span class="nav-badge">B</span> Middleware</a>
            <a href="{{ route('learn.controller') }}" class="nav-link {{ request()->routeIs('learn.controller') ? 'is-active' : '' }}"><span class="nav-badge">C</span> Controller</a>

            <p class="nav-label">Latihan</p>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
            <a href="{{ route('web.products.index') }}" class="nav-link {{ request()->routeIs('web.products.*') ? 'is-active' : '' }}">CRUD web</a>
            <a href="{{ route('faq') }}" class="nav-link {{ request()->routeIs('faq') ? 'is-active' : '' }}">FAQ interaktif</a>

            <p class="nav-label">Referensi</p>
            <a href="{{ route('code-review.index') }}" class="nav-link {{ request()->routeIs('code-review.*') ? 'is-active' : '' }}"><span class="nav-badge nav-badge-alt">⌘</span> Code Review</a>
        </nav>

        <div class="sidebar-role">
            <p class="eyebrow">Peran demo</p>
            <p class="role-name">{{ $roleLabel }}{{ $roleName ? ' · '.$roleName : '' }}</p>
            <form action="{{ route('demo.role') }}" method="POST" class="role-buttons">
                @csrf
                @foreach (config('demo.roles') as $key => $role)
                    <button type="submit" name="role" value="{{ $key }}" class="role-btn {{ $activeRole === $key ? 'is-active' : '' }}">
                        {{ $role['label'] }}
                    </button>
                @endforeach
            </form>
            <p class="tiny">Peran hanya simulasi, bukan autentikasi produksi.</p>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="menu-toggle" type="button" data-menu-toggle aria-controls="sidebar" aria-expanded="false">☰ Menu</button>
            <div class="topbar-meta">
                <span class="pill pill-role">Peran: <strong>{{ $roleLabel }}</strong></span>
                <span class="pill pill-soft">Tanpa database</span>
            </div>
            <form action="{{ route('demo.reset') }}" method="POST" class="reset-form">
                @csrf
                <button type="submit" class="btn btn-ghost btn-small">Reset data demo</button>
            </form>
        </header>

        @if (session('success'))
            <div class="flash flash-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error" role="alert">{{ session('error') }}</div>
        @endif

        <main id="konten" class="content">
            @yield('content')
        </main>

        <footer class="footer">
            <p>Demo lokal untuk belajar. Jangan deploy apa adanya. Sumber materi: <a href="https://adyahandika.my.id/course/pemrograman-web/routemiddleware-controller" target="_blank" rel="noreferrer">Route, Middleware &amp; Controller</a>.</p>
            <p>Tidak memakai database, Node, atau aset CDN.</p>
        </footer>
    </div>
</div>

<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>

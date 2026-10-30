@extends('layouts.app')

@section('title', 'Code Review '.$point['id'].' · '.$point['judul'])

@section('content')
@php
    $ids = array_column($points, 'id');
    $position = array_search($point['id'], $ids, true);
    $previous = $position !== false && $position > 0 ? $points[$position - 1] : null;
    $next = $position !== false && isset($points[$position + 1]) ? $points[$position + 1] : null;
    $grouped = collect($points)->groupBy('modul');
    $lines = explode("\n", $snippet['lines']);
@endphp

<div class="section-actions" style="margin-top:0;">
    <a href="{{ route('code-review.index') }}">← Semua titik review</a>
    <span class="chip">{{ $point['modul'] }}</span>
    <span class="chip mono">{{ $point['id'] }}</span>
</div>

<div class="code-review-layout" style="margin-top:.8rem;">
    <aside class="code-review-nav" aria-label="Navigasi titik review">
        @foreach ($grouped as $module => $items)
            <p class="nav-group">{{ $module }}</p>
            @foreach ($items as $item)
                <a href="{{ route('code-review.show', $item['id']) }}" @if ($item['id'] === $point['id']) aria-current="page" style="background:var(--accent-soft);color:var(--ink);font-weight:700;" @endif>
                    <span class="mono">{{ $item['id'] }}</span> · {{ \Illuminate\Support\Str::limit($item['judul'], 32) }}
                </a>
            @endforeach
        @endforeach
    </aside>

    <div>
        <section class="card">
            <p class="eyebrow">Titik review</p>
            <h1 style="font-size:clamp(1.6rem,3vw,2.35rem);">{{ $point['judul'] }}</h1>
            <p class="lead">{{ $point['tujuan'] }}</p>
            <p class="point-meta">{{ $point['file'] }} · baris {{ $snippet['start_line'] }}–{{ $snippet['end_line'] }} · total {{ $snippet['total_lines'] }} baris</p>

            <div class="section-actions">
                <a class="btn btn-small" href="{{ url($point['demo_url']) }}">Coba di GUI →</a>
                @if ($previous)
                    <a class="btn btn-small btn-ghost" href="{{ route('code-review.show', $previous['id']) }}">← {{ $previous['id'] }}</a>
                @endif
                @if ($next)
                    <a class="btn btn-small btn-ghost" href="{{ route('code-review.show', $next['id']) }}">{{ $next['id'] }} →</a>
                @endif
            </div>
            <p class="tiny">Viewer hanya menampilkan region yang terdaftar. Tidak ada endpoint untuk membaca path bebas.</p>
        </section>

        <div class="demo-grid" style="margin-top:1rem;">
            <section class="card">
                <p class="eyebrow">Yang perlu dipahami</p>
                <ul class="experiment-list">
                    @foreach ($point['yang_dilihat'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>
            <section class="card">
                <p class="eyebrow">Eksperimen</p>
                <ol class="experiment-list">
                    @foreach ($point['eksperimen'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ol>
                @if (! empty($point['faq_terkait']))
                    <p class="tiny">FAQ terkait: {{ implode('; ', $point['faq_terkait']) }}. <a href="{{ route('faq') }}">Buka FAQ →</a></p>
                @endif
            </section>
        </div>

        <section class="section-head">
            <p class="eyebrow">Region kode</p>
            <h2>{{ basename($point['file']) }}</h2>
            <p>Hanya region <code class="code-inline">{{ $point['id'] }}</code> yang ditampilkan. Nomor baris mengikuti file asli.</p>
        </section>
        <div class="snippet-wrap" aria-label="Potongan kode">
            <table class="snippet-table">
                <tbody>
                    @foreach ($lines as $index => $line)
                        <tr>
                            <td class="line-no">{{ $snippet['start_line'] + $index }}</td>
                            <td><pre>{{ $line }}</pre></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="tiny" style="margin-top:.6rem;">Sumber: <span class="mono">{{ $snippet['path'] }}</span>. Viewer menolak path traversal, file di luar allowlist, dan symlink keluar root.</p>
    </div>
</div>
@endsection

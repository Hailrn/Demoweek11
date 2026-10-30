@props(['title', 'purpose', 'code' => null, 'tag' => null])
<section {{ $attributes->merge(['class' => 'card demo-card']) }}>
    <div class="demo-card-head">
        <div>
            @if ($tag)
                <span class="chip">{{ $tag }}</span>
            @endif
            <p class="eyebrow">Tujuan belajar</p>
            <h3>{{ $title }}</h3>
        </div>
        @if ($code)
            <a class="code-mini" href="{{ route('code-review.show', $code) }}">kode: {{ $code }}</a>
        @endif
    </div>
    <p class="purpose">{{ $purpose }}</p>
    {{ $slot }}
    @if ($code)
        <div class="card-footer">
            <span class="tiny">Eksperimen lebih dalam ada di titik review ini.</span>
            <a class="code-link" href="{{ route('code-review.show', $code) }}">Lihat di kode <span aria-hidden="true">→</span></a>
        </div>
    @endif
</section>

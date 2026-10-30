@props(['items' => []])
@if (count($items))
    @php($headingId = 'faq-heading-'.\Illuminate\Support\Str::random(6))
    <section class="faq-block" aria-labelledby="{{ $headingId }}">
        <div class="section-head">
            <p class="eyebrow">Ruang eksperimen</p>
            <h2 id="{{ $headingId }}">Kalau begini gimana?</h2>
            <p>Buka pertanyaan, lalu tekan <strong>Coba sekarang</strong>. Hasil request tampil langsung di bawah pertanyaan.</p>
        </div>
        <div class="faq-items">
            @foreach ($items as $item)
                <details class="faq-item">
                    <summary>
                        <span class="q-mark">?</span>
                        <span>{{ $item['q'] }}</span>
                    </summary>
                    <div class="faq-body">
                        <p>{!! nl2br(e($item['a'])) !!}</p>
                        <div class="faq-actions">
                            @if (isset($item['scenario']))
                                <button type="button" class="btn btn-small" data-faq-run data-scenario="{{ json_encode($item['scenario']) }}">Coba sekarang</button>
                            @endif
                            @if (! empty($item['code']))
                                <a class="code-link" href="{{ route('code-review.show', $item['code']) }}">Lihat kode {{ $item['code'] }} →</a>
                            @endif
                        </div>
                        <div class="faq-result" data-faq-result hidden aria-live="polite"></div>
                    </div>
                </details>
            @endforeach
        </div>
    </section>
@endif

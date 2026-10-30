@props(['commands' => [], 'title' => 'Salin sebagai perintah terminal'])

<div {{ $attributes->merge(['class' => 'terminal']) }} data-terminal>
    <div class="terminal-tabs" role="tablist" aria-label="{{ $title }}">
        @foreach ($commands as $label => $command)
            <button
                type="button"
                class="terminal-tab {{ $loop->first ? 'is-active' : '' }}"
                role="tab"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                data-terminal-tab
            >{{ $label }}</button>
        @endforeach
    </div>
    <div class="terminal-panel">
        <button type="button" class="copy-btn" data-terminal-copy>Salin</button>
        @foreach ($commands as $label => $command)
            <pre data-terminal-panel @if (! $loop->first) hidden @endif>{{ $command }}</pre>
        @endforeach
    </div>
    <p class="tiny" style="padding: .35rem .65rem .55rem; color: #a9c6b9; margin: 0;" data-terminal-status>Pilih tab sesuai sistem operasi Anda.</p>
</div>

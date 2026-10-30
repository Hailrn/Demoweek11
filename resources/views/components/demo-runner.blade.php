@props([
    'path',
    'method' => 'GET',
    'body' => null,
    'headers' => [],
    'role' => null,
    'roleSelect' => false,
    'showBody' => false,
    'editablePath' => false,
    'note' => null,
    'sendLabel' => 'Kirim request',
])
@php
    $bodyText = is_array($body) ? json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) ($body ?? '');
    $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
    $headersJson = json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<div
    {{ $attributes->merge(['class' => 'runner']) }}
    data-demo-runner
    data-default-path="{{ $path }}"
    data-default-method="{{ $method }}"
    data-default-body="{{ $bodyText }}"
    data-headers="{{ $headersJson }}"
    data-role="{{ $role }}"
    data-show-body="{{ $showBody ? '1' : '0' }}"
>
    <div class="runner-controls">
        <label class="field field-method">
            <span>Method</span>
            <select data-runner-method aria-label="HTTP method">
                @foreach ($methods as $option)
                    <option value="{{ $option }}" @selected($method === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>
        <label class="field field-path">
            <span>Path internal</span>
            <input type="text" value="{{ $path }}" data-runner-path @readonly(! $editablePath) aria-label="Path request">
        </label>
        @if ($roleSelect)
            <label class="field field-role">
                <span>Peran request</span>
                <select data-runner-role aria-label="Peran untuk request ini">
                    @foreach (config('demo.roles') as $key => $roleData)
                        <option value="{{ $key }}" @selected(($role ?? 'guest') === $key)>{{ $roleData['label'] }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <div class="runner-actions">
            <button type="button" class="btn btn-small" data-runner-send>{{ $sendLabel }}</button>
            <button type="button" class="btn btn-ghost btn-small" data-runner-reset>Reset skenario</button>
        </div>
    </div>

    @if ($showBody)
        <label class="field field-body">
            <span>Body (JSON)</span>
            <textarea data-runner-body rows="4" spellcheck="false" placeholder="Contoh: {&quot;name&quot;: &quot;Produk&quot;}">{{ $bodyText }}</textarea>
        </label>
    @endif

    @if ($note)
        <p class="runner-note">{{ $note }}</p>
    @endif

    {{ $slot }}

    <div class="runner-result" data-runner-result hidden aria-live="polite"></div>
</div>

@props(['title' => 'Catatan koreksi materi'])
<aside class="correction" role="note">
    <p class="correction-title">{{ $title }}</p>
    <div>{{ $slot }}</div>
</aside>

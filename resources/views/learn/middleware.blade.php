@extends('layouts.app')

@section('title', 'Modul B · Middleware')

@section('content')
@php
    $middlewareCommands = [
        'Linux / macOS' => implode(PHP_EOL, [
            'curl -i http://localhost:8000/api/admin/dashboard',
            'curl -i -H "X-Demo-Role: admin" http://localhost:8000/api/admin/dashboard',
            'curl -i -H "X-Demo-Role: kasir" http://localhost:8000/api/admin/reports',
            'curl -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" http://localhost:8000/api/demo/global-public',
        ]),
        'Windows CMD' => implode(PHP_EOL, [
            'curl -i http://localhost:8000/api/admin/dashboard',
            'curl -i -H "X-Demo-Role: admin" http://localhost:8000/api/admin/dashboard',
            'curl -i -H "X-Demo-Role: kasir" http://localhost:8000/api/admin/reports',
            'curl -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" http://localhost:8000/api/demo/global-public',
        ]),
        'Windows PowerShell' => implode(PHP_EOL, [
            'curl.exe -i http://localhost:8000/api/admin/dashboard',
            'curl.exe -i -H "X-Demo-Role: admin" http://localhost:8000/api/admin/dashboard',
            'curl.exe -i -H "X-Demo-Role: kasir" http://localhost:8000/api/admin/reports',
            'curl.exe -i -H "X-Demo-Global-Role: 1" -H "X-Demo-Role: kasir" http://localhost:8000/api/demo/global-public',
        ]),
    ];
@endphp

<section class="page-hero">
    <p class="eyebrow">Modul B</p>
    <h1>Middleware</h1>
    <p class="lead" style="color:#d5e8df; max-width: 70ch;">
        Middleware adalah lapisan yang berjalan <em>sebelum</em> controller. Ia dapat meneruskan request,
        menghentikannya, atau menambahkan sesuatu pada response. Di sini Anda bisa melihat 401, 403,
        urutan lapisan, sampai middleware global yang tidak sengaja memblokir endpoint publik.
    </p>
    <div class="hero-actions">
        <a class="btn" href="#group-admin">Uji matriks role</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="#parameter">Coba parameter</a>
        <a class="btn btn-ghost" style="border-color:#a8c6b8;color:#eff7f1;" href="{{ route('code-review.show', 'mw-02') }}">Lihat CheckUserRole</a>
    </div>
</section>

<x-correction-note>
    <p><strong>Koreksi materi:</strong> contoh menulis <code>auth.bacis</code>; nama yang benar adalah <code>auth.basic</code>. Karena demo ini tidak memakai tabel user, alias <code>auth</code> sengaja diarahkan ke <code>DemoAuthenticate</code>.</p>
    <p><strong>Catatan:</strong> role di sini adalah simulasi. Jangan memakai header <code>X-Demo-Role</code> sebagai autentikasi produksi.</p>
</x-correction-note>

<section class="card">
    <div class="section-head" style="margin-top:0;">
        <p class="eyebrow">Peta lapisan</p>
        <h2>Request melewati beberapa lapisan</h2>
        <p>Urutan pada diagram adalah urutan yang bisa Anda lihat di trace. Response berjalan kembali dengan arah sebaliknya.</p>
    </div>
    <div class="diagram" aria-label="Diagram lapisan middleware">
        <div class="layer"><span class="layer-name">global</span><span class="layer-desc">RequestTrace, header demo, dan role global (opsional) berjalan untuk semua route.</span></div>
        <div class="diagram-arrow" aria-hidden="true">↓</div>
        <div class="layer"><span class="layer-name">group</span><span class="layer-desc">Grup web menyiapkan session; grup api menyiapkan request tanpa cookie/session. ResolveDemoUser membaca peran.</span></div>
        <div class="diagram-arrow" aria-hidden="true">↓</div>
        <div class="layer"><span class="layer-name">route</span><span class="layer-desc">Contoh: <code>auth</code>, lalu <code>role:admin,owner</code>. Di sinilah request dapat berhenti.</span></div>
        <div class="diagram-arrow" aria-hidden="true">↓</div>
        <div class="layer"><span class="layer-name">controller</span><span class="layer-desc">Hanya berjalan jika semua lapisan sebelumnya memanggil <code>$next($request)</code>.</span></div>
    </div>
</section>

<section class="section-head anchor-offset" id="group-admin">
    <p class="eyebrow">Konsep 1 · Route group</p>
    <h2>Matriks role pada area admin</h2>
    <p>Klik satu sel untuk menguji kombinasi endpoint dan role, atau uji semuanya. Peran guest mengirim request tanpa user.</p>
</section>

<div class="card">
    <div class="section-actions" style="margin-top:0;align-items:center;">
        <button type="button" class="btn btn-small" data-matrix-run>Uji semua kombinasi</button>
        <span class="tiny">12 request berurutan. Tidak ada CSRF karena semua endpoint berada di grup API.</span>
    </div>
    <div class="table-wrap" style="margin-top:.7rem;">
        <table class="matrix">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>guest</th>
                    <th>kasir</th>
                    <th>owner</th>
                    <th>admin</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    ['url' => '/api/admin/dashboard', 'label' => '/api/admin/dashboard', 'aturan' => 'auth + role:admin'],
                    ['url' => '/api/admin/reports', 'label' => '/api/admin/reports', 'aturan' => 'auth + role:admin,owner'],
                    ['url' => '/api/admin/area/info', 'label' => '/api/admin/area/info', 'aturan' => 'prefix admin + role:admin,owner'],
                ] as $endpoint)
                    <tr>
                        <td>
                            <span class="mono tiny">{{ $endpoint['label'] }}</span>
                            <span class="table-sub">{{ $endpoint['aturan'] }}</span>
                        </td>
                        @foreach (['guest', 'kasir', 'owner', 'admin'] as $role)
                            <td>
                                <button
                                    type="button"
                                    class="matrix-btn"
                                    data-matrix-url="{{ $endpoint['url'] }}"
                                    data-matrix-role="{{ $role }}"
                                    aria-label="Uji {{ $endpoint['label'] }} sebagai {{ $role }}"
                                >—</button>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="matrix-legend">
        <span><strong>200</strong> lolos dan controller berjalan</span>
        <span><strong>401</strong> belum ada user</span>
        <span><strong>403</strong> user ada, role tidak cocok</span>
    </div>
    <div class="faq-result" data-matrix-result hidden aria-live="polite"></div>
    <p class="tiny">Tips: baca trace pada hasil untuk melihat <code>Middleware auth</code> dan <code>Middleware role</code> mana yang berhenti.</p>
</div>

<div class="demo-grid" style="margin-top:1rem;">
    <x-demo-card title="Peran dari session (GUI)" purpose="Membuktikan role yang dipilih di sidebar dipakai oleh route web, tanpa header X-Demo-Role." code="mw-04" tag="Session">
        <x-demo-runner path="/demo/session-admin" method="GET" note="Ubah peran di sidebar, lalu kirim request ini. Guest → 401, kasir → 403, owner/admin → 200. Panel tidak mengirim header peran, jadi session yang dibaca." />
    </x-demo-card>
    <x-demo-card title="Peran dari header (API)" purpose="Membandingkan sumber peran: panel API mengirim X-Demo-Role, sedangkan route web membaca session." code="mw-04" tag="Header">
        <x-demo-runner path="/api/demo/middleware/inspect" method="GET" role-select="true" role="kasir" note="Header X-Demo-Role menimpa session untuk request ini karena grup API tidak memakai session." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="parameter">
    <p class="eyebrow">Konsep 2 · Parameter</p>
    <h2>role:admin,owner menjadi array</h2>
    <p><code>handle(Request $request, Closure $next, string ...$roles)</code> menampung parameter setelah tanda titik dua. Coba ganti peran dan lihat data <code>roles_parameter</code>.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Menerima satu atau banyak role" purpose="Membuktikan parameter middleware tidak dipecah manual oleh controller." code="mw-02" tag="Role">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/middleware/inspect","role":"kasir"}'>Kasir</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/middleware/inspect","role":"owner"}'>Owner</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/middleware/inspect","role":"admin"}'>Admin</button>
        </div>
        <x-demo-runner path="/api/demo/middleware/inspect" method="GET" role-select="true" role="kasir" note="Middleware route memakai role:admin,owner,kasir." />
    </x-demo-card>

    <x-demo-card title="401 vs 403" purpose="Membedakan belum punya user dengan user yang tidak berwenang." code="mw-03" tag="Status">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/admin/dashboard","role":"guest"}'>Guest → 401</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/admin/dashboard","role":"kasir"}'>Kasir → 403</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/admin/dashboard","role":"admin"}'>Admin → 200</button>
        </div>
        <x-demo-runner path="/api/admin/dashboard" method="GET" role-select="true" role="guest" note="Ganti role pada select. Header X-Demo-Role akan dikirim otomatis." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="urutan">
    <p class="eyebrow">Konsep 3 · Urutan</p>
    <h2>auth dulu, atau role dulu?</h2>
    <p>Hasil akhir bisa sama-sama 401, tetapi lapisan yang berhenti berbeda. Baca trace untuk melihat middleware mana yang lebih dulu menolak.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="auth → role" purpose="Request dicek oleh auth terlebih dahulu; role baru berjalan setelah user ada." code="mw-04" tag="Urutan">
        <x-demo-runner path="/api/demo/middleware-order/auth-first" method="GET" role-select="true" role="guest" note="Coba guest, kasir, lalu admin." />
    </x-demo-card>
    <x-demo-card title="role → auth" purpose="Role berjalan lebih dulu; untuk request tanpa user, CheckUserRole yang berhenti." code="mw-04" tag="Urutan">
        <x-demo-runner path="/api/demo/middleware-order/role-first" method="GET" role-select="true" role="guest" note="Bandingkan label trace dengan kartu di sebelah." />
    </x-demo-card>
</div>

<div class="demo-grid">
    <x-demo-card title="Normal: controller dijalankan" purpose="Melihat respons sukses setelah kedua middleware memanggil $next." code="mw-02" tag="$next">
        <x-demo-runner path="/api/demo/middleware/stop" method="GET" role-select="true" role="admin" note="Middleware role mengizinkan admin, lalu controller berjalan." />
    </x-demo-card>
    <x-demo-card title="Berhenti sebelum $next" purpose="Melihat response yang dibuat middleware tanpa menjalankan controller." code="mw-02" tag="$next">
        <x-demo-runner
            path="/api/demo/middleware/stop"
            method="GET"
            :headers="['X-Demo-Stop-Before-Next' => '1']"
            role-select="true"
            role="admin"
            note="Header demo membuat CheckUserRole mengembalikan response lebih awal. Bandingkan body dan trace."
        />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="alias">
    <p class="eyebrow">Konsep 4 · Alias</p>
    <h2>Alias di bootstrap/app.php</h2>
    <p>Nama pendek <code>role</code> dipetakan ke <code>CheckUserRole</code>. Jika alias salah, container gagal membuat middleware sebelum controller berjalan.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Simulasi alias yang belum terdaftar" purpose="Melihat pesan kelas yang tidak ditemukan tanpa menampilkan stack trace mentah." code="mw-01" tag="Alias">
        <x-demo-runner path="/api/demo/middleware/missing-alias" method="GET" note="Endpoint ini menangkap exception agar pesan tetap aman dibaca." />
    </x-demo-card>
    <x-demo-card title="Alias yang benar" purpose="Membandingkan pesan error dengan middleware yang sudah terdaftar." code="mw-03" tag="Alias">
        <x-demo-runner path="/api/admin/reports" method="GET" role-select="true" role="owner" note="auth dan role memakai alias yang terdaftar di bootstrap/app.php." />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="global">
    <p class="eyebrow">Konsep 5 · Global</p>
    <h2>Middleware global berjalan di semua route</h2>
    <p>Trace dan header demo ditambahkan oleh middleware global. Endpoint publik pun tetap melewatinya.</p>
</section>

<div class="demo-grid">
    <x-demo-card title="Global header dan trace" purpose="Melihat header X-Demo-Global dan X-Demo-Trace pada endpoint publik." code="mw-07" tag="Global">
        <x-demo-runner path="/api/demo/global-public" method="GET" note="Lihat header respons dan panel trace di bawah." />
    </x-demo-card>
    <x-demo-card id="global-role" title="Global role switch (aktif)" purpose="Memahami risiko memeriksa role secara global: endpoint publik ikut terblokir." code="mw-08" tag="Global">
        <div class="quick-examples">
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/global-public","role":"admin","headers":{"X-Demo-Global-Role":"1"}}'>Admin → 200</button>
            <button type="button" class="btn btn-small btn-ghost" data-runner-preset='{"method":"GET","path":"/api/demo/global-public","role":"kasir","headers":{"X-Demo-Global-Role":"1"}}'>Kasir → 403</button>
        </div>
        <x-demo-runner
            path="/api/demo/global-public"
            method="GET"
            :headers="['X-Demo-Global-Role' => '1']"
            role-select="true"
            role="kasir"
            note="Flag X-Demo-Global-Role: 1 sengaja mengaktifkan pemeriksaan role global."
        />
    </x-demo-card>
</div>

<section class="section-head anchor-offset" id="throttle">
    <p class="eyebrow">Bawaan Laravel · throttle</p>
    <h2>Batas lima request per menit</h2>
    <p>Route <code>/api/demo/throttle</code> memakai <code>throttle:5,1</code>. Kirim enam request cepat; request keenam harus menghasilkan 429.</p>
</section>

<div class="card">
    <div class="section-actions" style="margin-top:0;">
        <button type="button" class="btn btn-small" data-throttle-run>Kirim 6 request</button>
    </div>
    <div class="faq-result" data-throttle-result hidden aria-live="polite"></div>
    <div class="faq-result" data-throttle-detail hidden aria-live="polite"></div>
    <p class="tiny">Jika kuota dari percobaan sebelumnya masih terpakai, tunggu satu menit atau restart server untuk mereset cache lokal. Limit dihitung per key/IP.</p>
</div>

<section class="section-head anchor-offset" id="signed">
    <p class="eyebrow">Bawaan Laravel · signed</p>
    <h2>Signature menjaga URL</h2>
    <p>Signature adalah hash dari URL dan waktu kedaluwarsa. URL valid membuka halaman; satu karakter yang diubah menghasilkan 403.</p>
</section>

<div class="card">
    <div class="section-actions" style="margin-top:0;">
        <button type="button" class="btn btn-small" data-signed-run>Buat dan uji signed URL</button>
    </div>
    <div class="faq-result" data-signed-result hidden aria-live="polite"></div>
    <div class="faq-result" data-signed-detail hidden aria-live="polite"></div>
    <p class="tiny">URL yang dibuat berlaku 15 menit. Jangan membagikan URL bertanda tangan sebagai pengganti autentikasi.</p>
</div>

<section class="section-head anchor-offset" id="bawaan">
    <p class="eyebrow">Bawaan Laravel</p>
    <h2>Katalog 11 alias middleware bawaan</h2>
    <p>Ini daftar alias yang dibahas di materi. Dua di antaranya punya demo hidup di halaman ini
    (<code>throttle</code> dan <code>signed</code>); sisanya dijelaskan beserta alasan kenapa tidak bisa
    dijalankan di demo tanpa database atau starter kit.</p>
</section>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Alias</th>
                    <th>Fungsi</th>
                    <th>Kapan dipakai</th>
                    <th>Status di demo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    [
                        'alias' => 'auth',
                        'fungsi' => 'Memastikan ada user terautentikasi; jika tidak, request dihentikan.',
                        'kapan' => 'Route yang hanya boleh dibuka pengguna yang sudah login.',
                        'status' => 'Didemokan versi simulasi',
                        'demo' => true,
                    ],
                    [
                        'alias' => 'auth.basic',
                        'fungsi' => 'HTTP Basic auth: membaca kredensial dari header Authorization.',
                        'kapan' => 'Endpoint internal sederhana tanpa halaman login.',
                        'status' => 'Tidak didemokan — butuh provider user/tabel users',
                        'correction' => 'Materi menulis auth.bacis; nama yang benar adalah auth.basic.',
                    ],
                    [
                        'alias' => 'auth.session',
                        'fungsi' => 'Autentikasi stateful berbasis session (guard session).',
                        'kapan' => 'Aplikasi web dengan halaman login biasa.',
                        'status' => 'Tidak didemokan — butuh tabel users dan login asli',
                    ],
                    [
                        'alias' => 'guest',
                        'fungsi' => 'Kebalikan auth: hanya tamu yang boleh masuk; user yang sudah login dialihkan.',
                        'kapan' => 'Halaman login/register.',
                        'status' => 'Tidak didemokan — butuh login asli',
                    ],
                    [
                        'alias' => 'can',
                        'fungsi' => 'Otorisasi lewat Gate/Policy, misalnya can:update,post.',
                        'kapan' => 'Aksi yang bergantung kepemilikan atau izin spesifik.',
                        'status' => 'Tidak didemokan — butuh model/Eloquent dan Policy',
                    ],
                    [
                        'alias' => 'verified',
                        'fungsi' => 'Memastikan email user sudah diverifikasi (MustVerifyEmail).',
                        'kapan' => 'Area yang mensyaratkan email terverifikasi.',
                        'status' => 'Tidak didemokan — butuh tabel users dan alur email',
                    ],
                    [
                        'alias' => 'password.confirm',
                        'fungsi' => 'Meminta konfirmasi password terbaru sebelum aksi sensitif.',
                        'kapan' => 'Sebelum mengubah data keamanan.',
                        'status' => 'Tidak didemokan — butuh login asli',
                    ],
                    [
                        'alias' => 'throttle',
                        'fungsi' => 'Membatasi jumlah request, misalnya throttle:5,1.',
                        'kapan' => 'Mencegah spam atau penyalahgunaan endpoint.',
                        'status' => 'Didemokan — lihat bagian throttle di atas',
                        'demo' => true,
                    ],
                    [
                        'alias' => 'signed',
                        'fungsi' => 'Memvalidasi URL bertanda tangan; signature salah → 403.',
                        'kapan' => 'Tautan verifikasi email atau unduhan aman.',
                        'status' => 'Didemokan — lihat bagian signed di atas',
                        'demo' => true,
                    ],
                    [
                        'alias' => 'cache.headers',
                        'fungsi' => 'Menambahkan header Cache-Control (dan ETag) pada response.',
                        'kapan' => 'Response yang aman di-cache browser/proxy.',
                        'status' => 'Penjelasan saja — contoh kode ada di komentar',
                    ],
                    [
                        'alias' => 'precognitive',
                        'fungsi' => 'Menangani request validasi precognitive dari frontend.',
                        'kapan' => 'Validasi real-time sebelum submit form.',
                        'status' => 'Tidak didemokan — fitur frontend Laravel Precognition',
                    ],
                ] as $mw)
                    <tr>
                        <td><code>{{ $mw['alias'] }}</code></td>
                        <td>{{ $mw['fungsi'] }}</td>
                        <td>{{ $mw['kapan'] }}</td>
                        <td>
                            <span class="pill {{ ! empty($mw['demo']) ? 'pill-role' : 'pill-soft' }}">{{ $mw['status'] }}</span>
                            @isset($mw['correction'])
                                <span class="table-sub">{{ $mw['correction'] }}</span>
                            @endisset
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="tiny">Enam alias lain (<code>auth.basic</code>, <code>auth.session</code>, <code>guest</code>,
    <code>can</code>, <code>verified</code>, <code>password.confirm</code>) membutuhkan tabel user,
    model, atau starter kit, sehingga sengaja tidak dipasang di proyek tanpa database ini. Yang penting
    bagi pembelajaran di sini adalah peran <code>auth</code> dan alur <code>throttle</code>/<code>signed</code>
    yang tetap bisa dibuktikan secara nyata.</p>

    <details class="details-block">
        <summary>Contoh penulisan alias di route (tanpa menjalankannya)</summary>
        @verbatim
<pre class="snippet-pre">// routes/web.php (di project dengan database + tabel users)
Route::get('/profil', ProfilController::class)->middleware('auth');
Route::get('/login', LoginController::class)->middleware('guest');
Route::get('/verifikasi-email', EmailController::class)->middleware(['auth', 'verified']);
Route::post('/ubah-password', PasswordController::class)->middleware('password.confirm');
Route::get('/internal', ReportController::class)->middleware('auth.basic');
Route::get('/unduhan/{file}', FileController::class)->middleware('signed');
Route::get('/katalog', KatalogController::class)->middleware('cache.headers:public;max_age=300');

// routes/api.php
Route::get('/posts/{post}', [PostController::class, 'show'])->middleware('can:view,post');
Route::post('/form', FormController::class)->middleware('precognitive');
Route::get('/kirim-email', MailController::class)->middleware('throttle:5,1');
</pre>
        @endverbatim
    </details>
</div>

<x-correction-note title="Catatan materi">
    <p><strong><code>auth.basic</code> bukan <code>auth.bacis</code>.</strong> Materi sumber salah menulis
    namanya; menuliskan <code>auth.bacis</code> akan memunculkan error “Target class [auth.bacis] does not exist”.</p>
    <p><code>subscribed</code> yang disebut di materi bukan middleware bawaan universal. Alias itu berasal
    dari <em>Laravel Spark</em> (starter kit berbayar), jadi tidak tersedia di instalasi Laravel biasa.</p>
</x-correction-note>

<section class="section-head">
    <p class="eyebrow">Terminal</p>
    <h2>Uji role dari terminal</h2>
    <p>Header <code>X-Demo-Role</code> adalah sumber peran utama untuk API. Token Bearer demo juga tersedia, tetapi bukan autentikasi produksi.</p>
</section>
<x-terminal-commands :commands="$middlewareCommands" />

<section style="margin-top:2rem;">
    <x-faq-list :items="$faqs" />
</section>

<x-correction-note title="Ingat">
    <p>Urutan middleware penting. Meletakkan <code>role</code> sebelum <code>auth</code> tidak selalu salah,
    tetapi jika <code>CheckUserRole</code> tidak siap menerima user null, hasilnya bisa berbeda dari yang diharapkan.
    Baca trace untuk memastikan lapisan mana yang menolak.</p>
</x-correction-note>
@endsection

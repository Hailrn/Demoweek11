<?php

namespace App\Http\Controllers\Api;

use App\Demo\Trace\RequestTrace;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Throwable;

class MiddlewareDemoController extends Controller
{
    public function __construct(private RequestTrace $trace) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->adminPayload($request, 'Dashboard admin', ['role:admin']);
    }

    public function reports(Request $request): JsonResponse
    {
        return $this->adminPayload($request, 'Laporan', ['role:admin,owner']);
    }

    public function areaInfo(Request $request): JsonResponse
    {
        return $this->adminPayload($request, 'Admin area (group prefix)', ['auth', 'role:admin,owner']);
    }

    public function orderAuthFirst(Request $request): JsonResponse
    {
        $this->trace->add('controller', 'MiddlewareDemoController@orderAuthFirst', 'lolos', 'Controller berjalan setelah auth lalu role.');

        return $this->payload('Urutan auth → role', [
            'urutan_middleware' => ['auth', 'role:admin'],
            'user' => $request->user()?->role,
            'trace' => 'Lihat header X-Demo-Trace: auth berjalan lebih dulu.',
        ]);
    }

    public function orderRoleFirst(Request $request): JsonResponse
    {
        $this->trace->add('controller', 'MiddlewareDemoController@orderRoleFirst', 'lolos', 'Controller berjalan setelah role lalu auth.');

        return $this->payload('Urutan role → auth', [
            'urutan_middleware' => ['role:admin', 'auth'],
            'user' => $request->user()?->role,
            'trace' => 'Lihat header X-Demo-Trace: role memeriksa lebih dulu.',
        ]);
    }

    public function inspectRoles(Request $request): JsonResponse
    {
        $middleware = $request->route()?->gatherMiddleware() ?? [];
        $roleMiddleware = array_values(array_filter($middleware, fn ($item) => str_starts_with((string) $item, 'role:')));
        $roles = [];

        foreach ($roleMiddleware as $item) {
            $roles = array_merge($roles, explode(',', substr($item, strlen('role:'))));
        }

        $this->trace->add(
            'controller',
            'MiddlewareDemoController@inspectRoles',
            'lolos',
            'Parameter role diterima oleh CheckUserRole sebagai array: ['.implode(', ', $roles).'].',
            meta: ['roles' => $roles],
        );

        return $this->payload('Parameter middleware role', [
            'user' => $request->user()?->role,
            'middleware_route' => $middleware,
            'roles_parameter' => $roles,
            'cara_menerima' => 'handle(Request $request, Closure $next, string ...$roles)',
        ]);
    }

    public function missingAlias(): JsonResponse
    {
        try {
            app('roleYangBelumTerdaftar');
            $message = 'Tidak ada error (tidak terduga).';
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
        }

        $this->trace->add('controller', 'MiddlewareDemoController@missingAlias', 'info', 'Simulasi alias yang belum terdaftar.');

        return $this->payload('Simulasi alias belum terdaftar', [
            'simulasi' => true,
            'error' => $message,
            'contoh_error_laravel' => 'Target class [App\\Http\\Middleware\\CheckUserRoleSalah] does not exist.',
            'penjelasan' => 'Jika nama alias/class di route salah dan belum terdaftar di bootstrap/app.php, container gagal membuat middleware. Endpoint ini menangkap pesan error agar tidak menampilkan stack trace mentah.',
        ]);
    }

    public function globalPublic(Request $request): JsonResponse
    {
        $this->trace->add('controller', 'MiddlewareDemoController@globalPublic', 'lolos', 'Endpoint publik berhasil diakses.');

        return $this->payload('Endpoint publik', [
            'terblokir' => false,
            'role' => $request->user()?->role ?? 'guest',
            'catatan' => 'Kirim header X-Demo-Global-Role: 1 untuk mengaktifkan role di middleware global.',
        ]);
    }

    // [CODE-REVIEW:mw-05] Throttle bawaan Laravel
    // Baca : throttle:5,1 membatasi 5 request per menit per key.
    // Coba : tekan "Kirim 6x" dan lihat 429 + Retry-After.
    // Lihat: Modul B -> "Middleware bawaan: throttle"
    // region demo:mw-05
    public function throttle(): JsonResponse
    {
        $this->trace->add('controller', 'MiddlewareDemoController@throttle', 'lolos', 'Request lolos di bawah batas throttle.');

        return $this->payload('Request lolos throttle', [
            'batas' => '5 request / 1 menit',
            'headers_response' => [
                'X-RateLimit-Limit',
                'X-RateLimit-Remaining',
                'Retry-After (hanya saat 429)',
            ],
            'catatan' => 'Header limit dikirim Laravel pada response, bukan pada request. Buka panel Header respons untuk melihat nilainya.',
        ]);
    }
    // endregion demo:mw-05

    // [CODE-REVIEW:mw-06] Signed URL
    // Baca : signature adalah hash dari URL + expires; satu karakter berubah → 403.
    // Coba : buka URL valid, lalu URL tampered.
    // Lihat: Modul B -> "Middleware bawaan: signed"
    // region demo:mw-06
    public function signedGenerate(): JsonResponse
    {
        $valid = URL::temporarySignedRoute('demo.signed', now()->addMinutes(15));
        $parts = parse_url($valid);
        parse_str($parts['query'] ?? '', $query);
        $signature = (string) ($query['signature'] ?? '');
        $query['signature'] = $signature === '' ? 'x' : substr($signature, 0, -1).($signature[-1] === 'a' ? 'b' : 'a');
        $tampered = ($parts['scheme'] ?? 'http').'://'.($parts['host'] ?? 'localhost')
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .($parts['path'] ?? '/')
            .'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        $this->trace->add('controller', 'MiddlewareDemoController@signedGenerate', 'lolos', 'URL bertanda tangan dibuat; URL tampered disiapkan.');

        return $this->payload('Signed URL dibuat', [
            'valid_url' => $valid,
            'tampered_url' => $tampered,
            'expires_in' => '15 menit',
            'catatan' => 'Coba buka URL valid, lalu ubah satu karakter pada signature.',
        ]);
    }
    // endregion demo:mw-06

    public function inspectApi(Request $request): JsonResponse
    {
        return $this->payload('Request melewati grup api', [
            'session_started' => $request->hasSession(),
            'middleware_route' => $request->route()?->gatherMiddleware() ?? [],
            'catatan' => 'Grup api tidak memakai cookie/session/CSRF. Peran dikirim lewat X-Demo-Role.',
        ]);
    }

    private function adminPayload(Request $request, string $label, array $middleware): JsonResponse
    {
        $this->trace->add(
            'controller',
            'MiddlewareDemoController@'.($request->route()?->getActionMethod() ?? 'admin'),
            'lolos',
            $label.' berhasil dijalankan.',
            meta: ['middleware' => $middleware],
        );

        return $this->payload($label, [
            'user' => [
                'name' => $request->user()?->name,
                'role' => $request->user()?->role,
            ],
            'middleware_route' => $middleware,
            'pesan' => 'Controller hanya dijalankan setelah middleware mengizinkan.',
        ]);
    }

    private function payload(string $message, array $data): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Middleware inti materi. Sengaja dibuat sedekat mungkin dengan contoh,
| lalu ditambah pencatatan trace agar jalur request bisa divisualisasikan.
*/
class CheckUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $trace = app(RequestTrace::class);

        // [CODE-REVIEW:mw-02] CheckUserRole menerima parameter variadic
        // Baca : bagaimana string ...$roles menampung 'admin,owner'.
        // Coba : ubah in_array(..., true) jadi false, lalu uji role 'ADMIN'.
        // Lihat: Modul B -> "Middleware Parameters"
        // region demo:mw-02
        $user = $request->user();

        if (! $user) {
            $trace->add(
                stage: 'route',
                label: 'Middleware role:'.implode(',', $roles),
                status: 'berhenti',
                message: '$request->user() null → abort(401).',
                meta: ['roles' => $roles],
            );

            abort(401, 'Unauthenticated.');
        }

        if (! in_array($user->role, $roles, true)) {
            $trace->add(
                stage: 'route',
                label: 'Middleware role:'.implode(',', $roles),
                status: 'berhenti',
                message: "Role '{$user->role}' tidak ada di [".implode(', ', $roles).'] → abort(403).',
                meta: ['roles' => $roles, 'user_role' => $user->role],
            );

            abort(403, 'Forbidden.');
        }

        $trace->add(
            stage: 'route',
            label: 'Middleware role:'.implode(',', $roles),
            status: 'lolos',
            message: "Role '{$user->role}' cocok. Parameter diterima sebagai array \$roles.",
            meta: ['roles' => $roles, 'user_role' => $user->role],
        );
        // endregion demo:mw-02

        // Flag demo (header), bukan mengubah file saat runtime.
        if ($request->header('X-Demo-Stop-Before-Next') === '1') {
            $trace->add(
                stage: 'route',
                label: 'CheckUserRole: berhenti sebelum $next',
                status: 'berhenti',
                message: 'Response dikembalikan tanpa memanggil $next; controller tidak dijalankan.',
            );

            return response()->json([
                'status' => 'stopped',
                'message' => 'Middleware menghentikan request sebelum controller.',
                'data' => [
                    'controller_dijalankan' => false,
                    'middleware' => 'role:'.implode(',', $roles),
                ],
            ]);
        }

        return $next($request);
    }
}

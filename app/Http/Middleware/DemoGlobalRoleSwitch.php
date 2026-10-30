<?php

namespace App\Http\Middleware;

use App\Demo\Auth\DemoRoleResolver;
use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Middleware global yang "tertidur" dan hanya aktif jika header demo
| X-Demo-Global-Role: 1 dikirim. Ini memperlihatkan bahwa role global
| ikut memblokir endpoint publik. Peran dibaca dari header karena middleware
| global berjalan sebelum StartSession dan sebelum grup web/api.
*/
class DemoGlobalRoleSwitch
{
    public function __construct(
        private DemoRoleResolver $roles,
        private RequestTrace $trace,
    ) {}

    // [CODE-REVIEW:mw-08] Middleware global bisa memblokir endpoint publik
    // Baca : middleware global berjalan sebelum route; flag demo mengaktifkannya.
    // Coba : GET /api/demo/global-public dengan X-Demo-Global-Role: 1 dan role kasir.
    // Lihat: Modul B -> "Kalau role dipasang global?"
    // region demo:mw-08
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Demo-Global-Role') !== '1') {
            $this->trace->add(
                stage: 'global',
                label: 'DemoGlobalRoleSwitch',
                status: 'info',
                message: 'Mode role global tidak aktif. Middleware hanya lewat.',
            );

            return $next($request);
        }

        $role = $this->roles->roleFromRequest($request, allowSession: false);

        if ($role !== 'admin') {
            $this->trace->add(
                stage: 'global',
                label: 'DemoGlobalRoleSwitch (aktif)',
                status: 'berhenti',
                message: "Role global hanya mengizinkan admin. Role saat ini: {$role} → abort(403).",
                meta: ['role' => $role],
            );

            abort(403, 'Middleware role global memblokir request, termasuk endpoint publik.');
        }

        $this->trace->add(
            stage: 'global',
            label: 'DemoGlobalRoleSwitch (aktif)',
            status: 'lolos',
            message: 'Role admin lolos di middleware global.',
            meta: ['role' => $role],
        );

        return $next($request);
    }
    // endregion demo:mw-08
}

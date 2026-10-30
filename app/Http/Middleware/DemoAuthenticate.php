<?php

namespace App\Http\Middleware;

use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Pengganti middleware "auth" untuk demo tanpa database.
| Peran "auth" sengaja diarahkan ke class ini di bootstrap/app.php.
*/
class DemoAuthenticate
{
    public function __construct(private RequestTrace $trace) {}

    public function handle(Request $request, Closure $next): Response
    {
        // [CODE-REVIEW:mw-04] auth simulasi vs role
        // Baca : auth hanya memastikan ada user; role memeriksa nilai role.
        // Coba : urutan auth,role vs role,auth pada pengguna guest/kasir.
        // Lihat: Modul B -> "401 vs 403" dan "Urutan middleware"
        // region demo:mw-04
        if (! $request->user()) {
            $this->trace->add(
                stage: 'route',
                label: 'Middleware auth',
                status: 'berhenti',
                message: 'Tidak ada user pada request → abort(401).',
            );

            abort(401, 'Unauthenticated: pilih peran lewat Role Switcher atau kirim X-Demo-Role.');
        }

        $this->trace->add(
            stage: 'route',
            label: 'Middleware auth',
            status: 'lolos',
            message: 'User ditemukan: '.$request->user()->name.' ('.$request->user()->role.').',
            meta: ['role' => $request->user()->role],
        );

        return $next($request);
        // endregion demo:mw-04
    }
}

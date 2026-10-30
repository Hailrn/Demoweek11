<?php

namespace App\Http\Middleware;

use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Pemeriksa CSRF ketat untuk satu route demo. Laravel 13 sudah mengizinkan
| request same-origin berdasarkan Sec-Fetch-Site; route ini tetap mewajibkan
| token agar konsep token CSRF bisa dicoba dari GUI tanpa mengandalkan
| request lintas origin.
*/
class StrictDemoCsrf
{
    public function __construct(private RequestTrace $trace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $sessionToken = $request->hasSession() ? (string) $request->session()->token() : '';
        $sentToken = (string) ($request->input('_token') ?? $request->header('X-CSRF-TOKEN', ''));

        if ($sessionToken === '' || $sentToken === '' || ! hash_equals($sessionToken, $sentToken)) {
            $this->trace->add(
                'route',
                'StrictDemoCsrf',
                'berhenti',
                'Token CSRF tidak dikirim/tidak cocok → 419.',
            );

            abort(419, 'CSRF token mismatch (pemeriksa ketat demo).');
        }

        $this->trace->add('route', 'StrictDemoCsrf', 'lolos', 'Token CSRF cocok.');

        return $next($request);
    }
}

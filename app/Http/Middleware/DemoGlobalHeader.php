<?php

namespace App\Http\Middleware;

use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Contoh middleware global ringan yang menambah header pada semua response.
| Di GUI ditampilkan sebagai bukti middleware global benar-benar berjalan.
*/
class DemoGlobalHeader
{
    public function __construct(private RequestTrace $trace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->trace->add(
            stage: 'global',
            label: 'DemoGlobalHeader',
            status: 'lolos',
            message: 'Akan menambahkan X-Demo-Global: aktif pada response.',
        );

        $response = $next($request);
        $response->headers->set('X-Demo-Global', 'aktif');

        return $response;
    }
}

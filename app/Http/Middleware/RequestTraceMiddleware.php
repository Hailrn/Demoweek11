<?php

namespace App\Http\Middleware;

use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/*
| Middleware global pertama: menyiapkan trace dan menempelkan header
| X-Demo-Trace pada response sukses. Response error ditangani lewat
| callback exception handler di bootstrap/app.php.
*/
class RequestTraceMiddleware
{
    public function __construct(private RequestTrace $trace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->trace->reset();
        $this->trace->add(
            stage: 'global',
            label: 'Global middleware',
            status: 'info',
            message: 'RequestTrace dimulai. Setiap lapisan akan dicatat.',
            meta: ['method' => $request->method(), 'path' => '/'.ltrim($request->path(), '/')],
        );

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->trace->add(
                stage: 'global',
                label: 'Exception dilempar ke handler',
                status: 'berhenti',
                message: class_basename($exception).' akan dirender sebagai response error.',
            );

            throw $exception;
        }

        $this->trace->add(
            stage: 'response',
            label: 'Response dikirim',
            status: 'lolos',
            message: 'HTTP '.$response->getStatusCode().'. Response melewati middleware dengan arah sebaliknya.',
            phase: 'sesudah',
            meta: ['status' => $response->getStatusCode()],
        );

        $this->trace->attachTo($response);

        return $response;
    }
}

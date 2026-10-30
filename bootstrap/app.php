<?php

use App\Demo\Trace\RequestTrace;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\DemoAuthenticate;
use App\Http\Middleware\DemoGlobalHeader;
use App\Http\Middleware\DemoGlobalRoleSwitch;
use App\Http\Middleware\RequestTraceMiddleware;
use App\Http\Middleware\ResolveDemoUser;
use App\Http\Middleware\StrictDemoCsrf;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // [CODE-REVIEW:mw-07] Middleware global dan trace
        // Baca : middleware global membungkus SEMUA route, termasuk yang tidak ada.
        // Coba : lihat header X-Demo-Global dan X-Demo-Trace pada response mana pun.
        // Lihat: Modul B -> "Global vs Group"
        // region demo:mw-07
        $middleware->append([
            RequestTraceMiddleware::class,
            DemoGlobalHeader::class,
            DemoGlobalRoleSwitch::class,
        ]);
        // endregion demo:mw-07

        // Resolve user demo setelah StartSession pada grup web, dan pada grup api.
        $middleware->web(append: [ResolveDemoUser::class]);
        $middleware->api(append: [ResolveDemoUser::class]);

        // [CODE-REVIEW:mw-01] Registrasi alias middleware
        // Baca : alias 'role' dan 'auth' dipakai di routes/api.php.
        // Coba : ganti 'role' menjadi 'role-salah' lalu buka route admin.
        // Lihat: Modul B -> "Middleware Alias"
        // region demo:mw-01
        $middleware->alias([
            'role' => CheckUserRole::class,
            // Di demo tanpa database, alias auth diarahkan ke simulasi kita.
            'auth' => DemoAuthenticate::class,
            'demo.csrf' => StrictDemoCsrf::class,
        ]);
        // endregion demo:mw-01
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Envelope JSON konsisten untuk error API dan request yang meminta JSON.
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak valid.',
                    'data' => null,
                    'errors' => $exception->errors(),
                ], $exception->status);
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : ($exception instanceof AuthenticationException ? 401 : 500);

            $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

            $message = match ($status) {
                401 => 'Unauthenticated: pilih peran demo atau kirim header X-Demo-Role.',
                403 => 'Forbidden: peran tidak diizinkan.',
                404 => 'Resource atau route tidak ditemukan.',
                405 => 'HTTP method tidak diizinkan untuk route ini.',
                419 => 'CSRF token tidak cocok.',
                429 => 'Terlalu banyak request. Coba lagi nanti.',
                default => $status >= 500
                    ? 'Terjadi kesalahan pada server demo.'
                    : ($exception->getMessage() ?: (Response::$statusTexts[$status] ?? 'Request gagal.')),
            };

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'data' => null,
            ], $status, $headers);
        });

        // Tempelkan trace ke response error (response sukses ditangani middleware).
        $exceptions->respond(function (Response $response) {
            if (! app()->bound(RequestTrace::class)) {
                return $response;
            }

            $trace = app(RequestTrace::class);

            if (! $trace->isEmpty() && ! $trace->hasStop() && $response->getStatusCode() >= 400) {
                $trace->add(
                    stage: 'route',
                    label: 'Response error',
                    status: 'berhenti',
                    message: 'HTTP '.$response->getStatusCode().' dihasilkan oleh exception handler.',
                    meta: ['status' => $response->getStatusCode()],
                );
            }

            $trace->attachTo($response);

            return $response;
        });
    })->create();

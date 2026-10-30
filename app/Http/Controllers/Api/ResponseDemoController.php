<?php

namespace App\Http\Controllers\Api;

use App\Demo\Trace\RequestTrace;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ResponseDemoController extends Controller
{
    public function __construct(private RequestTrace $trace) {}

    // [CODE-REVIEW:ct-06] Lima jenis response controller
    // Baca : view, JSON, redirect, download, dan noContent punya header berbeda.
    // Coba : buka tiap endpoint, perhatikan Content-Type/Location/Content-Disposition/size.
    // Lihat: Modul C -> "Jenis response"
    // region demo:ct-06
    public function view(): View
    {
        $this->trace->add('controller', 'ResponseDemoController@view', 'lolos', 'Mengembalikan view HTML.');

        return view('demo.response-view', [
            'message' => 'Ini response view HTML dari controller.',
        ]);
    }

    public function json(): JsonResponse
    {
        $this->trace->add('controller', 'ResponseDemoController@json', 'lolos', 'Mengembalikan JSON.');

        return response()->json([
            'status' => 'success',
            'message' => 'Ini response JSON.',
            'data' => ['content_type' => 'application/json', 'body' => 'JSON terstruktur'],
        ]);
    }

    public function redirect(): RedirectResponse
    {
        $this->trace->add('controller', 'ResponseDemoController@redirect', 'lolos', 'Mengembalikan redirect 302 ke /dashboard.');

        return redirect()->route('dashboard')->with('success', 'Anda diarahkan dari response redirect.');
    }

    public function download(): Response
    {
        $this->trace->add('controller', 'ResponseDemoController@download', 'lolos', 'Mengembalikan file teks sebagai attachment.');

        return response("Produk demo:\n1. Kopi Arabika Gayo\n2. Teh Melati\n3. Keripik Singkong\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="produk-demo.txt"',
        ]);
    }

    public function noContent(): Response
    {
        $this->trace->add('controller', 'ResponseDemoController@noContent', 'lolos', 'Mengembalikan 204 tanpa body.');

        return response()->noContent();
    }
    // endregion demo:ct-06

    /**
     * Pemeriksa response untuk GUI. Dipakai terutama oleh redirect, karena
     * fetch browser akan mengikuti redirect dan menyembunyikan status 302.
     */
    public function inspect(string $type): JsonResponse
    {
        $allowed = ['view', 'json', 'redirect', 'download', 'no-content'];
        $normalized = $type === 'noContent' ? 'no-content' : $type;

        if (! in_array($normalized, $allowed, true)) {
            abort(404, 'Jenis response tidak dikenal.');
        }

        $response = match ($normalized) {
            // view() mengembalikan View, bukan Response. Bungkus lewat response()
            // agar header dan body bisa diperiksa seperti response lainnya.
            'view' => response($this->view()),
            'json' => $this->json(),
            'redirect' => $this->redirect(),
            'download' => $this->download(),
            'no-content' => $this->noContent(),
        };

        // prepare() menyelesaikan header yang biasanya baru dipasang Symfony
        // saat response benar-benar dikirim (mis. Content-Type untuk view).
        $response->prepare(request());

        $headers = [];
        foreach ($response->headers->all() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }

        $content = $response->getContent();
        $target = method_exists($response, 'getTargetUrl') ? $response->getTargetUrl() : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Response target diperiksa tanpa diikuti browser.',
            'data' => [
                'type' => $normalized,
                'status_code' => $response->getStatusCode(),
                'headers' => $headers,
                'body_size' => strlen((string) $content),
                'body_preview' => mb_substr((string) $content, 0, 1200),
                'target_url' => $target,
                'catatan' => 'Endpoint inspector ini mengembalikan metadata response asli; request GUI ke inspector sendiri berstatus 200.',
            ],
        ]);
    }
}

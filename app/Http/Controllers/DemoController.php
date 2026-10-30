<?php

namespace App\Http\Controllers;

use App\Demo\Store\DemoStore;
use App\Demo\Trace\RequestTrace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function reset(Request $request, DemoStore $store, RequestTrace $trace): JsonResponse|RedirectResponse
    {
        $store->reset();
        $trace->add('controller', 'DemoController@reset', 'lolos', 'State dikembalikan ke seed.');

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data demo berhasil direset ke seed.',
                'data' => [
                    'products' => $store->count(),
                    'state_file' => 'storage/app/demo/state.json',
                ],
            ]);
        }

        return back()->with('success', 'Data demo berhasil direset ke seed.');
    }

    // [CODE-REVIEW:rt-06] Method spoofing dan CSRF pada grup web
    // Baca : form HTML hanya GET/POST; _method dipakai untuk PUT/PATCH/DELETE.
    // Coba : kirim POST /demo/csrf-test tanpa token lalu dengan token.
    // Lihat: Modul A -> "Form HTML & _method" dan FAQ CSRF
    // region demo:rt-06
    /**
     * Route ini memakai StrictDemoCsrf agar konsep token tetap terlihat.
     * Laravel 13 juga mengizinkan POST same-origin berdasarkan Sec-Fetch-Site;
     * detailnya dijelaskan di kartu demo.
     */
    public function csrfTest(Request $request, RequestTrace $trace): JsonResponse
    {
        $trace->add('controller', 'DemoController@csrfTest', 'lolos', 'Token CSRF valid; controller dijalankan.');

        return response()->json([
            'status' => 'success',
            'message' => 'POST web diterima karena token CSRF valid.',
            'data' => [
                'token_valid' => true,
                'session_started' => $request->hasSession(),
            ],
        ]);
    }

    public function spoof(Request $request, RequestTrace $trace): JsonResponse
    {
        $trace->add(
            'controller',
            'DemoController@spoof',
            'lolos',
            'Request mencapai controller setelah method spoofing: '.$request->method(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Form HTML POST berhasil mencapai route PUT melalui _method=PUT.',
            'data' => [
                'method_setelah_spoofing' => $request->method(),
                'input_method' => $request->input('_method'),
                'catatan' => 'Browser mengirim POST, Laravel membaca _method lalu memilih route PUT.',
            ],
        ]);
    }

    public function signed(Request $request, RequestTrace $trace): View
    {
        $trace->add('controller', 'DemoController@signed', 'lolos', 'Signature URL valid.');

        return view('demo.signed', [
            'expires' => $request->query('expires'),
            'signature' => $request->query('signature'),
        ]);
    }

    public function inspectWeb(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Request melewati grup web.',
            'data' => [
                'session_started' => $request->hasSession(),
                'csrf_token_ada' => $request->hasSession() && $request->session()->token() !== null,
                'session_driver' => config('session.driver'),
                'middleware_route' => $request->route()?->gatherMiddleware() ?? [],
                'catatan' => 'Grup web memakai cookie, session, dan perlindungan CSRF. Grup api tidak.',
            ],
        ]);
    }

    // endregion demo:rt-06

    /**
     * Bukti bahwa role dari GUI benar-benar dibaca dari session web.
     * Route ini memakai alias auth dan role yang sama seperti contoh materi.
     */
    public function sessionAdmin(Request $request, RequestTrace $trace): JsonResponse
    {
        $trace->add(
            'controller',
            'DemoController@sessionAdmin',
            'lolos',
            'Peran dibaca dari session web, bukan dari header X-Demo-Role.',
            meta: ['role' => $request->user()?->role],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Route web admin diakses memakai peran dari session.',
            'data' => [
                'user' => [
                    'name' => $request->user()?->name,
                    'role' => $request->user()?->role,
                ],
                'session_role' => $request->session()->get('demo_role', 'guest'),
                'middleware_route' => $request->route()?->gatherMiddleware() ?? [],
                'catatan' => 'Ubah peran di sidebar lalu kirim ulang. Guest → 401, kasir → 403, owner/admin → 200.',
            ],
        ]);
    }
}

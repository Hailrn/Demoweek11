<?php

namespace App\Http\Controllers\Api;

use App\Demo\Models\Product;
use App\Demo\Trace\RequestTrace;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoRouteController extends Controller
{
    public function __construct(
        private ProductService $products,
        private RequestTrace $trace,
    ) {}

    // [CODE-REVIEW:rt-01] Route per method HTTP
    // Baca : GET /hello hanya membaca; POST /echo menerima body.
    // Coba : kirim GET ke /echo (tidak cocok) atau POST ke /hello (405).
    // Lihat: Modul A -> "HTTP Method Playground"
    // region demo:rt-01
    public function hello(Request $request): JsonResponse
    {
        $this->trace->add('controller', 'DemoRouteController@hello', 'lolos', 'Route GET dijalankan.');

        return $this->ok('Hello dari route GET', [
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'catatan' => 'Method HEAD juga dilayani otomatis: body kosong, header tetap ada.',
        ]);
    }

    public function echo(Request $request): JsonResponse
    {
        $this->trace->add('controller', 'DemoRouteController@echo', 'lolos', 'Route match GET+POST dijalankan.');

        return $this->ok('Echo request', [
            'method' => $request->method(),
            'query' => $request->query(),
            'body' => $request->all(),
            'catatan' => 'Route ini didaftarkan dengan Route::match([\'get\', \'post\'], ...).',
        ]);
    }
    // endregion demo:rt-01

    // [CODE-REVIEW:rt-02] Route::match dan Route::any
    // Baca : match membatasi method; any menerima banyak method.
    // Coba : kirim DELETE ke /api/demo/webhook, lalu ke /api/demo/echo.
    // Lihat: Modul A -> "match & any"
    // region demo:rt-02
    public function matchSearch(Request $request): JsonResponse
    {
        $keyword = (string) ($request->query('q') ?? $request->input('q', ''));
        $results = $this->products->search($keyword);

        return $this->ok('Pencarian via Route::match GET+POST', [
            'method' => $request->method(),
            'q' => $keyword,
            'jumlah_hasil' => count($results),
            'data' => $results,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        return $this->ok('Webhook demo menerima banyak method', [
            'method' => $request->method(),
            'body' => $request->all(),
            'peringatan' => 'Route::any() sebaiknya hanya dipakai jika semua method memang dibutuhkan. Batasi dengan match() bila bisa.',
        ]);
    }
    // endregion demo:rt-02

    // [CODE-REVIEW:rt-03] Urutan route menentukan pemenang
    // Baca : route statis yang didaftarkan lebih dulu menang atas {slug}.
    // Coba : buka /api/demo/order/special lalu /api/demo/order/apa-saja.
    // Lihat: Modul A -> "Dua route mirip"
    // region demo:rt-03
    public function orderSpecial(): JsonResponse
    {
        return $this->ok('Route statis menang', [
            'route' => '/api/demo/order/special',
            'alasan' => 'Route statis didaftarkan sebelum route dinamis {slug}.',
        ]);
    }

    public function orderSlug(string $slug): JsonResponse
    {
        return $this->ok('Route dinamis {slug} menang', [
            'slug' => $slug,
            'alasan' => 'Tidak ada route statis yang cocok, jadi parameter dinamis dipakai.',
        ]);
    }
    // endregion demo:rt-03

    // [CODE-REVIEW:rt-04] Parameter route dan constraint
    // Baca : {id} tanpa where menerima "abc"; where('[0-9]+') menolak sebelum controller.
    // Coba : buka /api/demo/where/abc dan /api/demo/where-numeric/abc.
    // Lihat: Modul A -> "Kalau {id} diisi abc?"
    // region demo:rt-04
    public function where(string $id): JsonResponse
    {
        return $this->ok('Parameter tanpa constraint', [
            'id' => $id,
            'tipe' => get_debug_type($id),
            'catatan' => 'Route ini menerima string apa pun, termasuk "abc".',
        ]);
    }

    public function whereNumeric(string $id): JsonResponse
    {
        return $this->ok('Parameter dengan constraint angka', [
            'id' => $id,
            'catatan' => 'Route memakai ->where(\'id\', \'[0-9]+\'). "abc" akan 404 tanpa menjalankan controller.',
        ]);
    }
    // endregion demo:rt-04

    public function byIdString(string $id): JsonResponse
    {
        $product = $this->products->find($id);

        if (! $product) {
            abort(404, 'Produk dengan id "'.$id.'" tidak ditemukan (manual).');
        }

        return $this->ok('Detail produk (manual string $id)', [
            'id' => $id,
            'data' => $product,
        ]);
    }

    // Demo semantik PUT vs PATCH. Endpoint /api/products/{product} tetap
    // memakai update() dengan sometimes seperti contoh materi.
    public function semanticPut(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = $this->products->update($product->id, $validated, replace: true);
        $this->trace->add('controller', 'DemoRouteController@semanticPut', 'lolos', 'PUT mengganti seluruh representasi resource.');
        $this->trace->add('route', 'Semantic PUT', 'lolos', 'PUT dianggap operasi full replacement; semua field wajib.');

        return $this->ok('PUT: ganti seluruh resource', [
            'data' => $updated,
            'catatan' => 'Field yang tidak dikirim akan menjadi default karena resource diganti seluruhnya.',
        ]);
    }

    public function semanticPatch(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $updated = $validated === []
            ? $product
            : $this->products->update($product->id, $validated);
        $this->trace->add('controller', 'DemoRouteController@semanticPatch', 'lolos', 'PATCH hanya mengubah field yang dikirim.');

        return $this->ok('PATCH: ubah sebagian', [
            'data' => $updated,
            'fields_dikirim' => array_keys($validated),
        ]);
    }

    // [CODE-REVIEW:ct-10] Simulasi mass assignment / $fillable
    // Baca : hanya field allowlist yang boleh masuk ke create()/update().
    // Coba : kirim name, price, dan is_admin; lihat field yang diabaikan.
    // Lihat: Modul C -> "Kalau $fillable tidak diisi?"
    // region demo:ct-10
    public function massAssignment(Request $request): JsonResponse
    {
        $allowed = ['name', 'price'];
        $input = $request->all();
        $accepted = array_intersect_key($input, array_flip($allowed));
        $ignored = array_diff_key($input, array_flip($allowed));
        $this->trace->add('controller', 'DemoRouteController@massAssignment', 'lolos', 'Simulasi $fillable: hanya field allowlist yang diterima.');

        return $this->ok('Simulasi mass assignment', [
            'input' => $input,
            'fillable' => $allowed,
            'diterima' => $accepted,
            'diabaikan' => $ignored,
            'catatan' => 'Dengan Eloquent, field di luar $fillable tidak masuk ke create()/update().',
        ]);
    }
    // endregion demo:ct-10

    public function arrayResponse(Request $request): array
    {
        $this->trace->add('controller', 'DemoRouteController@arrayResponse', 'lolos', 'Controller mengembalikan array biasa; Laravel mengubahnya menjadi JSON.');

        return [
            'status' => 'success',
            'message' => 'Controller mengembalikan array PHP biasa.',
            'data' => [
                'method' => $request->method(),
                'content_type' => 'application/json (otomatis)',
            ],
        ];
    }

    public function envelope(): JsonResponse
    {
        return $this->ok('Contoh envelope JSON konsisten', [
            'contoh_sukses' => ['status' => 'success', 'message' => '...', 'data' => []],
            'contoh_error' => ['status' => 'error', 'message' => '...', 'data' => null, 'errors' => []],
        ]);
    }

    private function ok(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }
}

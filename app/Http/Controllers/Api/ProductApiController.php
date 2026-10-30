<?php

namespace App\Http\Controllers\Api;

use App\Demo\Models\Product;
use App\Demo\Trace\RequestTrace;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /*
    | ProductService disuntik lewat constructor. Tidak ada new ProductService()
    | di dalam method, sehingga mudah diganti atau diuji.
    */
    public function __construct(
        private ProductService $products,
        private RequestTrace $trace,
    ) {}

    // [CODE-REVIEW:ct-02] Resource controller: index + pagination
    // Baca : index mengembalikan koleksi dengan meta pagination.
    // Coba : buka /api/products?page=2, lalu bandingkan /api/products/1.
    // Lihat: Modul C -> "CRUD & pagination"
    // region demo:ct-02
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $paginator = $this->products->paginate($page, 10);
        $this->trace->add(
            'controller',
            'ProductApiController@index',
            'lolos',
            'Pagination 10 produk per halaman; total '.$paginator['total'].'.',
            meta: ['page' => $paginator['page']],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar produk',
            'data' => $paginator['items'],
            'meta' => [
                'current_page' => $paginator['page'],
                'per_page' => $paginator['per_page'],
                'total' => $paginator['total'],
                'last_page' => $paginator['last_page'],
            ],
        ]);
    }
    // endregion demo:ct-02

    // [CODE-REVIEW:ct-03] Validasi request dan response 422
    // Baca : validate() menghentikan request sebelum create jika input salah.
    // Coba : POST name kosong atau price negatif tanpa header Accept JSON.
    // Lihat: Modul C -> "Validasi"
    // region demo:ct-03
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = $this->products->create($validated);
        $this->trace->add('controller', 'ProductApiController@store', 'lolos', 'Validasi lolos; produk dibuat.');

        return response()->json([
            'status' => 'success',
            'message' => 'Produk dibuat',
            'data' => $product,
        ], 201);
    }
    // endregion demo:ct-03

    public function show(Product $product): JsonResponse
    {
        $this->trace->add(
            'controller',
            'ProductApiController@show',
            'lolos',
            'Route Model Binding mengubah {product} menjadi Product #'.$product->id.'.',
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk',
            'data' => $product,
        ]);
    }

    // [CODE-REVIEW:ct-08] Aturan sometimes pada update
    // Baca : field yang tidak dikirim tidak divalidasi dan tidak diubah.
    // Coba : PATCH body {} (200), lalu PATCH {"name":""} (422).
    // Lihat: Modul C -> "PUT, PATCH, dan sometimes"
    // region demo:ct-08
    public function update(Request $request, Product $product): JsonResponse
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
        $this->trace->add(
            'controller',
            'ProductApiController@update',
            'lolos',
            $validated === []
                ? 'Tidak ada field yang dikirim; sometimes membuat semua aturan dilewati.'
                : 'Field yang dikirim: '.implode(', ', array_keys($validated)).'.',
            meta: ['fields' => array_keys($validated)],
        );

        return response()->json([
            'status' => 'success',
            'message' => $validated === [] ? 'Tidak ada field untuk diubah' : 'Produk diperbarui',
            'data' => $updated,
            'meta' => [
                'method' => $request->method(),
                'fields_dikirim' => array_keys($validated),
                'catatan' => 'PUT dan PATCH sama-sama diarahkan ke update(); aturan sometimes membuat keduanya menerima body sebagian.',
            ],
        ]);
    }
    // endregion demo:ct-08

    // [CODE-REVIEW:ct-09] destroy mengembalikan 204 tanpa body
    // Baca : response()->noContent() menghasilkan status 204 dan body kosong.
    // Coba : DELETE /api/products/2, lihat header lalu GET /api/products/2.
    // Lihat: Modul C -> "Jenis response"
    // region demo:ct-09
    public function destroy(Product $product): \Illuminate\Http\Response
    {
        $this->products->delete($product->id);
        $this->trace->add('controller', 'ProductApiController@destroy', 'lolos', 'Produk dihapus; response 204 tanpa body.');

        return response()->noContent();
    }
    // endregion demo:ct-09

    public function showById(string $id): JsonResponse
    {
        $product = $this->products->find($id);

        if (! $product) {
            $this->trace->add('controller', 'ProductApiController@showById', 'berhenti', 'Produk manual tidak ditemukan.');
            abort(404, 'Produk dengan id "'.$id.'" tidak ditemukan (pencarian manual).');
        }

        $this->trace->add('controller', 'ProductApiController@showById', 'lolos', 'Parameter {id} diterima sebagai string lalu dicari manual.');

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk (parameter string, tanpa binding)',
            'data' => $product,
            'meta' => ['mode' => 'manual string $id', 'id_type' => get_debug_type($id)],
        ]);
    }

    // [CODE-REVIEW:ct-07] Parameter opsional {category?}
    // Baca : route yang sama melayani tanpa dan dengan kategori.
    // Coba : /api/demo/products-by-category dan .../minuman.
    // Lihat: Modul C -> "Parameter opsional"
    // region demo:ct-07
    public function byCategory(?string $category = null): JsonResponse
    {
        $products = $this->products->byCategory($category);
        $this->trace->add(
            'controller',
            'ProductApiController@byCategory',
            'lolos',
            $category === null ? 'Kategori tidak dikirim; $category = null.' : 'Kategori: '.$category,
        );

        return response()->json([
            'status' => 'success',
            'message' => $category === null ? 'Semua produk (kategori opsional tidak diisi)' : 'Produk kategori '.$category,
            'data' => $products,
            'meta' => [
                'category' => $category,
                'jumlah' => count($products),
                'categories_tersedia' => $this->products->categories(),
            ],
        ]);
    }
    // endregion demo:ct-07

    public function search(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $results = $this->products->search($keyword);
        $this->trace->add('controller', 'ProductApiController@search', 'lolos', 'Method injection Request + service hasil constructor injection.');

        return response()->json([
            'status' => 'success',
            'message' => 'Hasil pencarian',
            'data' => $results,
            'meta' => [
                'q' => $keyword,
                'jumlah' => count($results),
                'injection' => [
                    'constructor' => ProductService::class,
                    'method' => \Illuminate\Http\Request::class,
                ],
            ],
        ]);
    }

    // [CODE-REVIEW:ct-05] Dependency Injection
    // Baca : service lewat constructor, Request lewat method.
    // Coba : bandingkan urutan parameter Product $product dan Request $request.
    // Lihat: Modul C -> "Dependency Injection"
    // region demo:ct-05
    public function di(Request $request, ProductService $service): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $this->trace->add(
            'controller',
            'ProductApiController@di',
            'lolos',
            'ProductService disuntik lewat constructor; Request disuntik lewat method.',
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Contoh dependency injection',
            'data' => [
                'constructor_injection' => [
                    'class' => ProductService::class,
                    'cara' => '__construct(ProductService $products)',
                ],
                'method_injection' => [
                    'class' => \Illuminate\Http\Request::class,
                    'cara' => 'di(Request $request)',
                    'q_dibaca' => $keyword,
                ],
                'method_injection_tambahan' => [
                    'class' => get_class($service),
                    'cara' => 'Laravel juga dapat menginjeksi service pada method',
                ],
                'jumlah_hasil' => count($service->search($keyword)),
            ],
        ]);
    }
    // endregion demo:ct-05
}

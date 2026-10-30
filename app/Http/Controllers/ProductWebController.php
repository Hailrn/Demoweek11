<?php

namespace App\Http\Controllers;

use App\Demo\Models\Product;
use App\Demo\Trace\RequestTrace;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/*
| Resource controller versi web: memakai view dan redirect.
| CRUD utama di GUI modul C memakai API agar response mentah mudah dilihat.
*/
class ProductWebController extends Controller
{
    public function __construct(
        private ProductService $products,
        private RequestTrace $trace,
    ) {}

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->query('page', 1));
        $paginator = $this->products->paginate($page);
        $this->trace->add('controller', 'ProductWebController@index', 'lolos', 'Menampilkan daftar produk (10 per halaman).');

        return view('products.index', [
            'paginator' => $paginator,
            'summary' => $this->products->summary(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => null,
            'categories' => $this->products->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $product = $this->products->create($validated);

        return redirect()
            ->route('web.products.show', $product->id)
            ->with('success', 'Produk "'.$product->name.'" dibuat dengan status 201 di API; di web hasilnya redirect.');
    }

    public function show(Product $product): View
    {
        $this->trace->add('controller', 'ProductWebController@show', 'lolos', 'Route Model Binding menemukan produk #'.$product->id.'.');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => $this->products->categories(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $this->products->update($product->id, $validated);

        return redirect()
            ->route('web.products.show', $product->id)
            ->with('success', 'Produk diperbarui melalui form web (redirect + flash session).');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product->id);

        return redirect()
            ->route('web.products.index')
            ->with('success', 'Produk dihapus. API mengembalikan 204 tanpa body; web mengembalikan redirect.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}

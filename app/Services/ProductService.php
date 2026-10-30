<?php

namespace App\Services;

use App\Demo\Models\Product;
use App\Demo\Store\DemoStore;

/*
| Logika produk dipisahkan dari controller. Controller menerima class ini
| lewat constructor (constructor injection), persis pola pada materi.
*/
class ProductService
{
    public function __construct(private DemoStore $store) {}

    /**
     * @return array<int, Product>
     */
    public function all(): array
    {
        return $this->store->all();
    }

    public function paginate(int $page = 1, int $perPage = 10): array
    {
        return $this->store->paginate($page, $perPage);
    }

    public function find(int|string $id): ?Product
    {
        return $this->store->find($id);
    }

    public function create(array $data): Product
    {
        return $this->store->create($data);
    }

    public function update(int|string $id, array $data, bool $replace = false): ?Product
    {
        return $this->store->update($id, $data, $replace);
    }

    public function delete(int|string $id): bool
    {
        return $this->store->delete($id);
    }

    public function count(): int
    {
        return $this->store->count();
    }

    /**
     * @return array<int, Product>
     */
    public function search(?string $keyword): array
    {
        return $this->store->search((string) $keyword);
    }

    /**
     * @return array<int, Product>
     */
    public function byCategory(?string $category): array
    {
        return $this->store->byCategory($category);
    }

    public function categories(): array
    {
        $categories = array_map(fn (Product $product) => $product->category, $this->all());
        $categories = array_values(array_unique($categories));
        sort($categories);

        return $categories;
    }

    public function summary(): array
    {
        $products = $this->all();
        $totalValue = array_reduce(
            $products,
            fn (float $carry, Product $product) => $carry + ($product->price * $product->stock),
            0.0,
        );

        return [
            'total_products' => count($products),
            'total_categories' => count($this->categories()),
            'total_stock' => array_sum(array_map(fn (Product $product) => $product->stock, $products)),
            'inventory_value' => $totalValue,
            'store_path' => $this->store->path(),
        ];
    }
}

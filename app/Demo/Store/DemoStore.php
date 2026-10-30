<?php

namespace App\Demo\Store;

use App\Demo\Models\Product;
use RuntimeException;

/*
|--------------------------------------------------------------------------
| DemoStore
|--------------------------------------------------------------------------
| State demo disimpan di file JSON. Semua akses file memakai flock agar
| request GUI dan curl tidak saling menimpa saat berjalan bersamaan.
| Tidak ada database, migration, atau Eloquent di sini.
*/
final class DemoStore
{
    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? config('demo.state_path', storage_path('app/demo/state.json'));
    }

    public function path(): string
    {
        return $this->path;
    }

    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @return array<int, Product>
     */
    public function all(): array
    {
        return array_map(
            fn (array $product) => new Product($product),
            $this->state()['products'] ?? []
        );
    }

    public function find(int|string $id): ?Product
    {
        foreach ($this->all() as $product) {
            if ((string) $product->id === (string) $id) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return array{items: array<int, Product>, total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $products = $this->all();
        $total = count($products);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        return [
            'items' => array_slice($products, ($page - 1) * $perPage, $perPage),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * @return array<int, Product>
     */
    public function byCategory(?string $category): array
    {
        if ($category === null || $category === '') {
            return $this->all();
        }

        return array_values(array_filter(
            $this->all(),
            fn (Product $product) => mb_strtolower($product->category) === mb_strtolower($category)
        ));
    }

    public function search(string $keyword): array
    {
        $keyword = mb_strtolower(trim($keyword));

        if ($keyword === '') {
            return $this->all();
        }

        return array_values(array_filter($this->all(), function (Product $product) use ($keyword) {
            return str_contains(mb_strtolower($product->name), $keyword)
                || str_contains(mb_strtolower($product->category), $keyword)
                || str_contains(mb_strtolower($product->description), $keyword);
        }));
    }

    public function create(array $data): Product
    {
        return $this->mutate(function (array $state) use ($data) {
            $nextId = 1;
            foreach ($state['products'] as $product) {
                $nextId = max($nextId, (int) $product['id'] + 1);
            }

            $now = now()->toIso8601String();
            $product = Product::fromInput($data, $nextId);
            $product->created_at = $now;
            $product->updated_at = $now;

            $state['products'][] = $product->toArray();
            $state['meta']['updated_at'] = $now;

            return [$state, $product];
        });
    }

    public function update(int|string $id, array $data, bool $replace = false): ?Product
    {
        return $this->mutate(function (array $state) use ($id, $data, $replace) {
            foreach ($state['products'] as $index => $existing) {
                if ((string) $existing['id'] !== (string) $id) {
                    continue;
                }

                $current = new Product($existing);
                $updated = $replace
                    ? Product::fromInput($data, $current->id)
                    : $current->fill($data);

                $updated->created_at = $current->created_at;
                $updated->updated_at = now()->toIso8601String();

                $state['products'][$index] = $updated->toArray();
                $state['meta']['updated_at'] = $updated->updated_at;

                return [$state, $updated];
            }

            return [$state, null];
        });
    }

    public function delete(int|string $id): bool
    {
        return $this->mutate(function (array $state) use ($id) {
            $before = count($state['products']);
            $state['products'] = array_values(array_filter(
                $state['products'],
                fn (array $product) => (string) $product['id'] !== (string) $id
            ));

            if (count($state['products']) === $before) {
                return [$state, false];
            }

            $state['meta']['updated_at'] = now()->toIso8601String();

            return [$state, true];
        });
    }

    public function reset(): void
    {
        $seed = require app_path('Demo/Data/seed.php');

        $this->writeState([
            'products' => $seed['products'],
            'meta' => [
                'seeded_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'source' => 'app/Demo/Data/seed.php',
            ],
        ]);
    }

    public function state(): array
    {
        $this->ensureFileExists();

        $handle = fopen($this->path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('State demo tidak bisa dibuka: '.$this->path);
        }

        try {
            flock($handle, LOCK_SH);
            $contents = stream_get_contents($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        $decoded = json_decode((string) $contents, true);

        if (! is_array($decoded) || ! isset($decoded['products'])) {
            throw new RuntimeException('File state demo rusak. Klik Reset data demo.');
        }

        return $decoded;
    }

    private function mutate(callable $callback): mixed
    {
        $this->ensureFileExists();
        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('State demo tidak bisa dibuka: '.$this->path);
        }

        try {
            flock($handle, LOCK_EX);
            rewind($handle);
            $contents = stream_get_contents($handle);
            $state = json_decode((string) $contents, true);

            if (! is_array($state) || ! isset($state['products'])) {
                throw new RuntimeException('File state demo rusak. Klik Reset data demo.');
            }

            [$state, $result] = $callback($state);

            $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($json === false) {
                throw new RuntimeException('Gagal mengubah state demo menjadi JSON.');
            }

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, $json);
            fflush($handle);
            flock($handle, LOCK_UN);

            return $result;
        } finally {
            fclose($handle);
        }
    }

    private function writeState(array $state): void
    {
        $directory = dirname($this->path);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder state demo tidak bisa dibuat: '.$directory);
        }

        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Gagal membuat JSON seed demo.');
        }

        $temporary = $this->path.'.tmp';
        file_put_contents($temporary, $json, LOCK_EX);
        rename($temporary, $this->path);
    }

    private function ensureFileExists(): void
    {
        if (is_file($this->path)) {
            return;
        }

        $this->reset();
    }
}

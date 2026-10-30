<?php

namespace App\Demo\Models;

use App\Demo\Store\DemoStore;
use Illuminate\Contracts\Routing\UrlRoutable;
use JsonSerializable;

/*
|--------------------------------------------------------------------------
| Product (bukan model Eloquent)
|--------------------------------------------------------------------------
| Di project dengan database, class ini biasanya extends Model dan route
| model binding otomatis memakai Eloquent. Di demo ini Product membaca array
| lokal lewat DemoStore, tetapi tetap mengimplementasikan UrlRoutable supaya
| implicit binding Product $product tetap bekerja dan 404 otomatis.
*/
class Product implements UrlRoutable, JsonSerializable
{
    public int $id;

    public string $name;

    public string $category;

    public float $price;

    public int $stock;

    public string $description;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /*
    | Parameter $attributes sengaja punya nilai default. Laravel membuat instance
    | model lewat container sebelum memanggil resolveRouteBinding(); constructor
    | tanpa default akan gagal di-resolve saat implicit binding berjalan.
    */
    public function __construct(array $attributes = [])
    {
        $this->id = (int) ($attributes['id'] ?? 0);
        $this->name = (string) ($attributes['name'] ?? '');
        $this->category = (string) ($attributes['category'] ?? '');
        $this->price = (float) ($attributes['price'] ?? 0);
        $this->stock = (int) ($attributes['stock'] ?? 0);
        $this->description = (string) ($attributes['description'] ?? '');
        $this->created_at = $attributes['created_at'] ?? null;
        $this->updated_at = $attributes['updated_at'] ?? null;
    }

    public static function fromInput(array $data, int $id = 0): self
    {
        return new self([
            'id' => $id,
            'name' => (string) ($data['name'] ?? ''),
            'category' => (string) ($data['category'] ?? 'umum'),
            'price' => (float) ($data['price'] ?? 0),
            'stock' => (int) ($data['stock'] ?? 0),
            'description' => (string) ($data['description'] ?? ''),
        ]);
    }

    /**
     * Mengubah sebagian field, dipakai untuk PATCH dan simulasi $fillable.
     */
    public function fill(array $data): self
    {
        $allowed = ['name', 'category', 'price', 'stock', 'description'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $this->{$field} = match ($field) {
                    'price' => (float) $data[$field],
                    'stock' => (int) $data[$field],
                    default => (string) $data[$field],
                };
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'stock' => $this->stock,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // [CODE-REVIEW:ct-04] Route Model Binding tanpa database
    // Baca : implicit binding memanggil resolveRouteBinding() lalu 404 jika null.
    // Coba : buka /api/products/999 dan bandingkan dengan /api/demo/products-by-id/999.
    // Lihat: Modul C -> "Route Model Binding"
    // region demo:ct-04
    public function getRouteKey(): mixed
    {
        return $this->id;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return app(DemoStore::class)->find($value);
    }

    public function resolveChildRouteBinding($childType, $value, $field): ?self
    {
        return null;
    }
    // endregion demo:ct-04
}

<?php

namespace Tests\Feature;

use App\Demo\Store\DemoStore;
use App\Services\ProductService;
use Tests\TestCase;

class DemoStateTest extends TestCase
{
    public function test_reset_idempoten_mengembalikan_seed(): void
    {
        $store = app(DemoStore::class);
        $products = app(ProductService::class);

        $products->create([
            'name' => 'Produk Sementara',
            'category' => 'uji',
            'price' => 1000,
            'stock' => 1,
            'description' => '',
        ]);

        $this->assertSame(15, $store->count());

        $store->reset();
        $store->reset();

        $this->assertSame(14, $store->count());
        $this->assertSame('Kopi Arabika Gayo', $store->find(1)?->name);
        $this->assertNull($store->find(15));

        $state = json_decode((string) file_get_contents($store->path()), true);
        $this->assertIsArray($state);
        $this->assertCount(14, $state['products']);
        $this->assertSame('app/Demo/Data/seed.php', $state['meta']['source']);
    }

    public function test_reset_lewat_api_dan_web(): void
    {
        app(ProductService::class)->delete(1);
        $this->assertSame(13, app(DemoStore::class)->count());

        $this->postJson('/api/demo/reset')
            ->assertOk()
            ->assertJsonPath('data.products', 14);

        $this->assertSame(14, app(DemoStore::class)->count());

        app(ProductService::class)->delete(2);

        $this->from('/dashboard')
            ->post('/reset-demo')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('success');

        $this->assertSame(14, app(DemoStore::class)->count());
    }

    public function test_store_update_dan_delete_bekerja_dengan_file_json(): void
    {
        $store = app(DemoStore::class);

        $created = $store->create([
            'name' => 'Langsung Store',
            'category' => 'uji',
            'price' => 5000,
            'stock' => 3,
            'description' => 'tes',
        ]);

        $this->assertSame(15, $created->id);
        $this->assertNotNull($created->created_at);

        $updated = $store->update($created->id, ['stock' => 10, 'name' => 'Diubah']);

        $this->assertSame('Diubah', $updated?->name);
        $this->assertSame(10, $updated?->stock);
        $this->assertSame($created->created_at, $updated?->created_at);

        $this->assertTrue($store->delete($created->id));
        $this->assertFalse($store->delete($created->id));
        $this->assertNull($store->find($created->id));
    }

    public function test_path_state_test_terpisah_dari_state_browser(): void
    {
        $path = app(DemoStore::class)->path();

        $this->assertSame(storage_path('framework/testing/demo-state.json'), $path);
        $this->assertFileExists($path);
    }
}

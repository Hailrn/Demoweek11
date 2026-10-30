<?php

namespace Tests\Feature;

use App\Demo\Store\DemoStore;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    public function test_index_memakai_pagination_dari_array_lokal(): void
    {
        $pageOne = $this->getJson('/api/products');

        $pageOne->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 14)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(10, 'data');

        $this->getJson('/api/products?page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(4, 'data');

        // Halaman di luar batas dijepit ke halaman terakhir, bukan 404.
        $this->getJson('/api/products?page=999')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_route_model_binding_menghasilkan_200_dan_404(): void
    {
        $this->getJson('/api/products/1')
            ->assertOk()
            ->assertJsonPath('data.name', 'Kopi Arabika Gayo');

        $this->assertApiError($this->getJson('/api/products/999'), 404);
        $this->assertApiError($this->getJson('/api/products/abc'), 404);
    }

    public function test_store_validasi_dan_response_201(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Produk Uji',
            'category' => 'uji',
            'price' => 12345,
            'stock' => 7,
            'description' => 'Dibuat dari test.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 15)
            ->assertJsonPath('data.name', 'Produk Uji');

        $this->getJson('/api/products/15')
            ->assertOk()
            ->assertJsonPath('data.stock', 7);

        $this->assertSame(15, app(DemoStore::class)->count());
    }

    public function test_validasi_gagal_mengembalikan_422_dan_errors(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => '',
            'category' => '',
            'price' => -1,
            'stock' => 'bukan-angka',
        ]);

        $this->assertApiError($response, 422);
        $response->assertJsonValidationErrors(['name', 'category', 'price', 'stock']);
    }

    public function test_patch_body_kosong_tidak_mengubah_timestamp(): void
    {
        $before = $this->getJson('/api/products/1')->json('data');

        $this->patchJson('/api/products/1', [])
            ->assertOk()
            ->assertJsonPath('message', 'Tidak ada field untuk diubah')
            ->assertJsonPath('data.updated_at', $before['updated_at'])
            ->assertJsonPath('data.name', $before['name']);
    }

    public function test_patch_hanya_mengubah_field_yang_dikirim(): void
    {
        $this->patchJson('/api/products/1', ['stock' => 99])
            ->assertOk()
            ->assertJsonPath('data.stock', 99)
            ->assertJsonPath('data.name', 'Kopi Arabika Gayo')
            ->assertJsonPath('meta.fields_dikirim', ['stock']);

        $this->patchJson('/api/products/1', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_put_dan_patch_sama_sama_masuk_ke_update_dengan_sometimes(): void
    {
        $this->putJson('/api/products/1', ['price' => 4321])
            ->assertOk()
            ->assertJsonPath('meta.method', 'PUT')
            ->assertJsonPath('data.price', 4321)
            ->assertJsonPath('data.name', 'Kopi Arabika Gayo');
    }

    public function test_semantic_put_mengganti_seluruh_resource_dan_patch_sebagian(): void
    {
        $this->putJson('/api/demo/semantic-put/1', [
            'name' => 'Kopi Full',
            'category' => 'minuman',
            'price' => 1,
            'stock' => 2,
            'description' => 'full replacement',
        ])->assertOk()
            ->assertJsonPath('data.data.name', 'Kopi Full')
            ->assertJsonPath('data.data.price', 1);

        $this->patchJson('/api/demo/semantic-patch/1', ['stock' => 3])
            ->assertOk()
            ->assertJsonPath('data.data.stock', 3)
            ->assertJsonPath('data.data.name', 'Kopi Full')
            ->assertJsonPath('data.fields_dikirim', ['stock']);
    }

    public function test_delete_mengembalikan_204_tanpa_body(): void
    {
        $response = $this->deleteJson('/api/products/2');

        $response->assertNoContent();
        $this->assertSame('', $response->getContent());

        $this->assertApiError($this->getJson('/api/products/2'), 404);
        $this->assertSame(13, app(DemoStore::class)->count());
    }

    public function test_dependency_injection_dan_service(): void
    {
        $this->getJson('/api/demo/di?q=kopi')
            ->assertOk()
            ->assertJsonPath('data.method_injection.q_dibaca', 'kopi')
            ->assertJsonPath('data.method_injection_tambahan.class', \App\Services\ProductService::class)
            ->assertJsonPath('data.jumlah_hasil', 2);

        $this->getJson('/api/demo/search?q=tidak-ada')
            ->assertOk()
            ->assertJsonPath('meta.jumlah', 0);
    }

    public function test_mass_assignment_hanya_menerima_allowlist(): void
    {
        $this->postJson('/api/demo/mass-assignment', [
            'name' => 'Produk Baru',
            'price' => 10000,
            'is_admin' => true,
        ])->assertOk()
            ->assertJsonPath('data.diterima', ['name' => 'Produk Baru', 'price' => 10000])
            ->assertJsonPath('data.diabaikan.is_admin', true);
    }

    public function test_validasi_web_mengembalikan_redirect_dan_errors(): void
    {
        $this->from('/web/products/create')
            ->post('/web/products', [
                'name' => '',
                'category' => '',
                'price' => -1,
                'stock' => 0,
            ])
            ->assertRedirect('/web/products/create')
            ->assertSessionHasErrors(['name', 'category', 'price']);
    }
}

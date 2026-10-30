<?php

namespace Tests\Feature;

use App\Demo\Store\DemoStore;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductWebTest extends TestCase
{
    public function test_resource_web_memiliki_tujuh_route_dan_update_put_patch(): void
    {
        $names = [
            'web.products.index',
            'web.products.create',
            'web.products.store',
            'web.products.show',
            'web.products.edit',
            'web.products.update',
            'web.products.destroy',
        ];

        foreach ($names as $name) {
            $this->assertNotNull(Route::getRoutes()->getByName($name), "Route {$name} tidak ada.");
        }

        $update = Route::getRoutes()->getByName('web.products.update');
        $this->assertSame(['PUT', 'PATCH'], array_values(array_diff($update->methods(), ['HEAD'])));
        $this->assertNotContains('UPDATE', $update->methods());
    }

    public function test_halaman_index_create_show_edit(): void
    {
        $this->get('/web/products')
            ->assertOk()
            ->assertSee('14 total')
            ->assertSee('Kopi Arabika Gayo');

        $this->get('/web/products/create')
            ->assertOk()
            ->assertSee('Tambah produk');

        $this->get('/web/products/1')
            ->assertOk()
            ->assertSee('Kopi Arabika Gayo')
            ->assertSee('resolveRouteBinding');

        $this->get('/web/products/1/edit')
            ->assertOk()
            ->assertSee('_method=PUT')
            ->assertSee('Kopi Arabika Gayo');
    }

    public function test_show_produk_tidak_ada_menghasilkan_404(): void
    {
        $this->get('/web/products/999')->assertNotFound();
        $this->get('/web/products/999/edit')->assertNotFound();
    }

    public function test_store_web_mengarahkan_ke_halaman_detail(): void
    {
        $response = $this->post('/web/products', [
            'name' => 'Produk Web',
            'category' => 'web',
            'price' => 1000,
            'stock' => 2,
            'description' => 'Dibuat lewat form web.',
        ]);

        $response->assertRedirect(route('web.products.show', 15));
        $response->assertSessionHas('success');

        $this->get('/web/products/15')
            ->assertOk()
            ->assertSee('Produk Web');

        $this->assertSame('Produk Web', app(DemoStore::class)->find(15)?->name);
    }

    public function test_update_dan_destroy_web(): void
    {
        $this->put('/web/products/1', [
            'name' => 'Kopi Setelah Edit',
            'category' => 'minuman',
            'price' => 90000,
            'stock' => 10,
            'description' => 'Diubah lewat form.',
        ])->assertRedirect(route('web.products.show', 1))
            ->assertSessionHas('success');

        $this->assertSame('Kopi Setelah Edit', app(DemoStore::class)->find(1)?->name);

        $this->delete('/web/products/2')
            ->assertRedirect(route('web.products.index'))
            ->assertSessionHas('success');

        $this->assertNull(app(DemoStore::class)->find(2));
        $this->assertSame(13, app(DemoStore::class)->count());
    }

    public function test_validasi_web_mengembalikan_input_dan_errors(): void
    {
        $this->from('/web/products/create')
            ->post('/web/products', [
                'name' => '',
                'category' => '',
                'price' => -1,
                'stock' => 'x',
            ])
            ->assertRedirect('/web/products/create')
            ->assertSessionHasErrors(['name', 'category', 'price', 'stock']);

        $this->assertSame(14, app(DemoStore::class)->count());
    }

    public function test_method_spoofing_dan_csrf_di_grup_web(): void
    {
        $this->post('/demo/spoof', [
            '_method' => 'PUT',
            'name' => 'Dari form',
        ])->assertOk()
            ->assertJsonPath('data.method_setelah_spoofing', 'PUT')
            ->assertJsonPath('data.input_method', 'PUT');
    }
}

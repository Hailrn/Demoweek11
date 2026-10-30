<?php

namespace Tests\Feature;

use App\Demo\Store\DemoStore;
use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_halaman_utama_dan_modul_bisa_dibuka(): void
    {
        $pages = [
            '/' => 'Route, Middleware & Controller',
            '/belajar/route' => 'Method menentukan method controller',
            '/belajar/middleware' => 'Matriks role pada area admin',
            '/belajar/controller' => 'Product studio',
            '/faq' => 'FAQ interaktif',
            '/code-review' => 'Code Review',
            '/dashboard' => 'Dashboard',
            '/web/products' => 'Daftar produk',
            '/web/products/create' => 'Tambah produk',
            '/web/products/1' => 'Kopi Arabika Gayo',
            '/web/products/1/edit' => 'Edit produk',
        ];

        foreach ($pages as $path => $text) {
            $this->get($path)
                ->assertOk()
                ->assertSee($text)
                ->assertHeader('X-Demo-Global', 'aktif');
        }
    }

    public function test_setiap_halaman_membawa_header_trace_dan_tautan_kode(): void
    {
        foreach (['/', '/belajar/route', '/belajar/middleware', '/belajar/controller'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $this->assertNotEmpty($this->traceFrom($response)['entries']);
        }
    }

    public function test_dashboard_summary_memakai_data_seed_yang_sama(): void
    {
        $response = $this->getJson('/dashboard/summary');

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'total_products' => 14,
                'total_categories' => 3,
            ],
        ]);

        $count = app(DemoStore::class)->count();
        $this->assertSame(14, $count);
    }

    public function test_halaman_error_web_tidak_menampilkan_stack_trace(): void
    {
        $response = $this->get('/halaman-yang-tidak-ada');

        $response->assertNotFound();
        $response->assertSee('Halaman atau resource tidak ditemukan');
        $response->assertDontSee('Whoops');
        $response->assertDontSee('BindingResolutionException');
    }

    public function test_error_api_memakai_envelope_json(): void
    {
        $response = $this->getJson('/api/demo/tidak-ada');

        $this->assertApiError($response, 404);
    }

    public function test_semua_route_aplikasi_punya_nama_unik(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $appRoutes = $routes->filter(
            fn ($route) => str_starts_with((string) $route->getActionName(), 'App\\')
        );

        $names = $appRoutes->map(fn ($route) => $route->getName())->filter();
        $duplicates = $names->duplicates();

        $this->assertCount(0, $duplicates, 'Nama route ganda: '.$duplicates->implode(', '));
        $this->assertCount($appRoutes->count(), $names->unique());
        $this->assertGreaterThan(50, $appRoutes->count());
    }
}

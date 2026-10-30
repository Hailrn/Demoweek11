<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteDemoTest extends TestCase
{
    public function test_get_dan_post_memilih_route_berbeda(): void
    {
        $this->getJson('/api/demo/hello')
            ->assertOk()
            ->assertJsonPath('data.method', 'GET');

        $this->postJson('/api/demo/echo', ['message' => 'Halo'])
            ->assertOk()
            ->assertJsonPath('data.method', 'POST')
            ->assertJsonPath('data.body.message', 'Halo');
    }

    public function test_method_salah_menghasilkan_405_bukan_404(): void
    {
        $response = $this->postJson('/api/demo/hello');

        $this->assertApiError($response, 405);
        $response->assertHeader('Allow', 'GET, HEAD');
    }

    public function test_match_membatasi_method_sedangkan_any_menerima_banyak_method(): void
    {
        $this->deleteJson('/api/demo/echo')->assertStatus(405);
        $this->deleteJson('/api/demo/webhook')
            ->assertOk()
            ->assertJsonPath('data.method', 'DELETE');

        $this->postJson('/api/demo/products/search', ['q' => 'kopi'])
            ->assertOk()
            ->assertJsonPath('data.method', 'POST');
    }

    public function test_route_statis_menang_atas_route_dinamis(): void
    {
        $this->getJson('/api/demo/order/special')
            ->assertOk()
            ->assertJsonPath('data.route', '/api/demo/order/special');

        $this->getJson('/api/demo/order/apa-saja')
            ->assertOk()
            ->assertJsonPath('data.slug', 'apa-saja');
    }

    public function test_constraint_where_menolak_sebelum_controller(): void
    {
        $this->getJson('/api/demo/where/abc')
            ->assertOk()
            ->assertJsonPath('data.id', 'abc');

        $this->getJson('/api/demo/where-numeric/42')
            ->assertOk()
            ->assertJsonPath('data.id', '42');

        $this->assertApiError($this->getJson('/api/demo/where-numeric/abc'), 404);
    }

    public function test_parameter_opsional_kategori(): void
    {
        $all = $this->getJson('/api/demo/products-by-category');

        $all->assertOk()
            ->assertJsonPath('meta.category', null)
            ->assertJsonPath('meta.jumlah', 14);

        $this->getJson('/api/demo/products-by-category/minuman')
            ->assertOk()
            ->assertJsonPath('meta.category', 'minuman')
            ->assertJsonPath('meta.jumlah', 5);

        $this->getJson('/api/demo/products-by-category/tidak-ada')
            ->assertOk()
            ->assertJsonPath('meta.jumlah', 0);
    }

    public function test_pencarian_manual_string_dan_hasil_pencarian(): void
    {
        $this->getJson('/api/demo/products-by-id/1')
            ->assertOk()
            ->assertJsonPath('data.id', '1')
            ->assertJsonPath('data.data.name', 'Kopi Arabika Gayo');

        $this->assertApiError($this->getJson('/api/demo/products-by-id/999'), 404);

        $this->getJson('/api/demo/search?q=kopi')
            ->assertOk()
            ->assertJsonPath('meta.jumlah', 2);
    }

    public function test_semua_method_http_umum_memiliki_perilaku_jelas(): void
    {
        $this->getJson('/api/demo/hello')->assertOk();
        $this->postJson('/api/demo/echo')->assertOk();
        $this->putJson('/api/demo/webhook')->assertOk();
        $this->patchJson('/api/demo/webhook')->assertOk();
        $this->deleteJson('/api/demo/webhook')->assertOk();
        $this->options('/api/demo/webhook')->assertOk();
    }

    public function test_options_mengirim_header_allow_dan_head_tanpa_body(): void
    {
        $this->options('/api/demo/hello')
            ->assertOk()
            ->assertHeader('Allow', 'GET,HEAD');

        $this->options('/api/products/1')
            ->assertOk()
            ->assertHeader('Allow', 'GET,HEAD,PUT,PATCH,DELETE');

        // Route::any menangkap OPTIONS sendiri, sehingga controller yang menjawab.
        $this->options('/api/demo/webhook')
            ->assertOk()
            ->assertJsonPath('data.method', 'OPTIONS');

        // HEAD diterima untuk route GET, tetapi body response kosong.
        $head = $this->head('/api/demo/hello')->assertOk();
        $this->assertSame('', $head->getContent());
    }

    public function test_request_web_inspect_memakai_session_dan_csrf(): void
    {
        $this->getJson('/demo/inspect-web')
            ->assertOk()
            ->assertJsonPath('data.session_started', true)
            ->assertJsonPath('data.csrf_token_ada', true);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResponseDemoTest extends TestCase
{
    public function test_lima_jenis_response_memiliki_status_dan_header_berbeda(): void
    {
        $this->get('/api/demo/response/view')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $this->getJson('/api/demo/response/json')
            ->assertOk()
            ->assertJsonPath('data.content_type', 'application/json');

        $this->get('/api/demo/response/redirect')
            ->assertStatus(302)
            ->assertRedirect(route('dashboard'));

        $this->get('/api/demo/response/download')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="produk-demo.txt"');

        $response = $this->get('/api/demo/response/no-content');
        $response->assertNoContent();
        $this->assertSame('', $response->getContent());
    }

    public function test_inspector_mengembalikan_metadata_tanpa_mengikuti_redirect(): void
    {
        $expected = [
            'view' => 200,
            'json' => 200,
            'redirect' => 302,
            'download' => 200,
            'no-content' => 204,
        ];

        foreach ($expected as $type => $status) {
            $response = $this->getJson('/api/demo/response/inspect/'.$type);

            $response->assertOk()
                ->assertJsonPath('data.type', $type)
                ->assertJsonPath('data.status_code', $status)
                ->assertJsonStructure(['data' => ['headers', 'body_size', 'body_preview']]);
        }

        $this->getJson('/api/demo/response/inspect/view')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('data.headers.content-type', 'text/html; charset=utf-8');

        $this->getJson('/api/demo/response/inspect/redirect')
            ->assertOk()
            ->assertJsonPath('data.target_url', route('dashboard'));

        $this->assertApiError($this->getJson('/api/demo/response/inspect/nope'), 404);
    }

    public function test_array_dan_envelope_json(): void
    {
        $this->getJson('/api/demo/array-response')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('data.method', 'GET');

        $this->getJson('/api/demo/envelope')
            ->assertOk()
            ->assertJsonStructure(['data' => ['contoh_sukses', 'contoh_error']]);
    }
}

<?php

namespace Tests;

use App\Demo\Store\DemoStore;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // State test terpisah dari state demo yang dipakai di browser.
        config(['demo.state_path' => storage_path('framework/testing/demo-state.json')]);

        app(DemoStore::class)->reset();
    }

    /**
     * Header X-Demo-Trace berisi base64url JSON. Helper ini memudahkan
     * pemeriksaan urutan middleware tanpa mengurai header di setiap test.
     */
    protected function traceFrom(TestResponse $response): array
    {
        $header = $response->headers->get('X-Demo-Trace');

        $this->assertNotNull($header, 'Header X-Demo-Trace tidak ditemukan.');

        $json = base64_decode(strtr($header, '-_', '+/'), true);

        $this->assertNotFalse($json, 'Header X-Demo-Trace bukan base64 yang valid.');

        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('entries', $decoded);

        return $decoded;
    }

    protected function traceLabels(TestResponse $response): array
    {
        return array_column($this->traceFrom($response)['entries'], 'label');
    }

    protected function assertApiError(TestResponse $response, int $status): void
    {
        $response->assertStatus($status);
        $response->assertJson([
            'status' => 'error',
            'data' => null,
        ]);
        $this->assertNotEmpty($response->json('message'));
    }
}

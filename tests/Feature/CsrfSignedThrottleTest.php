<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CsrfSignedThrottleTest extends TestCase
{
    public function test_csrf_ketat_menolak_tanpa_token_dan_menerima_token_yang_cocok(): void
    {
        $this->postJson('/demo/csrf-test', ['name' => 'tanpa token'])
            ->assertStatus(419);

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Sec-Fetch-Site' => 'same-origin',
        ])->postJson('/demo/csrf-test', ['name' => 'same origin tanpa token'])
            ->assertStatus(419);

        $this->get('/faq')->assertOk();
        $token = (string) session()->token();

        $this->withSession(['_token' => $token])
            ->postJson('/demo/csrf-test', ['name' => 'dengan token'], ['X-CSRF-TOKEN' => $token])
            ->assertOk()
            ->assertJsonPath('data.token_valid', true);

        $this->withSession(['_token' => $token])
            ->postJson('/demo/csrf-test', ['_token' => $token, 'name' => 'token di body'])
            ->assertOk();
    }

    public function test_signed_url_valid_tampered_dan_kedaluwarsa(): void
    {
        $valid = URL::temporarySignedRoute('demo.signed', now()->addMinutes(5));

        $this->get($valid)
            ->assertOk()
            ->assertSee('URL bertanda tangan valid');

        $tampered = $this->tamperSignature($valid);
        $this->get($tampered)->assertForbidden();

        $expired = URL::temporarySignedRoute('demo.signed', now()->subMinute());
        $this->get($expired)->assertForbidden();
    }

    public function test_throttle_lima_per_menit_menghasilkan_429_pada_request_keenam(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->getJson('/api/demo/throttle')
                ->assertOk()
                ->assertHeader('X-RateLimit-Limit', '5')
                ->assertHeader('X-RateLimit-Remaining', (string) (5 - $i));
        }

        $response = $this->getJson('/api/demo/throttle');

        $this->assertApiError($response, 429);
        $response->assertHeader('X-RateLimit-Remaining', '0');
        $response->assertHeader('Retry-After');
    }

    private function tamperSignature(string $url): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $signature = (string) ($query['signature'] ?? '');
        $this->assertNotSame('', $signature);

        $last = substr($signature, -1);
        $query['signature'] = substr($signature, 0, -1).($last === 'a' ? 'b' : 'a');

        return $parts['scheme'].'://'.$parts['host']
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .($parts['path'] ?? '/')
            .'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    public function test_guest_tidak_punya_user_dan_mendapat_401(): void
    {
        $response = $this->getJson('/api/admin/dashboard');

        $this->assertApiError($response, 401);
        $this->assertContains('Middleware auth', $this->traceLabels($response));
    }

    public function test_role_kasir_tidak_boleh_dashboard_admin(): void
    {
        $response = $this->withHeader('X-Demo-Role', 'kasir')
            ->getJson('/api/admin/dashboard');

        $this->assertApiError($response, 403);
        $this->assertContains('Middleware role:admin', $this->traceLabels($response));
    }

    public function test_admin_boleh_dashboard_dan_owner_boleh_laporan(): void
    {
        $this->withHeader('X-Demo-Role', 'admin')
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'admin');

        $this->withHeader('X-Demo-Role', 'owner')
            ->getJson('/api/admin/reports')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'owner');

        $this->withHeader('X-Demo-Role', 'kasir')
            ->getJson('/api/admin/reports')
            ->assertStatus(403);
    }

    public function test_group_admin_memakai_prefix_dan_dua_role(): void
    {
        $this->withHeader('X-Demo-Role', 'owner')
            ->getJson('/api/admin/area/info')
            ->assertOk()
            ->assertJsonPath('data.middleware_route.0', 'auth')
            ->assertJsonPath('data.middleware_route.1', 'role:admin,owner');
    }

    public function test_bearer_token_demo_dan_role_tidak_dikenal(): void
    {
        $this->withHeader('Authorization', 'Bearer demo-admin')
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'admin');

        $this->flushHeaders();

        $this->withHeader('X-Demo-Role', 'superadmin')
            ->getJson('/api/admin/dashboard')
            ->assertStatus(401);
    }

    public function test_urutan_auth_dan_role_mengubah_lapisan_yang_berhenti(): void
    {
        $authFirst = $this->getJson('/api/demo/middleware-order/auth-first');
        $authFirst->assertStatus(401);
        $authLabels = $this->traceLabels($authFirst);
        $this->assertContains('Middleware auth', $authLabels);
        $this->assertNotContains('Middleware role:admin', $authLabels);

        $roleFirst = $this->getJson('/api/demo/middleware-order/role-first');
        $roleFirst->assertStatus(401);
        $roleLabels = $this->traceLabels($roleFirst);
        $this->assertContains('Middleware role:admin', $roleLabels);
        $this->assertNotContains('Middleware auth', $roleLabels);
    }

    public function test_middleware_dapat_berhenti_sebelum_next(): void
    {
        $response = $this->withHeaders([
            'X-Demo-Role' => 'admin',
            'X-Demo-Stop-Before-Next' => '1',
        ])->getJson('/api/demo/middleware/stop');

        $response->assertOk()
            ->assertJsonPath('status', 'stopped')
            ->assertJsonPath('data.controller_dijalankan', false);

        $this->assertNotContains('MiddlewareDemoController@dashboard', $this->traceLabels($response));
    }

    public function test_role_global_dapat_memblokir_endpoint_publik(): void
    {
        $this->getJson('/api/demo/global-public')
            ->assertOk()
            ->assertJsonPath('data.terblokir', false);

        $this->withHeaders([
            'X-Demo-Role' => 'kasir',
            'X-Demo-Global-Role' => '1',
        ])->getJson('/api/demo/global-public')->assertStatus(403);

        $this->withHeaders([
            'X-Demo-Role' => 'admin',
            'X-Demo-Global-Role' => '1',
        ])->getJson('/api/demo/global-public')
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_alias_salah_ditangkap_tanpa_stack_trace(): void
    {
        $response = $this->getJson('/api/demo/middleware/missing-alias');

        $response->assertOk()
            ->assertJsonPath('data.simulasi', true);

        $this->assertStringContainsString('roleYangBelumTerdaftar', $response->json('data.error'));
    }

    public function test_role_dari_session_gui_dipakai_route_web(): void
    {
        $this->withSession(['demo_role' => 'guest'])
            ->getJson('/demo/session-admin')
            ->assertStatus(401);

        $this->withSession(['demo_role' => 'kasir'])
            ->getJson('/demo/session-admin')
            ->assertStatus(403);

        $this->withSession(['demo_role' => 'owner'])
            ->getJson('/demo/session-admin')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'owner')
            ->assertJsonPath('data.session_role', 'owner');
    }

    public function test_role_switcher_mengubah_session(): void
    {
        $this->post('/demo/role', ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('demo_role', 'admin');

        $this->getJson('/demo/session-admin')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'admin');

        $this->post('/demo/role', ['role' => 'superadmin'])
            ->assertRedirect()
            ->assertSessionHasErrors('role');
    }

    public function test_inspect_menampilkan_parameter_role_sebagai_array(): void
    {
        $this->withHeader('X-Demo-Role', 'kasir')
            ->getJson('/api/demo/middleware/inspect')
            ->assertOk()
            ->assertJsonPath('data.roles_parameter', ['admin', 'owner', 'kasir'])
            ->assertJsonPath('data.cara_menerima', 'handle(Request $request, Closure $next, string ...$roles)');
    }
}

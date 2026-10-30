<?php

namespace App\Http\Middleware;

use App\Demo\Auth\DemoRoleResolver;
use App\Demo\Trace\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Memasang user resolver demo pada request. Middleware ini ditambahkan ke
| grup web dan api, jadi berjalan setelah StartSession (web) tetapi sebelum
| middleware route seperti auth/role.
*/
class ResolveDemoUser
{
    public function __construct(
        private DemoRoleResolver $roles,
        private RequestTrace $trace,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $role = $this->roles->roleFromRequest($request);
        $user = $this->roles->userFor($role);

        $request->setUserResolver(fn () => $user);

        $this->trace->add(
            stage: 'group',
            label: 'Grup '.($request->is('api/*') ? 'api' : 'web').' → ResolveDemoUser',
            status: 'lolos',
            message: $user
                ? "User demo terpasang: {$user->name} ({$user->role})"
                : 'Tidak ada user (guest). $request->user() bernilai null.',
            meta: ['role' => $role],
        );

        return $next($request);
    }
}

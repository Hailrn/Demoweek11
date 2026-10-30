<?php

namespace App\Demo\Auth;

use Illuminate\Http\Request;

/*
| Sumber peran demo:
| 1. Header X-Demo-Role (utama untuk terminal/API)
| 2. Authorization: Bearer demo-admin (alternatif)
| 3. Session demo_role (dipilih lewat Role Switcher di GUI)
| Peran tidak dikenal selalu dianggap guest.
*/
final class DemoRoleResolver
{
    public function validRoles(): array
    {
        return array_keys(config('demo.roles', []));
    }

    public function roleFromRequest(Request $request, bool $allowSession = true): string
    {
        $headerRole = mb_strtolower(trim((string) $request->header('X-Demo-Role', '')));

        if (in_array($headerRole, $this->validRoles(), true)) {
            return $headerRole;
        }

        $bearer = (string) $request->bearerToken();

        if ($bearer !== '') {
            $tokenRole = config('demo.demo_tokens.'.$bearer);

            if (is_string($tokenRole) && in_array($tokenRole, $this->validRoles(), true)) {
                return $tokenRole;
            }
        }

        if ($allowSession && $request->hasSession()) {
            $sessionRole = (string) $request->session()->get('demo_role', 'guest');

            if (in_array($sessionRole, $this->validRoles(), true)) {
                return $sessionRole;
            }
        }

        return 'guest';
    }

    public function userFor(string $role): ?DemoUser
    {
        $data = config('demo.roles.'.$role);

        if (! is_array($data) || $role === 'guest' || empty($data['name'])) {
            return null;
        }

        return new DemoUser($role, $data['name']);
    }

    public function userFromRequest(Request $request, bool $allowSession = true): ?DemoUser
    {
        return $this->userFor($this->roleFromRequest($request, $allowSession));
    }
}

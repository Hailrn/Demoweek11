<?php

namespace App\Http\Controllers;

use App\Demo\Auth\DemoRoleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoAuthController extends Controller
{
    public function update(Request $request, DemoRoleResolver $roles): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', $roles->validRoles())],
        ]);

        $request->session()->put('demo_role', $validated['role']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Peran demo diperbarui.',
                'data' => ['role' => $validated['role']],
            ]);
        }

        return back()->with('success', 'Peran demo diganti menjadi '.$validated['role'].'.');
    }
}

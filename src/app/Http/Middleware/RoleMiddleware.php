<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * $roles: daftar kode role yang diizinkan, dipisah koma.
     * superadmin selalu diizinkan.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Role tidak ditemukan');
        }

        $allowed = in_array('superadmin', $roles, true) || in_array($user->role->kode, $roles, true);

        if (! $allowed) {
            abort(403, 'Anda tidak memiliki akses');
        }

        return $next($request);
    }
}
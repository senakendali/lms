<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) !== $role) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

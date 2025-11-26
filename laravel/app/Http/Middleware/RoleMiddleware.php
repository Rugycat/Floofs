<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role = null)
{
    // jei route nenurodytas role parametras — middleware NEVEIKIA
    if ($role === null) {
        return $next($request);
    }

    $user = auth()->user();

    if (!$user || $user->role !== $role) {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    return $next($request);
}

}

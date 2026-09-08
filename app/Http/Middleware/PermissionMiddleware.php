<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (method_exists($user, 'hasRole') && $user->hasAnyRole(['Super Admin', 'Owner', 'super-admin'])) {
            return $next($request);
        }

        if (! method_exists($user, 'can') || ! $user->can($permission)) {
            abort(403, 'You do not have permission: ' . $permission);
        }

        return $next($request);
    }
}

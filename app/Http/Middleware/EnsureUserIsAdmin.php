<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Placeholder middleware for future admin authorization.
 * Wire this into routes once authentication and roles are implemented.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Future: verify authenticated user has an admin role.
        // For now this only prepares the architecture.

        return $next($request);
    }
}

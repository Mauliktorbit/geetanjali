<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireStaleAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        if ($user->last_login_at === null) {
            $user->forceFill(['last_login_at' => now()])->save();

            return $next($request);
        }

        if (! $user->authenticationHasExpired()) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Your session has expired. Please sign in again.',
            ], 401);
        }

        return redirect()->guest(route('login'))
            ->with('success', 'Your session expired after 1 week. Please sign in again.');
    }
}

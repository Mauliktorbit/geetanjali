<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user || $user->is_staff) {
            return $next($request);
        }

        $blocked = ! $user->is_active || (bool) $user->customer?->is_blocked;
        if (! $blocked) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['email' => 'Your account is inactive. Please contact support.']);
    }
}

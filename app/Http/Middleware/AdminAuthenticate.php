<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_staff) {
            return redirect()->route('account.index')
                ->with('error', 'You do not have admin access.');
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Your account is inactive.');
        }

        if ($user->isLocked()) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Your account is temporarily locked.');
        }

        return $next($request);
    }
}

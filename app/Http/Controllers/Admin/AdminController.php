<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

abstract class AdminController extends Controller
{
    protected function success(string $message, ?string $route = null, array $params = []): RedirectResponse
    {
        $redirect = $route ? redirect()->route($route, $params) : redirect()->back();
        return $redirect->with('success', $message);
    }

    protected function error(string $message, ?string $route = null, array $params = []): RedirectResponse
    {
        $redirect = $route ? redirect()->route($route, $params) : redirect()->back();
        return $redirect->withInput()->with('error', $message);
    }
}

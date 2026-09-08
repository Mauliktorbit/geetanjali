<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Backward-compatible redirects from old /admin/* route prefix
Route::redirect('admin/login', '/login', 301);
Route::get('admin/{path?}', function (?string $path = null) {
    return redirect('/' . ltrim((string) $path, '/'), 301);
})->where('path', '.*');

require __DIR__.'/admin.php';

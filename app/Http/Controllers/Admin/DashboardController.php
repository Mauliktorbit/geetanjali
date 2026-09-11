<?php

namespace App\Http\Controllers\Admin;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends AdminController
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $overview = $this->dashboardService->overview();

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $adminName = trim((string) strtok((string) (auth()->user()?->name ?? 'Admin'), ' ')) ?: 'Admin';

        return view('admin.dashboard.index', compact(
            'overview',
            'greeting',
            'adminName'
        ));
    }
}

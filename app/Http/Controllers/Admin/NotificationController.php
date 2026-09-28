<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends AdminController
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $items = $this->notificationService->queryFor(Auth::id())
            ->when($request->filled('unread'), fn ($q) => $q->where('is_read', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.index', compact('items'));
    }

    public function feed(): JsonResponse
    {
        return response()->json($this->notificationService->feed(Auth::id()));
    }

    public function markRead(AdminNotification $notification)
    {
        $this->authorizeNotification($notification);

        $this->notificationService->markRead($notification);
        $url = $notification->url();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'url' => $url]);
        }

        return redirect($url);
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead(Auth::id());

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return $this->success('All notifications marked as read.');
    }

    public function destroy(AdminNotification $notification)
    {
        $this->authorizeNotification($notification);
        $this->notificationService->clear($notification);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return $this->success('Notification cleared.');
    }

    public function destroyAll()
    {
        $this->notificationService->clearAll(Auth::id());

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return $this->success('All notifications cleared.');
    }

    protected function authorizeNotification(AdminNotification $notification): void
    {
        if (! $this->notificationService->belongsToUser($notification, Auth::id())) {
            abort(403);
        }
    }
}

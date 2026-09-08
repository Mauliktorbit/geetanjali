<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends AdminController
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $items = AdminNotification::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->when($request->filled('unread'), fn ($q) => $q->where('is_read', false))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.notifications.index', compact('items'));
    }

    public function markRead(AdminNotification $notification)
    {
        if ($notification->user_id && $notification->user_id !== Auth::id()) {
            abort(403);
        }

        $this->notificationService->markRead($notification);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return $this->success('Notification marked as read.');
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead(Auth::id());

        return $this->success('All notifications marked as read.');
    }
}

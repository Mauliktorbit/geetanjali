<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;

class NotificationService
{
    public function notifyAdmins(
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        array $data = []
    ): void {
        $admins = User::query()
            ->where('is_staff', true)
            ->where('is_active', true)
            ->get(['id']);

        if ($admins->isEmpty()) {
            AdminNotification::create([
                'user_id' => null,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
                'is_read' => false,
            ]);

            return;
        }

        foreach ($admins as $admin) {
            AdminNotification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
                'is_read' => false,
            ]);
        }
    }

    public function notifyOrderEvent(string $event, int $orderId, string $title, ?string $message = null): void
    {
        $this->notifyAdmins($event, $title, $message, '/admin/orders/' . $orderId, [
            'order_id' => $orderId,
        ]);
    }

    public function notifyStockEvent(string $event, int $productId, string $title, ?string $message = null): void
    {
        $this->notifyAdmins($event, $title, $message, '/admin/products/' . $productId, [
            'product_id' => $productId,
        ]);
    }

    public function notifyReturnEvent(string $event, int $returnId, string $title, ?string $message = null): void
    {
        $this->notifyAdmins($event, $title, $message, '/admin/returns/' . $returnId, [
            'return_id' => $returnId,
        ]);
    }

    public function markRead(AdminNotification $notification): AdminNotification
    {
        $notification->markAsRead();

        return $notification->fresh();
    }

    public function markAllRead(?int $userId = null): int
    {
        return AdminNotification::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}

<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class NotificationService
{
    /**
     * @return list<string>
     */
    public static function alertTypes(): array
    {
        return ['order_created', 'return_requested', 'enquiry_created', 'newsletter_subscribed'];
    }

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

    public function notifyNewOrder(Order $order): void
    {
        if (Auth::user()?->is_staff) {
            return;
        }

        $order->loadMissing('items');
        $who = $order->customer_name ?: 'A customer';
        $product = $order->productSummary();
        $detail = $product !== '—'
            ? $who.' ordered '.$product
            : $who.' placed a new order';

        $this->notifyAdmins(
            'order_created',
            'New order received',
            $detail.' · '.$order->order_number.' · '.money($order->grand_total),
            $this->orderLink($order->id),
            ['order_id' => $order->id]
        );
    }

    public function notifyNewReturn(ReturnRequest $return): void
    {
        $return->loadMissing(['items.orderItem', 'order', 'customer']);
        $who = $return->customerDisplayName();
        if ($who === '—') {
            $who = 'A customer';
        }
        $product = $return->productSummary();
        $orderNo = $return->order?->order_number;
        $detail = $product !== '—'
            ? $who.' asked to return '.$product
            : $who.' asked to return an order';

        if ($orderNo) {
            $detail .= ' · Order '.$orderNo;
        }

        $this->notifyAdmins(
            'return_requested',
            'New return request',
            $detail,
            $this->returnLink($return->id),
            ['return_id' => $return->id]
        );
    }

    public function notifyNewEnquiry(\App\Models\Enquiry $enquiry): void
    {
        $this->notifyAdmins(
            'enquiry_created',
            'New enquiry',
            trim($enquiry->name.' sent a message'.($enquiry->phone ? ' · '.$enquiry->phone : '')),
            Route::has('admin.enquiries.show')
                ? route('admin.enquiries.show', $enquiry)
                : url('admin/enquiries/'.$enquiry->id),
            ['enquiry_id' => $enquiry->id]
        );
    }

    public function notifyNewSubscriber(\App\Models\NewsletterSubscriber $subscriber): void
    {
        $this->notifyAdmins(
            'newsletter_subscribed',
            'New subscriber',
            $subscriber->email.' joined the newsletter.',
            Route::has('admin.enquiries.index')
                ? route('admin.enquiries.index', ['tab' => 'subscriptions'])
                : url('admin/enquiries?tab=subscriptions'),
            ['subscriber_id' => $subscriber->id]
        );
    }

    public function notifyOrderEvent(string $event, int $orderId, string $title, ?string $message = null): void
    {
        $this->notifyAdmins($event, $title, $message, $this->orderLink($orderId), [
            'order_id' => $orderId,
        ]);
    }

    public function notifyStockEvent(string $event, int $productId, string $title, ?string $message = null): void
    {
        $link = Route::has('admin.products.show') ? route('admin.products.show', $productId) : url('admin/products/'.$productId);
        $this->notifyAdmins($event, $title, $message, $link, [
            'product_id' => $productId,
        ]);
    }

    public function notifyReturnEvent(string $event, int $returnId, string $title, ?string $message = null): void
    {
        $this->notifyAdmins($event, $title, $message, $this->returnLink($returnId), [
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
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function queryFor(?int $userId)
    {
        return AdminNotification::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            });
    }

    public function latest(?int $userId, int $limit = 8): Collection
    {
        return $this->queryFor($userId)->latest()->limit($limit)->get();
    }

    public function unreadCount(?int $userId): int
    {
        return (int) $this->queryFor($userId)->where('is_read', false)->count();
    }

    public function unreadAlerts(?int $userId, int $limit = 5): Collection
    {
        return $this->queryFor($userId)
            ->where('is_read', false)
            ->whereIn('type', self::alertTypes())
            ->where('created_at', '>=', now()->subMinutes(30))
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function feed(?int $userId): array
    {
        $items = $this->latest($userId, 8);

        return [
            'count' => $this->unreadCount($userId),
            'items' => $items->map(fn (AdminNotification $row) => $row->toFeed())->values()->all(),
            'alerts' => $this->unreadAlerts($userId)->map(fn (AdminNotification $row) => $row->toFeed())->values()->all(),
        ];
    }

    protected function orderLink(int $orderId): string
    {
        return Route::has('admin.orders.show')
            ? route('admin.orders.show', $orderId)
            : url('admin/orders/'.$orderId);
    }

    protected function returnLink(int $returnId): string
    {
        return Route::has('admin.returns.show')
            ? route('admin.returns.show', $returnId)
            : url('admin/returns/'.$returnId);
    }
}

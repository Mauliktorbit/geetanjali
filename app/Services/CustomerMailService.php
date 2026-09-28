<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderShippedMail;
use App\Mail\OrderStatusMail;
use App\Mail\WelcomeCustomerMail;
use App\Models\CommunicationLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CustomerMailService
{
    public function sendWelcome(User $user): bool
    {
        return $this->deliver(
            $user->email,
            new WelcomeCustomerMail($user->name ?: 'there', route('home')),
            [
                'customer_id' => $user->customer?->id,
                'type' => 'welcome',
                'subject' => 'Welcome to '.config('brand.name'),
                'message' => 'Registration welcome email',
            ]
        );
    }

    public function sendOrderConfirmed(Order $order, bool $force = false): bool
    {
        $order->loadMissing('items');

        if (! $force && $this->alreadySent($order, 'order_confirmation')) {
            return true;
        }

        $name = $order->customer_name ?: 'there';

        return $this->deliver(
            $this->orderEmail($order),
            new OrderConfirmedMail(
                $order,
                $name,
                $this->trackUrl($order),
                route('account.orders.show', $order->order_number),
            ),
            [
                'customer_id' => $order->customer_id,
                'type' => 'order_confirmation',
                'subject' => 'Order Confirmation '.$order->order_number,
                'message' => 'Order confirmation for '.$order->order_number,
                'meta' => ['order_id' => $order->id],
            ]
        );
    }

    public function sendOrderShipped(Order $order, bool $force = false): bool
    {
        $order->loadMissing('items');

        if (! $force && $this->alreadySent($order, 'order_shipped')) {
            return true;
        }

        $statusLabel = strtolower(OrderStatus::customerLabel((string) $order->status));

        return $this->deliver(
            $this->orderEmail($order),
            new OrderShippedMail(
                $order,
                $order->customer_name ?: 'there',
                $statusLabel,
                $this->trackUrl($order),
                $order->tracking_number,
                $order->shipping_partner,
            ),
            [
                'customer_id' => $order->customer_id,
                'type' => 'order_shipped',
                'subject' => 'Order '.$order->order_number.' is on the way',
                'message' => 'Tracking email for '.$order->order_number,
                'meta' => ['order_id' => $order->id, 'tracking_number' => $order->tracking_number],
            ]
        );
    }

    public function sendOrderDelivered(Order $order): bool
    {
        if ($this->alreadySent($order, 'order_delivered')) {
            return true;
        }

        return $this->deliver(
            $this->orderEmail($order),
            new OrderStatusMail(
                $order,
                $order->customer_name ?: 'there',
                'Your order has been delivered',
                'Order '.$order->order_number.' was delivered. We hope you love your jewellery.',
                route('account.reviews'),
                'Write a review',
                'delivered',
            ),
            [
                'customer_id' => $order->customer_id,
                'type' => 'order_delivered',
                'subject' => 'Order '.$order->order_number.' delivered',
                'message' => 'Delivery email for '.$order->order_number,
                'meta' => ['order_id' => $order->id],
            ]
        );
    }

    public function sendOrderCancelled(Order $order): bool
    {
        if ($this->alreadySent($order, 'order_cancelled')) {
            return true;
        }

        $reason = trim((string) $order->cancel_reason);
        $intro = 'order '.$order->order_number.' has been cancelled.';
        if ($reason !== '') {
            $intro .= ' Reason: '.$reason;
        }

        return $this->deliver(
            $this->orderEmail($order),
            new OrderStatusMail(
                $order,
                $order->customer_name ?: 'there',
                'Your order was cancelled',
                $intro,
                route('home'),
                'Continue shopping',
                'cancelled',
            ),
            [
                'customer_id' => $order->customer_id,
                'type' => 'order_cancelled',
                'subject' => 'Order '.$order->order_number.' cancelled',
                'message' => 'Cancellation email for '.$order->order_number,
                'meta' => ['order_id' => $order->id],
            ]
        );
    }

    public function notifyOrderStatus(Order $order): void
    {
        match ((string) $order->status) {
            OrderStatus::CONFIRMED => $this->sendOrderConfirmed($order),
            OrderStatus::SHIPPED, OrderStatus::OUT_FOR_DELIVERY => $this->sendOrderShipped($order),
            OrderStatus::DELIVERED => $this->sendOrderDelivered($order),
            OrderStatus::CANCELLED => $this->sendOrderCancelled($order),
            default => null,
        };
    }

    private function orderEmail(Order $order): ?string
    {
        $order->loadMissing('customer.user');

        return $order->customer_email
            ?: $order->customer?->email
            ?: $order->customer?->user?->email;
    }

    private function trackUrl(Order $order): string
    {
        return route('pages.track-order', ['order_id' => $order->order_number]);
    }

    private function alreadySent(Order $order, string $type): bool
    {
        return CommunicationLog::query()
            ->where('type', $type)
            ->where('status', 'sent')
            ->get()
            ->contains(fn (CommunicationLog $log) => (int) data_get($log->meta, 'order_id') === (int) $order->id);
    }

    /**
     * @param  array{customer_id?: int|null, type: string, subject: string, message: string, meta?: array<string, mixed>}  $log
     */
    private function deliver(?string $email, object $mailable, array $log): bool
    {
        $email = strtolower(trim((string) $email));
        $payload = [
            'customer_id' => $log['customer_id'] ?? null,
            'channel' => 'email',
            'type' => $log['type'],
            'subject' => $log['subject'],
            'message' => $log['message'],
            'sent_by' => Auth::id(),
            'meta' => $log['meta'] ?? null,
        ];

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CommunicationLog::query()->create($payload + ['status' => 'failed', 'message' => 'No customer email address.']);

            return false;
        }

        try {
            Mail::to($email)->send($mailable);
            CommunicationLog::query()->create($payload + ['status' => 'sent']);

            return true;
        } catch (Throwable $e) {
            Log::warning('Customer email failed', [
                'type' => $log['type'],
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            CommunicationLog::query()->create($payload + [
                'status' => 'failed',
                'message' => $log['message'].' · '.$e->getMessage(),
            ]);

            return false;
        }
    }
}

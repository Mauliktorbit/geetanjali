<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected NotificationService $notificationService
    ) {}

    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be positive.');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? $order->payment_method ?? 'manual',
                'gateway' => $data['gateway'] ?? null,
                'amount' => $amount,
                'status' => PaymentStatus::PAID,
                'gateway_charges' => $data['gateway_charges'] ?? 0,
                'net_settlement' => $data['net_settlement'] ?? ($amount - (float) ($data['gateway_charges'] ?? 0)),
                'gateway_response' => $data['gateway_response'] ?? null,
                'payment_link' => $data['payment_link'] ?? null,
                'is_partial' => $amount < (float) $order->grand_total,
                'is_advance' => (bool) ($data['is_advance'] ?? false),
                'settlement_status' => $data['settlement_status'] ?? 'pending',
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            $this->syncOrderPaymentStatus($order);

            return $payment;
        });
    }

    public function markFailed(Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'status' => PaymentStatus::FAILED,
                'failure_reason' => $reason,
            ]);

            if ($payment->order) {
                $this->syncOrderPaymentStatus($payment->order);
            }

            return $payment->fresh();
        });
    }

    public function processRefund(
        Order $order,
        float $amount,
        ?Payment $payment = null,
        string $method = 'original',
        ?string $reason = null,
        ?int $returnId = null
    ): Refund {
        return DB::transaction(function () use ($order, $amount, $payment, $method, $reason, $returnId) {
            if ($amount <= 0) {
                throw new InvalidArgumentException('Refund amount must be positive.');
            }

            $refundable = (float) $order->paid_amount - (float) $order->refunded_amount;
            if ($amount > $refundable + 0.01) {
                throw new InvalidArgumentException('Refund exceeds refundable amount.');
            }

            $payment ??= $order->payments()
                ->where('status', PaymentStatus::PAID)
                ->orderByDesc('paid_at')
                ->first();

            $isPartial = $amount < (float) $order->paid_amount;

            $refund = Refund::create([
                'refund_number' => $this->generateRefundNumber(),
                'order_id' => $order->id,
                'return_id' => $returnId,
                'payment_id' => $payment?->id,
                'customer_id' => $order->customer_id,
                'amount' => $amount,
                'method' => $method,
                'status' => 'processed',
                'is_partial' => $isPartial,
                'reason' => $reason,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            if ($payment) {
                $payment->update([
                    'refund_amount' => (float) $payment->refund_amount + $amount,
                    'refund_status' => $isPartial ? PaymentStatus::PARTIALLY_REFUNDED : PaymentStatus::REFUNDED,
                ]);
            }

            $order->update([
                'refunded_amount' => (float) $order->refunded_amount + $amount,
            ]);

            $this->syncOrderPaymentStatus($order->fresh());

            $this->notificationService->notifyAdmins(
                'refund_processed',
                'Refund ' . $refund->refund_number,
                money($amount) . ' refunded for order ' . $order->order_number,
                '/admin/orders/' . $order->id,
                ['refund_id' => $refund->id, 'order_id' => $order->id]
            );

            return $refund;
        });
    }

    public function syncOrderPaymentStatus(Order $order): void
    {
        $order->refresh();

        $paid = (float) $order->payments()
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');

        $refunded = (float) $order->refunded_amount;
        $netPaid = max(0, $paid - $refunded);
        $grand = (float) $order->grand_total;

        $status = match (true) {
            $refunded > 0 && $netPaid <= 0 => PaymentStatus::REFUNDED,
            $refunded > 0 && $netPaid < $grand => PaymentStatus::PARTIALLY_REFUNDED,
            $netPaid <= 0 => PaymentStatus::PENDING,
            $netPaid + 0.01 >= $grand => PaymentStatus::PAID,
            default => PaymentStatus::PARTIALLY_PAID,
        };

        $order->update([
            'paid_amount' => round($paid, 2),
            'payment_status' => $status,
        ]);
    }

    protected function generateRefundNumber(): string
    {
        do {
            $number = 'REF-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Refund::where('refund_number', $number)->exists());

        return $number;
    }
}

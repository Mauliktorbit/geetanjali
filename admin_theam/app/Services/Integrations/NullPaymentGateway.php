<?php

namespace App\Services\Integrations;

use App\Services\Integrations\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class NullPaymentGateway implements PaymentGatewayInterface
{
    public function charge(array $payload): array
    {
        return [
            'success' => true,
            'transaction_id' => 'NULL-TXN-' . strtoupper(Str::random(10)),
            'status' => 'paid',
            'amount' => $payload['amount'] ?? 0,
            'gateway' => 'null',
            'raw' => $payload,
        ];
    }

    public function refund(string $transactionId, float $amount, array $meta = []): array
    {
        return [
            'success' => true,
            'refund_id' => 'NULL-REF-' . strtoupper(Str::random(8)),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
            'gateway' => 'null',
            'meta' => $meta,
        ];
    }

    public function verify(string $transactionId): array
    {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'paid',
            'gateway' => 'null',
        ];
    }

    public function createPaymentLink(array $payload): array
    {
        return [
            'success' => true,
            'payment_link' => url('/payments/null/' . Str::uuid()),
            'gateway' => 'null',
            'payload' => $payload,
        ];
    }
}

<?php

namespace App\Services\Integrations\Contracts;

interface PaymentGatewayInterface
{
    public function charge(array $payload): array;

    public function refund(string $transactionId, float $amount, array $meta = []): array;

    public function verify(string $transactionId): array;

    public function createPaymentLink(array $payload): array;
}

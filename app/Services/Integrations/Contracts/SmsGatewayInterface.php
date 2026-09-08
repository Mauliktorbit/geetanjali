<?php

namespace App\Services\Integrations\Contracts;

interface SmsGatewayInterface
{
    public function send(string $to, string $message, array $options = []): array;
}

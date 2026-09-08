<?php

namespace App\Services\Integrations\Contracts;

interface WhatsAppGatewayInterface
{
    public function sendText(string $to, string $message, array $options = []): array;

    public function sendTemplate(string $to, string $template, array $params = [], array $options = []): array;
}

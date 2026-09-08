<?php

namespace App\Services\Integrations\Contracts;

interface EmailProviderInterface
{
    public function send(string $to, string $subject, string $html, array $options = []): array;
}

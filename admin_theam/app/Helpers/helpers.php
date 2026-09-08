<?php

use App\Enums\OrderStatus;
use App\Services\SettingService;

if (! function_exists('money')) {
    function money(float|int|string|null $amount, string $currency = 'INR', int $decimals = 2): string
    {
        $value = (float) ($amount ?? 0);
        $symbol = match (strtoupper($currency)) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => strtoupper($currency) . ' ',
        };

        return $symbol . number_format($value, $decimals, '.', ',');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return app(SettingService::class)->get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('order_statuses')) {
    function order_statuses(): array
    {
        return OrderStatus::labels();
    }
}

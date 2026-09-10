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

if (! function_exists('storefront_image')) {
    function storefront_image(?string $path, string $fallback = 'public/assets/images/categories/kundan.jpg'): string
    {
        if ($path === null || trim($path) === '') {
            return asset($fallback);
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'public/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        $relative = ltrim($path, '/');
        foreach (['public/storage/', 'storage/'] as $prefix) {
            if (str_starts_with($relative, $prefix)) {
                $relative = substr($relative, strlen($prefix));
                break;
            }
        }

        return asset('storage/'.$relative);
    }
}

if (! function_exists('order_statuses')) {
    function order_statuses(): array
    {
        return OrderStatus::labels();
    }
}

if (! function_exists('admin_breadcrumbs')) {
    /**
     * @return list<array{label: string, url?: string|null}>
     */
    function admin_breadcrumbs(): array
    {
        return \App\Support\AdminBreadcrumb::items();
    }
}

if (! function_exists('digits_only')) {
    function digits_only(mixed $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }
}

if (! function_exists('indian_mobile')) {
    function indian_mobile(mixed $value): ?string
    {
        $digits = digits_only($value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }
}

if (! function_exists('indian_mobile_rules')) {
    /**
     * @return list<string>
     */
    function indian_mobile_rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'regex:/^[6-9][0-9]{9}$/',
        ];
    }
}

if (! function_exists('indian_pincode_rules')) {
    /**
     * @return list<string>
     */
    function indian_pincode_rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'regex:/^[0-9]{6}$/',
        ];
    }
}

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

if (! function_exists('price_discount_label')) {
    /**
     * Discount badge from the same rupee amounts shown on screen.
     * "N% OFF" is used only when N% of MRP rounds to the sale price.
     */
    function price_discount_label(float|int|string|null $price, float|int|string|null $compare): ?string
    {
        $price = (int) round((float) $price);
        $compare = (int) round((float) $compare);

        if ($price <= 0 || $compare <= $price) {
            return null;
        }

        $saved = $compare - $price;
        $raw = ($saved / $compare) * 100;

        $matches = static function (float $percent) use ($compare, $price): bool {
            if ($percent < 0.01 || $percent > 99.99) {
                return false;
            }

            return (int) round($compare * (1 - $percent / 100)) === $price;
        };

        $whole = (int) round($raw);
        if ($matches($whole)) {
            return $whole.'% OFF';
        }

        foreach ([1, 2] as $decimals) {
            $candidate = round($raw, $decimals);
            if ($matches($candidate)) {
                $label = rtrim(rtrim(number_format($candidate, $decimals, '.', ''), '0'), '.');

                return $label.'% OFF';
            }
        }

        return 'Save ₹'.number_format($saved);
    }
}

if (! function_exists('review_count_label')) {
    function review_count_label(mixed $count, bool $parentheses = false): string
    {
        $n = max(0, (int) $count);
        $label = $n.' '.($n === 1 ? 'Review' : 'Reviews');

        return $parentheses ? '('.$label.')' : $label;
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

        $relative = ltrim($relative, '/');

        try {
            $request = request();
            $base = rtrim(str_replace('\\', '/', (string) $request->getBasePath()), '/');

            return $request->getSchemeAndHttpHost().$base.'/storage/'.$relative;
        } catch (\Throwable $e) {
            return asset('storage/'.$relative);
        }
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

if (! function_exists('parse_dmy')) {
    function parse_dmy(mixed $value, bool $endOfDay = false): ?\Carbon\Carbon
    {
        $value = trim((string) $value);
        if ($value === '' || ! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $parts)) {
            return null;
        }

        $day = (int) $parts[1];
        $month = (int) $parts[2];
        $year = (int) $parts[3];

        if ($year < 2000 || $year > 2100 || ! checkdate($month, $day, $year)) {
            return null;
        }

        $date = \Carbon\Carbon::create($year, $month, $day);

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }
}

if (! function_exists('dmy_date_rules')) {
    /**
     * @return list<mixed>
     */
    function dmy_date_rules(bool $required = false, ?string $afterOrEqual = null): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            'regex:/^\d{2}\/\d{2}\/\d{4}$/',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim((string) $value) === '') {
                    return;
                }

                if (parse_dmy($value) === null) {
                    $fail('Enter a valid date as DD/MM/YYYY, for example 26/12/2026.');
                }
            },
        ];

        if ($afterOrEqual) {
            $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($afterOrEqual): void {
                $end = parse_dmy($value);
                $start = parse_dmy(request()->input($afterOrEqual));

                if ($end && $start && $end->lt($start)) {
                    $fail('End date must be on or after the start date.');
                }
            };
        }

        return $rules;
    }
}

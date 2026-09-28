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

        $path = str_replace('\\', '/', trim($path));

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

        return asset('storage/'.ltrim($relative, '/'));
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
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $iso)) {
            $year = (int) $iso[1];
            $month = (int) $iso[2];
            $day = (int) $iso[3];
        } elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $parts)) {
            $day = (int) $parts[1];
            $month = (int) $parts[2];
            $year = (int) $parts[3];
        } else {
            return null;
        }

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
            'regex:/^(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})$/',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim((string) $value) === '') {
                    return;
                }

                if (parse_dmy($value) === null) {
                    $fail('Choose a valid date.');
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

if (! function_exists('admin_sort_state')) {
    function admin_sort_state(string $column): ?string
    {
        $sort = trim((string) request('sort', ''));
        $direction = strtolower(trim((string) request('direction', '')));

        if ($sort !== '' && preg_match('/^([a-z0-9_]+)_(asc|desc)$/i', $sort, $matches)) {
            $sort = $matches[1];
            $direction = strtolower($matches[2]);
        }

        if ($sort !== $column || ! in_array($direction, ['asc', 'desc'], true)) {
            return null;
        }

        return $direction;
    }
}

if (! function_exists('admin_sort_url')) {
    function admin_sort_url(string $column, string $defaultDirection = 'asc'): string
    {
        $defaultDirection = strtolower($defaultDirection) === 'desc' ? 'desc' : 'asc';
        $current = admin_sort_state($column);
        $next = $current === 'asc' ? 'desc' : ($current === 'desc' ? 'asc' : $defaultDirection);

        $query = request()->except(['page']);
        $query['sort'] = $column;
        $query['direction'] = $next;

        return request()->url().'?'.http_build_query($query);
    }
}

if (! function_exists('storefront_contact')) {
    /**
     * Public phone, email and address for the website.
     *
     * @return array{address: string, phone: string, phone_href: string, email: string, hours: string, hours_sunday: string, map_embed: string, map_directions: string}
     */
    function storefront_contact(): array
    {
        $base = (array) config('brand.contact', []);
        $email = storefront_contact_email(
            setting('store_email'),
            setting('general.contact_email'),
            $base['email'] ?? null
        );
        $phone = storefront_contact_phone(
            setting('store_phone'),
            setting('general.contact_phone'),
            $base['phone'] ?? null
        );

        return [
            'address' => trim((string) (setting('store_address') ?: setting('general.business_address') ?: ($base['address'] ?? ''))),
            'phone' => $phone,
            'phone_href' => preg_replace('/\s+/', '', $phone) ?: '',
            'email' => $email,
            'hours' => (string) ($base['hours'] ?? 'Mon - Sat: 10:00 AM - 7:00 PM'),
            'hours_sunday' => (string) ($base['hours_sunday'] ?? 'Sunday: Closed'),
            'map_embed' => (string) ($base['map_embed'] ?? ''),
            'map_directions' => (string) ($base['map_directions'] ?? ''),
        ];
    }
}

if (! function_exists('storefront_contact_email')) {
    function storefront_contact_email(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            $email = strtolower(trim((string) $candidate));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (preg_match('/torbitmultisoft\.com$|@example\.com$|@ecommerce\.test$|@geetanjali\.test$/i', $email)) {
                continue;
            }

            return $email;
        }

        return 'info@geetanjalijewellers.com';
    }
}

if (! function_exists('storefront_contact_phone')) {
    function storefront_contact_phone(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            $raw = trim((string) $candidate);
            $digits = preg_replace('/\D+/', '', $raw) ?? '';
            if ($digits === '' || $digits === '0000000000' || str_contains($raw, '1800-000')) {
                continue;
            }
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $digits = substr($digits, 2);
            }
            if (strlen($digits) === 10) {
                return '+91 '.substr($digits, 0, 5).' '.substr($digits, 5);
            }
            if ($raw !== '') {
                return $raw;
            }
        }

        return '+91 95839 59503';
    }
}

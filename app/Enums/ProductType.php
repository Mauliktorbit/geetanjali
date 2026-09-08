<?php

namespace App\Enums;

class ProductType
{
    public const SIMPLE = 'simple';
    public const VARIABLE = 'variable';
    public const DIGITAL = 'digital';
    public const BUNDLE = 'bundle';
    public const GROUPED = 'grouped';
    public const SUBSCRIPTION = 'subscription';

    public static function all(): array
    {
        return array_keys(self::labels());
    }

    public static function labels(): array
    {
        return [
            self::SIMPLE => 'Simple',
            self::VARIABLE => 'Variable',
            self::DIGITAL => 'Digital',
            self::BUNDLE => 'Bundle',
            self::GROUPED => 'Grouped',
            self::SUBSCRIPTION => 'Subscription',
        ];
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}

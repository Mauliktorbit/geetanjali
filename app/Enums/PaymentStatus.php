<?php

namespace App\Enums;

class PaymentStatus
{
    public const PENDING = 'pending';
    public const AUTHORIZED = 'authorized';
    public const PAID = 'paid';
    public const PARTIALLY_PAID = 'partially_paid';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';
    public const PARTIALLY_REFUNDED = 'partially_refunded';
    public const CANCELLED = 'cancelled';

    public static function all(): array
    {
        return array_keys(self::labels());
    }

    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::AUTHORIZED => 'Authorized',
            self::PAID => 'Paid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::CANCELLED => 'Cancelled',
        ];
    }

    public static function simpleLabel(string $status): string
    {
        return $status === self::PAID ? 'Paid' : 'Pending';
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

<?php

namespace App\Enums;

class OrderStatus
{
    public const NEW = 'new';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const PACKED = 'packed';
    public const READY_TO_SHIP = 'ready_to_ship';
    public const SHIPPED = 'shipped';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const RETURN_REQUESTED = 'return_requested';
    public const RETURNED = 'returned';
    public const REFUNDED = 'refunded';
    public const PARTIALLY_REFUNDED = 'partially_refunded';
    public const ON_HOLD = 'on_hold';
    public const FAILED_DELIVERY = 'failed_delivery';
    public const RTO = 'rto';

    public static function all(): array
    {
        return array_keys(self::labels());
    }

    public static function labels(): array
    {
        return [
            self::NEW => 'New',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::PACKED => 'Packed',
            self::READY_TO_SHIP => 'Ready to Ship',
            self::SHIPPED => 'Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::RETURN_REQUESTED => 'Return Requested',
            self::RETURNED => 'Returned',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::ON_HOLD => 'On Hold',
            self::FAILED_DELIVERY => 'Failed Delivery',
            self::RTO => 'RTO',
        ];
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function cancellable(): array
    {
        return [
            self::NEW,
            self::CONFIRMED,
            self::PROCESSING,
            self::PACKED,
            self::READY_TO_SHIP,
            self::ON_HOLD,
        ];
    }

    public static function inventoryDeductible(): array
    {
        return [
            self::CONFIRMED,
            self::PROCESSING,
            self::PACKED,
            self::READY_TO_SHIP,
            self::SHIPPED,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
        ];
    }
}

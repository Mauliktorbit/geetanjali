<?php

namespace App\Enums;

class ReturnStatus
{
    public const REQUESTED = 'requested';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const REFUNDED = 'refunded';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::REQUESTED => 'Requested',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::REFUNDED => 'Refunded',
        ];
    }

    /**
     * @return list<string>
     */
    public static function open(): array
    {
        return [self::REQUESTED, self::APPROVED, 'inspected', 'picked_up'];
    }

    public static function normalize(string $status): string
    {
        return match ($status) {
            'inspected', 'picked_up' => self::APPROVED,
            'replacement_created', 'completed' => self::REFUNDED,
            default => array_key_exists($status, self::labels()) ? $status : self::REQUESTED,
        };
    }

    /**
     * @return list<string>
     */
    public static function filterKeys(string $status): array
    {
        return match (self::normalize($status)) {
            self::APPROVED => [self::APPROVED, 'inspected', 'picked_up'],
            self::REFUNDED => [self::REFUNDED, 'replacement_created', 'completed'],
            default => [self::normalize($status)],
        };
    }

    public static function label(string $status): string
    {
        return self::labels()[self::normalize($status)] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function customerLabel(string $status): string
    {
        return match (self::normalize($status)) {
            self::REQUESTED => 'Under review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Not accepted',
            self::REFUNDED => 'Refunded',
            default => self::label($status),
        };
    }

    public static function badge(string $status): string
    {
        return match (self::normalize($status)) {
            self::REQUESTED => 'warning',
            self::APPROVED => 'info',
            self::REJECTED => 'cancelled',
            self::REFUNDED => 'refunded',
            default => 'default',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function nextOptions(string $status): array
    {
        $current = self::normalize($status);
        $labels = self::labels();

        if ($current === self::REQUESTED) {
            return [
                self::REQUESTED => 'Requested — waiting',
                self::APPROVED => 'Approve return',
                self::REJECTED => 'Reject return',
                self::REFUNDED => 'Mark money as refunded',
            ];
        }

        if ($current === self::APPROVED) {
            return [
                self::APPROVED => 'Approved — refund pending',
                self::REJECTED => 'Reject return',
                self::REFUNDED => 'Mark money as refunded',
            ];
        }

        return [$current => $labels[$current] ?? self::label($current)];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'order_id',
        'customer_name',
        'rating',
        'title',
        'comment',
        'media',
        'is_verified_purchase',
        'is_featured',
        'status',
        'admin_reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'media' => 'array',
            'is_verified_purchase' => 'boolean',
            'is_featured' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function displayName(): string
    {
        return trim((string) ($this->customer_name ?: $this->customer?->name ?: 'Customer')) ?: 'Customer';
    }

    public function productName(): string
    {
        return $this->product?->name ?: 'Product removed';
    }

    public function commentPreview(int $length = 80): string
    {
        $text = trim((string) $this->comment);

        return $text === '' ? '—' : \Illuminate\Support\Str::limit($text, $length);
    }

    public function statusLabel(): string
    {
        if ($this->status === 'hidden') {
            return 'Hidden';
        }

        return self::statuses()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'cancelled',
            'hidden' => 'inactive',
            default => 'pending',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

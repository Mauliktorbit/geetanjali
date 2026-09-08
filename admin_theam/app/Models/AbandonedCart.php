<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'email',
        'phone',
        'cart_items',
        'cart_value',
        'cart_date',
        'last_activity_at',
        'recovery_status',
        'coupon_sent',
        'reminder_status',
        'recovered_at',
    ];

    protected function casts(): array
    {
        return [
            'cart_items' => 'array',
            'cart_value' => 'decimal:2',
            'cart_date' => 'datetime',
            'last_activity_at' => 'datetime',
            'coupon_sent' => 'boolean',
            'recovered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

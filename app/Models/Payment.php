<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'transaction_id',
        'payment_method',
        'gateway',
        'amount',
        'status',
        'failure_reason',
        'refund_amount',
        'refund_status',
        'settlement_status',
        'gateway_charges',
        'net_settlement',
        'gateway_response',
        'payment_link',
        'is_partial',
        'is_advance',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'gateway_charges' => 'decimal:2',
            'net_settlement' => 'decimal:2',
            'gateway_response' => 'array',
            'is_partial' => 'boolean',
            'is_advance' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}

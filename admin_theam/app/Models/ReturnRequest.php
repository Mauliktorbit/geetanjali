<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'order_id',
        'customer_id',
        'return_reason_id',
        'type',
        'status',
        'customer_reason',
        'evidence_files',
        'refund_amount',
        'return_shipping_deduction',
        'refund_method',
        'restock',
        'inspection_notes',
        'inspection_status',
        'replacement_order_id',
        'reviewed_by',
        'approved_at',
        'rejected_at',
        'picked_up_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence_files' => 'array',
            'refund_amount' => 'decimal:2',
            'return_shipping_deduction' => 'decimal:2',
            'restock' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'return_id');
    }

    public function replacementOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replacement_order_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

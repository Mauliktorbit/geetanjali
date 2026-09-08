<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'awb_number',
        'tracking_number',
        'status',
        'label_path',
        'pickup_requested_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'is_reverse',
        'ndr_status',
        'ndr_reason',
        'delivery_proof',
        'cod_amount',
        'cod_reconciliation_status',
        'tracking_data',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'pickup_requested_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_reverse' => 'boolean',
            'cod_amount' => 'decimal:2',
            'tracking_data' => 'array',
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }
}

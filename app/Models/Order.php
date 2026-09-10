<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'source',
        'sales_channel',
        'status',
        'payment_status',
        'payment_method',
        'shipping_method',
        'coupon_id',
        'coupon_code',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_charge',
        'cod_charge',
        'grand_total',
        'paid_amount',
        'refunded_amount',
        'cost_total',
        'currency',
        'billing_address',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_pincode',
        'shipping_partner',
        'tracking_number',
        'awb_number',
        'customer_notes',
        'internal_notes',
        'warehouse_id',
        'created_by',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'cod_charge' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function canRequestReturn(): bool
    {
        if ((string) $this->status !== \App\Enums\OrderStatus::DELIVERED) {
            return false;
        }

        $open = $this->relationLoaded('returns')
            ? $this->returns->contains(fn ($row) => in_array((string) $row->status, \App\Enums\ReturnStatus::open(), true))
            : $this->returns()->whereIn('status', \App\Enums\ReturnStatus::open())->exists();

        return ! $open;
    }

    public function canCancel(): bool
    {
        return in_array((string) $this->status, \App\Enums\OrderStatus::cancellable(), true);
    }

    public function productSummary(): string
    {
        $names = $this->relationLoaded('items')
            ? $this->items->pluck('product_name')
            : $this->items()->pluck('product_name');

        $names = $names->filter(fn ($name) => filled($name))->values();

        if ($names->isEmpty()) {
            return '—';
        }

        $first = (string) $names->first();
        $extra = $names->count() - 1;

        return $extra > 0 ? $first.' +'.$extra.' more' : $first;
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }
}

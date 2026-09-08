<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'supplier_id',
        'warehouse_bin_id',
        'current_stock',
        'available_stock',
        'reserved_stock',
        'damaged_stock',
        'returned_stock',
        'incoming_stock',
        'reorder_level',
        'batch_lot_number',
        'expiry_date',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'warehouse_bin_id');
    }

    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }
}

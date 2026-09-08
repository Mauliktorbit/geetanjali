<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingMethod extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'shipping_zone_id',
  1 => 'name',
  2 => 'code',
  3 => 'type',
  4 => 'rate',
  5 => 'min_order_amount',
  6 => 'free_shipping_threshold',
  7 => 'min_weight',
  8 => 'max_weight',
  9 => 'cod_available',
  10 => 'cod_charges',
  11 => 'estimated_delivery',
  12 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
            'cod_available' => 'boolean',
            'cod_charges' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

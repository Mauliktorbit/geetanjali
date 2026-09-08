<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = array (
  0 => 'code',
  1 => 'name',
  2 => 'discount_type',
  3 => 'discount_value',
  4 => 'starts_at',
  5 => 'ends_at',
  6 => 'usage_limit',
  7 => 'usage_count',
  8 => 'per_customer_limit',
  9 => 'minimum_cart',
  10 => 'maximum_discount',
  11 => 'included_products',
  12 => 'excluded_products',
  13 => 'included_categories',
  14 => 'customer_groups',
  15 => 'new_customers_only',
  16 => 'payment_methods',
  17 => 'locations',
  18 => 'is_stackable',
  19 => 'meta',
  20 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'minimum_cart' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'included_products' => 'array',
            'excluded_products' => 'array',
            'included_categories' => 'array',
            'customer_groups' => 'array',
            'payment_methods' => 'array',
            'locations' => 'array',
            'meta' => 'array',
            'new_customers_only' => 'boolean',
            'is_stackable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

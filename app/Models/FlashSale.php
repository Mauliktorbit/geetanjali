<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlashSale extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'title',
  1 => 'starts_at',
  2 => 'ends_at',
  3 => 'product_ids',
  4 => 'discount_percent',
  5 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'product_ids' => 'array',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

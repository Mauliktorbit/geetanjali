<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroup extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'slug',
  2 => 'discount_percent',
  3 => 'pricing_rules',
  4 => 'payment_terms_days',
  5 => 'moq',
  6 => 'shipping_rates',
  7 => 'credit_limit',
  8 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'pricing_rules' => 'array',
            'shipping_rates' => 'array',
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

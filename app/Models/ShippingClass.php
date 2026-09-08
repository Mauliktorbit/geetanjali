<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingClass extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'slug',
  2 => 'description',
  3 => 'cost',
  4 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

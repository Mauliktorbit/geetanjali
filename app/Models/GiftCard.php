<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'code',
  1 => 'initial_balance',
  2 => 'balance',
  3 => 'customer_id',
  4 => 'expires_at',
  5 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}

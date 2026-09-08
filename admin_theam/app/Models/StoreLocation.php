<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreLocation extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'address',
  2 => 'city',
  3 => 'state',
  4 => 'pincode',
  5 => 'phone',
  6 => 'email',
  7 => 'latitude',
  8 => 'longitude',
  9 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }
}

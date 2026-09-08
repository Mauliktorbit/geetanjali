<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingZone extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'countries',
  2 => 'states',
  3 => 'cities',
  4 => 'pincodes',
  5 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'states' => 'array',
            'cities' => 'array',
            'pincodes' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

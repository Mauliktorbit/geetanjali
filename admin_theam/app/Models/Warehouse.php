<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'code',
  2 => 'address',
  3 => 'city',
  4 => 'state',
  5 => 'country',
  6 => 'pincode',
  7 => 'phone',
  8 => 'priority',
  9 => 'is_default',
  10 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function bins()
    {
        return $this->hasMany(WarehouseBin::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}

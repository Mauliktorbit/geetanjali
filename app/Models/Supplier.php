<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = array (
  0 => 'name',
  1 => 'company_name',
  2 => 'email',
  3 => 'phone',
  4 => 'gstin',
  5 => 'address',
  6 => 'city',
  7 => 'state',
  8 => 'country',
  9 => 'pincode',
  10 => 'contact_person',
  11 => 'lead_time_days',
  12 => 'rating',
  13 => 'outstanding',
  14 => 'notes',
  15 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'outstanding' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

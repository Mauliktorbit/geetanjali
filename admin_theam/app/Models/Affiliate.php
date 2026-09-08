<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Affiliate extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'email',
  2 => 'phone',
  3 => 'code',
  4 => 'commission_percent',
  5 => 'total_earnings',
  6 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
